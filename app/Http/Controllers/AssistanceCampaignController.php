<?php

namespace App\Http\Controllers;

use App\Models\AssistanceCampaign;
use App\Models\DisasterEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AssistanceCampaignController extends Controller
{
    /**
     * Display all assistance campaigns.
     */
    public function index(Request $request): View
    {
        $query = AssistanceCampaign::with([
            'disasterEvent',
            'creator',
            'updater',
        ])->withCount('reliefVouchers');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('campaign_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('assistance_type', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        /*
        |--------------------------------------------------------------------------
        | Assistance Type Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('assistance_type')) {
            $query->where(
                'assistance_type',
                $request->input('assistance_type')
            );
        }

        $campaigns = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Dashboard Statistics
        |--------------------------------------------------------------------------
        */

        $totalCampaigns = AssistanceCampaign::count();

        $activeCampaigns = AssistanceCampaign::where(
            'status',
            'active'
        )->count();

        $draftCampaigns = AssistanceCampaign::where(
            'status',
            'draft'
        )->count();

        $closedCampaigns = AssistanceCampaign::where(
            'status',
            'closed'
        )->count();

        $cancelledCampaigns = AssistanceCampaign::where(
            'status',
            'cancelled'
        )->count();

        /*
        |--------------------------------------------------------------------------
        | Assistance Types
        |--------------------------------------------------------------------------
        */

        $assistanceTypes = AssistanceCampaign::query()
            ->select('assistance_type')
            ->whereNotNull('assistance_type')
            ->where('assistance_type', '!=', '')
            ->distinct()
            ->orderBy('assistance_type')
            ->pluck('assistance_type');

        return view('assistance-campaigns.index', compact(
            'campaigns',
            'totalCampaigns',
            'activeCampaigns',
            'draftCampaigns',
            'closedCampaigns',
            'cancelledCampaigns',
            'assistanceTypes'
        ));
    }

    /**
     * Show the campaign creation form.
     */
    public function create(): View
    {
        $disasterEvents = DisasterEvent::query()
            ->whereNotIn('status', ['cancelled', 'closed'])
            ->latest('start_date')
            ->get();

        return view(
            'assistance-campaigns.create',
            compact('disasterEvents')
        );
    }

    /**
     * Store a new assistance campaign.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'assistance_type' => [
                'required',
                'string',
                'max:255',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'assistance_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'assistance_package' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'status' => [
                'required',
                'in:draft,active',
            ],

            'disaster_event_id' => [
                'nullable',
                'integer',
                'exists:disaster_events,id',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Generate Unique Campaign Code
        |--------------------------------------------------------------------------
        |
        | Example:
        | CAMP-20260904-A8F32C
        |
        */

        do {
            $campaignCode =
                'CAMP-' .
                now()->format('Ymd') .
                '-' .
                strtoupper(Str::random(6));
        } while (
            AssistanceCampaign::where(
                'campaign_code',
                $campaignCode
            )->exists()
        );

        $campaign = AssistanceCampaign::create([
            'campaign_code' => $campaignCode,

            'name' => trim($validated['name']),

            'description' => $validated['description'] ?? null,

            'assistance_type' =>
                trim($validated['assistance_type']),

            'start_date' => $validated['start_date'],

            'end_date' => $validated['end_date'] ?? null,

            'assistance_amount' =>
                $validated['assistance_amount'] ?? null,

            'assistance_package' =>
                $validated['assistance_package'] ?? null,

            'status' => $validated['status'],

            'disaster_event_id' =>
                $validated['disaster_event_id'] ?? null,

            'barangay' => 'Barangay Poblacion',

            'municipality' => 'Bani',

            'province' => 'Pangasinan',

            'created_by' => Auth::id(),

            'updated_by' => Auth::id(),
        ]);

        return redirect()
            ->route(
                'assistance-campaigns.show',
                $campaign
            )
            ->with(
                'success',
                'Assistance campaign created successfully.'
            );
    }

    /**
     * Display a specific campaign.
     */
    public function show(
        AssistanceCampaign $assistanceCampaign
    ): View {
        $assistanceCampaign->load([
            'disasterEvent',
            'creator',
            'updater',
        ]);

        $voucherCount = $assistanceCampaign
            ->reliefVouchers()
            ->count();

        $issuedVoucherCount = $assistanceCampaign
            ->reliefVouchers()
            ->where('status', 'issued')
            ->count();

        $claimedVoucherCount = $assistanceCampaign
            ->reliefVouchers()
            ->where('status', 'claimed')
            ->count();

        $cancelledVoucherCount = $assistanceCampaign
            ->reliefVouchers()
            ->where('status', 'cancelled')
            ->count();

        return view(
            'assistance-campaigns.show',
            compact(
                'assistanceCampaign',
                'voucherCount',
                'issuedVoucherCount',
                'claimedVoucherCount',
                'cancelledVoucherCount'
            )
        );
    }

    /**
     * Show the campaign edit form.
     */
    public function edit(
        AssistanceCampaign $assistanceCampaign
    ): View {
        $disasterEvents = DisasterEvent::query()
            ->whereNotIn('status', ['cancelled', 'closed'])
            ->latest('start_date')
            ->get();

        return view(
            'assistance-campaigns.edit',
            compact(
                'assistanceCampaign',
                'disasterEvents'
            )
        );
    }

    /**
     * Update an assistance campaign.
     */
    public function update(
        Request $request,
        AssistanceCampaign $assistanceCampaign
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Do not allow changes to a campaign after vouchers are claimed.
        |--------------------------------------------------------------------------
        */

        $hasClaimedVouchers = $assistanceCampaign
            ->reliefVouchers()
            ->where('status', 'claimed')
            ->exists();

        if ($hasClaimedVouchers) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'This campaign cannot be modified because it already has claimed vouchers.'
                );
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'assistance_type' => [
                'required',
                'string',
                'max:255',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'assistance_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'assistance_package' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'status' => [
                'required',
                'in:draft,active,closed,cancelled',
            ],

            'disaster_event_id' => [
                'nullable',
                'integer',
                'exists:disaster_events,id',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Prevent reopening a cancelled campaign.
        |--------------------------------------------------------------------------
        */

        if (
            $assistanceCampaign->isCancelled() &&
            $validated['status'] !== 'cancelled'
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'A cancelled campaign cannot be reopened.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent reopening a closed campaign.
        |--------------------------------------------------------------------------
        */

        if (
            $assistanceCampaign->isClosed() &&
            $validated['status'] === 'draft'
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'A closed campaign cannot be changed back to draft.'
                );
        }

        $assistanceCampaign->update([
            'name' => trim($validated['name']),

            'description' =>
                $validated['description'] ?? null,

            'assistance_type' =>
                trim($validated['assistance_type']),

            'start_date' =>
                $validated['start_date'],

            'end_date' =>
                $validated['end_date'] ?? null,

            'assistance_amount' =>
                $validated['assistance_amount'] ?? null,

            'assistance_package' =>
                $validated['assistance_package'] ?? null,

            'status' =>
                $validated['status'],

            'disaster_event_id' =>
                $validated['disaster_event_id'] ?? null,

            'updated_by' =>
                Auth::id(),
        ]);

        return redirect()
            ->route(
                'assistance-campaigns.show',
                $assistanceCampaign
            )
            ->with(
                'success',
                'Assistance campaign updated successfully.'
            );
    }

    /**
     * Activate a draft campaign.
     */
    public function activate(
        AssistanceCampaign $assistanceCampaign
    ): RedirectResponse {
        if (! $assistanceCampaign->isDraft()) {
            return back()->with(
                'error',
                'Only draft campaigns can be activated.'
            );
        }

        $assistanceCampaign->update([
            'status' => 'active',
            'updated_by' => Auth::id(),
        ]);

        return back()->with(
            'success',
            'Assistance campaign is now active.'
        );
    }

    /**
     * Close an active campaign.
     */
    public function close(
        AssistanceCampaign $assistanceCampaign
    ): RedirectResponse {
        if (! $assistanceCampaign->isActive()) {
            return back()->with(
                'error',
                'Only active campaigns can be closed.'
            );
        }

        $assistanceCampaign->update([
            'status' => 'closed',
            'updated_by' => Auth::id(),
        ]);

        return back()->with(
            'success',
            'Assistance campaign has been closed.'
        );
    }

    /**
     * Cancel a campaign.
     */
    public function cancel(
        AssistanceCampaign $assistanceCampaign
    ): RedirectResponse {
        if ($assistanceCampaign->isCancelled()) {
            return back()->with(
                'error',
                'This campaign is already cancelled.'
            );
        }

        $hasClaimedVouchers = $assistanceCampaign
            ->reliefVouchers()
            ->where('status', 'claimed')
            ->exists();

        if ($hasClaimedVouchers) {
            return back()->with(
                'error',
                'This campaign cannot be cancelled because it already has claimed vouchers.'
            );
        }

        $assistanceCampaign->update([
            'status' => 'cancelled',
            'updated_by' => Auth::id(),
        ]);

        return back()->with(
            'success',
            'Assistance campaign has been cancelled.'
        );
    }
}