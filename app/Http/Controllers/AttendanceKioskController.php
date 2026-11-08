<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Personnel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AttendanceKioskController extends Controller
{
    /**
     * Display the public attendance kiosk.
     */
    public function index()
    {
        return view('attendance.kiosk');
    }

    /**
     * Identify personnel and automatically toggle Time In / Time Out.
     */
    public function identify(Request $request): JsonResponse
    {
        $ipKey = 'attendance-kiosk-identify:' . hash('sha256', $request->ip());

        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            return response()->json([
                'message' => 'Too many attempts. Please wait before trying again.',
                'retry_after' => RateLimiter::availableIn($ipKey),
            ], 429);
        }

        RateLimiter::hit($ipKey, 60);

        $validated = $request->validate([
            'method' => ['required', 'in:manual,id,qr'],
            'personnel_id' => ['required_if:method,manual,id', 'nullable', 'string', 'max:50'],
            'qr_token' => ['required_if:method,qr', 'nullable', 'string', 'max:500'],
        ]);

        $method = $validated['method'] === 'id'
            ? 'manual'
            : $validated['method'];

        $personnelQuery = Personnel::query()
            ->where('status', 'active');

        if ($method === 'manual') {
            $personnelQuery->where(
                'personnel_id',
                trim($validated['personnel_id'])
            );
        } else {
            $token = trim($validated['qr_token']);

            $personnelQuery
                ->where('qr_token_hash', hash('sha256', $token))
                ->whereNotNull('qr_token_hash');
        }

        $personnel = $personnelQuery->first();

        if (! $personnel) {
            return response()->json([
                'message' => 'Personnel could not be verified. Check your ID or QR code and try again.',
            ], 422);
        }

        /*
         * Lock the personnel row so simultaneous kiosk requests
         * cannot create duplicate attendance actions.
         */
        $result = DB::transaction(function () use ($personnel, $method) {
            $lockedPersonnel = Personnel::query()
                ->whereKey($personnel->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (! $lockedPersonnel) {
                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'Personnel is no longer active or registered.',
                ];
            }

            $attendanceDate = today()->toDateString();

            $record = AttendanceRecord::query()
                ->where('personnel_id', $lockedPersonnel->id)
                ->whereDate('attendance_date', $attendanceDate)
                ->lockForUpdate()
                ->first();

            $now = now();
            $action = null;
            $message = null;

            if (! $record || ! $record->hasTimedIn()) {
                // FIRST SUBMISSION: TIME IN
                if ($record) {
                    $record->update([
                        'time_in' => $now,
                        'time_in_method' => $method,
                        'status' => 'present',
                    ]);

                    $record->refresh();
                } else {
                    $record = AttendanceRecord::create([
                        'personnel_id' => $lockedPersonnel->id,
                        'attendance_date' => $attendanceDate,
                        'time_in' => $now,
                        'time_in_method' => $method,
                        'status' => 'present',
                    ]);
                }

                $action = 'time_in';
                $message = 'Time In successfully recorded!';
            } elseif (! $record->hasTimedOut()) {
                // SECOND SUBMISSION: TIME OUT
                $record->update([
                    'time_out' => $now,
                    'time_out_method' => $method,
                ]);

                $record->refresh();

                $action = 'time_out';
                $message = 'Time Out successfully recorded!';
            } else {
                // THIRD SUBMISSION: ALREADY COMPLETED
                $action = 'completed';
                $message = 'Your attendance is already completed for today.';
            }

            return [
                'success' => true,
                'action' => $action,
                'message' => $message,
                'personnel_name' => $lockedPersonnel->full_name,
                'personnel_id' => $lockedPersonnel->personnel_id,
                'position' => $lockedPersonnel->position,
                'date' => Carbon::parse($attendanceDate)->format('F d, Y'),
                'time_in' => $record->time_in
                    ? Carbon::parse($record->time_in)->format('h:i:s A')
                    : null,
                'time_out' => $record->time_out
                    ? Carbon::parse($record->time_out)->format('h:i:s A')
                    : null,
                'status' => $record->hasTimedOut()
                    ? 'Completed'
                    : 'Present',
            ];
        });

        $this->clearKioskSession($request);

        if (! $result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], $result['status']);
        }

        return response()->json([
            'success' => true,
            'action' => $result['action'],
            'message' => $result['message'],
            'personnel' => [
                'name' => $result['personnel_name'],
                'personnel_id' => $result['personnel_id'],
                'position' => $result['position'],
            ],
            'attendance' => [
                'date' => $result['date'],
                'time_in' => $result['time_in'],
                'time_out' => $result['time_out'],
                'status' => $result['status'],
            ],
        ]);
    }

    /**
     * Legacy PIN verification endpoint.
     */
    public function verifyPin(Request $request): JsonResponse
    {
        $personnelId = $request->session()->get(
            'attendance_kiosk_pending_personnel_id'
        );

        $expiresAt = $request->session()->get(
            'attendance_kiosk_pending_expires_at'
        );

        if (
            ! $personnelId ||
            ! $expiresAt ||
            now()->timestamp > $expiresAt
        ) {
            $this->clearKioskSession($request);

            return response()->json([
                'message' => 'Your verification session expired. Please identify yourself again.',
            ], 419);
        }

        $validated = $request->validate([
            'pin' => ['required', 'digits:6'],
        ]);

        $rateLimitKey = 'attendance-kiosk-pin:' .
            $personnelId . ':' .
            hash('sha256', $request->ip());

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            return response()->json([
                'message' => 'Too many incorrect PIN attempts. Please wait before trying again.',
                'retry_after' => RateLimiter::availableIn($rateLimitKey),
            ], 429);
        }

        $personnel = Personnel::query()
            ->whereKey($personnelId)
            ->where('status', 'active')
            ->first();

        if (
            ! $personnel ||
            ! $personnel->pin_hash ||
            ! Hash::check($validated['pin'], $personnel->pin_hash)
        ) {
            RateLimiter::hit($rateLimitKey, 60);

            return response()->json([
                'message' => 'Incorrect PIN. Please try again.',
            ], 422);
        }

        RateLimiter::clear($rateLimitKey);

        $request->session()->put([
            'attendance_kiosk_verified_personnel_id' => $personnel->id,
            'attendance_kiosk_verified_expires_at' => now()->addMinutes(5)->timestamp,
        ]);

        $request->session()->forget([
            'attendance_kiosk_pending_personnel_id',
            'attendance_kiosk_pending_expires_at',
        ]);

        return response()->json([
            'message' => 'PIN verified.',
            'personnel' => [
                'name' => $personnel->full_name,
                'position' => $personnel->position,
            ],
        ]);
    }

    /**
     * Legacy endpoint for explicit Time In or Time Out actions.
     */
    public function recordAttendance(Request $request): JsonResponse
    {
        $personnelId = $request->session()->get(
            'attendance_kiosk_verified_personnel_id'
        );

        $expiresAt = $request->session()->get(
            'attendance_kiosk_verified_expires_at'
        );

        if (
            ! $personnelId ||
            ! $expiresAt ||
            now()->timestamp > $expiresAt
        ) {
            $this->clearKioskSession($request);

            return response()->json([
                'message' => 'Your verification session expired. Please start again.',
            ], 419);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:time_in,time_out'],
        ]);

        $method = $request->session()->get('attendance_kiosk_method');

        if (! in_array($method, ['manual', 'qr'], true)) {
            $this->clearKioskSession($request);

            return response()->json([
                'message' => 'Identification session is invalid. Please start again.',
            ], 419);
        }

        $result = DB::transaction(function () use ($personnelId, $validated, $method) {
            $personnel = Personnel::query()
                ->whereKey($personnelId)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (! $personnel) {
                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'Personnel is no longer active or registered.',
                ];
            }

            $attendanceDate = today()->toDateString();

            $record = AttendanceRecord::query()
                ->where('personnel_id', $personnel->id)
                ->whereDate('attendance_date', $attendanceDate)
                ->lockForUpdate()
                ->first();

            if ($validated['action'] === 'time_in') {
                if ($record) {
                    return [
                        'success' => false,
                        'status' => 409,
                        'message' => 'Time In has already been recorded for today.',
                    ];
                }

                $record = AttendanceRecord::create([
                    'personnel_id' => $personnel->id,
                    'attendance_date' => $attendanceDate,
                    'time_in' => now(),
                    'time_in_method' => $method,
                    'status' => 'present',
                ]);

                return [
                    'success' => true,
                    'message' => 'Time In successfully recorded.',
                    'action' => 'time_in',
                    'time' => $record->time_in,
                    'name' => $personnel->full_name,
                ];
            }

            if (! $record || ! $record->hasTimedIn()) {
                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'You must record Time In before recording Time Out.',
                ];
            }

            if ($record->hasTimedOut()) {
                return [
                    'success' => false,
                    'status' => 409,
                    'message' => 'Time Out has already been recorded for today.',
                ];
            }

            $record->update([
                'time_out' => now(),
                'time_out_method' => $method,
            ]);

            return [
                'success' => true,
                'message' => 'Time Out successfully recorded.',
                'action' => 'time_out',
                'time' => $record->time_out,
                'name' => $personnel->full_name,
            ];
        });

        $this->clearKioskSession($request);

        if (! $result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], $result['status']);
        }

        return response()->json([
            'message' => $result['message'],
            'action' => $result['action'],
            'time' => Carbon::parse($result['time'])->format('h:i:s A'),
            'personnel_name' => $result['name'],
        ]);
    }

    /**
     * Clear all temporary kiosk verification information.
     */
    private function clearKioskSession(Request $request): void
    {
        $request->session()->forget([
            'attendance_kiosk_pending_personnel_id',
            'attendance_kiosk_method',
            'attendance_kiosk_pending_expires_at',
            'attendance_kiosk_verified_personnel_id',
            'attendance_kiosk_verified_expires_at',
        ]);
    }
}
