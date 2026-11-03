<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Personnel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

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
     * Identify personnel through manual ID entry or QR token.
     *
     * This step does not record attendance.
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
            'method' => ['required', 'in:manual,qr'],
            'personnel_id' => ['required_if:method,manual', 'nullable', 'string', 'max:50'],
            'qr_token' => ['required_if:method,qr', 'nullable', 'string', 'max:500'],
        ]);

        $personnel = null;

        if ($validated['method'] === 'manual') {
            $personnel = Personnel::query()
                ->where('personnel_id', trim($validated['personnel_id']))
                ->where('status', 'active')
                ->first();
        }

        if ($validated['method'] === 'qr') {
            $token = trim($validated['qr_token']);

            $personnel = Personnel::query()
                ->where('qr_token_hash', hash('sha256', $token))
                ->where('status', 'active')
                ->whereNotNull('qr_token_hash')
                ->first();
        }

        if (! $personnel) {
            return response()->json([
                'message' => 'Personnel could not be verified. Check your ID or QR code and try again.',
            ], 422);
        }

        // Store only the personnel reference and identification method.
        // The PIN must still be verified before attendance is recorded.
        $request->session()->put([
            'attendance_kiosk_pending_personnel_id' => $personnel->id,
            'attendance_kiosk_method' => $validated['method'],
            'attendance_kiosk_pending_expires_at' => now()->addMinutes(5)->timestamp,
        ]);

        $request->session()->forget([
            'attendance_kiosk_verified_personnel_id',
            'attendance_kiosk_verified_expires_at',
        ]);

        return response()->json([
            'message' => 'Personnel identified. Please enter your six-digit PIN.',
            'personnel' => [
                'name' => $personnel->full_name,
                'position' => $personnel->position,
            ],
        ]);
    }

    /**
     * Verify the personnel's six-digit PIN.
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
            'message' => 'PIN verified. You may now choose Time In or Time Out.',
            'personnel' => [
                'name' => $personnel->full_name,
                'position' => $personnel->position,
            ],
        ]);
    }

    /**
     * Record an explicitly selected Time In or Time Out.
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
            // Lock the personnel row to prevent simultaneous attendance actions.
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

                AttendanceRecord::create([
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
                    'time' => now()->format('h:i:s A'),
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
                'time' => now()->format('h:i:s A'),
                'name' => $personnel->full_name,
            ];
        });

        // Require fresh identification and PIN verification for the next transaction.
        $this->clearKioskSession($request);

        if (! $result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], $result['status']);
        }

        return response()->json([
            'message' => $result['message'],
            'action' => $result['action'],
            'time' => $result['time'],
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
