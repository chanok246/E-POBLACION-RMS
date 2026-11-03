@extends('layouts.app')

@section('title', 'Beneficiary Encoding | e-Poblacion')

@section('content')

@php
    // ---------------- SAFE DEFAULTS ----------------
    $operation = $operation ?? null;

    $masterlistRecords = $masterlistRecords ?? collect();
    $encodedBeneficiaries = $encodedBeneficiaries ?? collect();

    $search = trim((string) ($search ?? request('search', '')));
    $purok = trim((string) ($purok ?? request('purok', '')));
    $filter = trim((string) ($filter ?? request('filter', 'all')));

    $filterOptions = $filterOptions ?? [
        'all' => 'All',
        'household_heads' => 'Household Heads',
        'students' => 'Students',
        'senior_citizens' => 'Senior Citizens',
    ];

    if (!array_key_exists($filter, $filterOptions)) {
        $filter = 'all';
    }

    $purokOptions = $purokOptions ?? collect();

    // ---------------- OPERATION VALUES ----------------
    $operationName = $operation->operation_name ?? 'Relief Assistance Operation';
    $operationCode = $operation->operation_code ?? '—';
    $assistanceType = $operation->assistance_type ?? 'Relief Assistance';
    $quantityUnit = $operation->quantity_unit ?? 'units';

    $quantityReceived = (float) ($operation->quantity_received ?? 0);
    $quantityAllocated = (float) ($operation->quantity_allocated ?? 0);
    $remainingQuantity = max(0, $quantityReceived - $quantityAllocated);

    $allocatedPercent = $quantityReceived > 0
        ? (int) min(
            100,
            round(
                ($quantityAllocated / $quantityReceived) * 100
            )
        )
        : 0;

    // ---------------- HELPERS ----------------
    $fullName = function ($person) {
        if (!$person) {
            return 'Unknown Resident';
        }

        $name = collect([
            $person->first_name ?? null,
            $person->middle_name ?? null,
            $person->last_name ?? null,
            $person->suffix ?? null,
        ])
            ->filter(
                fn ($value) =>
                    trim((string) $value) !== ''
            )
            ->implode(' ');

        return trim($name) !== ''
            ? trim($name)
            : 'Unknown Resident';
    };

    $getAge = function ($person) {
        if (!$person || !$person->birth_date) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse(
                $person->birth_date
            )->age;
        } catch (\Throwable $exception) {
            return null;
        }
    };

    /*
    |--------------------------------------------------------------------------
    | NORMALIZE MASTERLIST RECORDS
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | The controller now provides resident-level masterlist records.
    |
    | Each record represents ONE resident together with that resident's
    | household context.
    |
    */
    $normalizedRecords = collect();

    foreach ($masterlistRecords as $record) {

        $resident = null;
        $household = null;
        $relationship = null;
        $householdId = null;

        if (is_array($record)) {

            $resident =
                $record['resident']
                ?? null;

            $household =
                $record['household']
                ?? null;

            $relationship =
                $record['relationship']
                ?? null;

            $householdId =
                $record['household_id']
                ?? null;

        } elseif (is_object($record)) {

            $resident =
                $record->resident
                ?? null;

            $household =
                $record->household
                ?? null;

            $relationship =
                $record->relationship
                ?? null;

            $householdId =
                $record->household_id
                ?? null;
        }

        if (!$householdId && $household) {
            $householdId =
                $household->id
                ?? null;
        }

        /*
         * A resident and household are both required for a valid
         * beneficiary selection.
         */
        if (
            !$resident
            ||
            !$household
            ||
            !$householdId
        ) {
            continue;
        }

        $residentId =
            (int) (
                $resident->id
                ?? 0
            );

        if ($residentId <= 0) {
            continue;
        }

        $normalizedRecords->push([
            'resident' =>
                $resident,

            'resident_id' =>
                $residentId,

            'household' =>
                $household,

            'household_id' =>
                (int) $householdId,

            'relationship' =>
                $relationship,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ENCODED RESIDENT IDs
    |--------------------------------------------------------------------------
    |
    | A resident is considered encoded only when that specific resident
    | already has a confirmed beneficiary record for this operation.
    |
    */
    $encodedResidentIds = collect(
        $encodedBeneficiaries
            ->filter(
                fn ($beneficiary) =>
                    strtolower(
                        (string) (
                            $beneficiary->beneficiary_status
                            ?? ''
                        )
                    ) === 'confirmed'
            )
            ->pluck('resident_id')
            ->filter()
            ->map(
                fn ($id) =>
                    (int) $id
            )
            ->unique()
            ->values()
    );

    /*
    |--------------------------------------------------------------------------
    | ACTIVE VOUCHER RESIDENT IDs
    |--------------------------------------------------------------------------
    |
    | A resident with an active voucher should not be selected again.
    |
    */
    $existingVoucherResidentIds = collect(
        $encodedBeneficiaries
            ->filter(
                fn ($beneficiary) =>
                    !empty(
                        $beneficiary->resident_id
                    )
            )
            ->filter(
                function ($beneficiary) {

                    $status =
                        strtolower(
                            (string) (
                                $beneficiary->assistance_status
                                ?? ''
                            )
                        );

                    return in_array(
                        $status,
                        [
                            'allocated',
                            'voucher_generated',
                            'distributed',
                            'claimed',
                        ],
                        true
                    );
                }
            )
            ->pluck('resident_id')
            ->filter()
            ->map(
                fn ($id) =>
                    (int) $id
            )
            ->unique()
            ->values()
    );

    /*
    |--------------------------------------------------------------------------
    | DISPLAY ROWS
    |--------------------------------------------------------------------------
    |
    | ONE ROW = ONE RESIDENT.
    |
    | This is the important change from the previous household-based
    | interface.
    |
    */
    $displayRows = $normalizedRecords
        ->unique(
            fn ($record) =>
                (int) $record['resident_id']
        )
        ->map(
            function ($record) use (
                $encodedResidentIds,
                $existingVoucherResidentIds,
                $fullName,
                $getAge
            ) {

                $resident =
                    $record['resident'];

                $household =
                    $record['household'];

                $residentId =
                    (int) $record['resident_id'];

                $householdId =
                    (int) $record['household_id'];

                $isEncoded =
                    $encodedResidentIds
                        ->contains(
                            $residentId
                        );

                $hasVoucher =
                    $existingVoucherResidentIds
                        ->contains(
                            $residentId
                        );

                if ($hasVoucher) {

                    $displayStatus =
                        'Voucher Generated';

                    $statusClass =
                        'is-green';

                } elseif ($isEncoded) {

                    $displayStatus =
                        'Already Confirmed';

                    $statusClass =
                        'is-ube';

                } else {

                    $displayStatus =
                        'Awaiting Encoding';

                    $statusClass =
                        'is-grey';
                }

                $householdHead =
                    $household->householdHead
                    ?? null;

                $householdNumber =
                    trim(
                        (string) (
                            $household->household_number
                            ?? ''
                        )
                    );

                if ($householdNumber === '') {

                    $householdNumber =
                        'Household #'
                        . $householdId;
                }

                return [

                    'resident' =>
                        $resident,

                    'resident_id' =>
                        $residentId,

                    'household' =>
                        $household,

                    'household_id' =>
                        $householdId,

                    'relationship' =>
                        $record['relationship'],

                    'resident_name' =>
                        $fullName($resident),

                    'resident_age' =>
                        $getAge($resident),

                    'household_head' =>
                        $householdHead,

                    'household_head_name' =>
                        $householdHead
                            ? $fullName(
                                $householdHead
                            )
                            : 'No household head assigned',

                    'household_number' =>
                        $householdNumber,

                    'purok' =>
                        trim(
                            (string) (
                                $household->purok
                                ?? ''
                            )
                        ),

                    'address' =>
                        trim(
                            (string) (
                                $household->address
                                ?? ''
                            )
                        ),

                    'is_encoded' =>
                        $isEncoded,

                    'has_voucher' =>
                        $hasVoucher,

                    'display_status' =>
                        $displayStatus,

                    'status_class' =>
                        $statusClass,
                ];
            }
        )
        ->sortBy([
            [
                fn ($row) =>
                    mb_strtolower(
                        (string) (
                            $row['purok']
                            ?? ''
                        )
                    ),
                'asc',
            ],
            [
                fn ($row) =>
                    mb_strtolower(
                        (string) (
                            $row['household_number']
                            ?? ''
                        )
                    ),
                'asc',
            ],
            [
                fn ($row) =>
                    mb_strtolower(
                        (string) (
                            $row['resident_name']
                            ?? ''
                        )
                    ),
                'asc',
            ],
        ])
        ->values();

    // ---------------- COUNTS ----------------

    $totalMatchingResidents =
        $displayRows->count();

    $availableForEncodingCount =
        $displayRows
            ->filter(
                fn ($row) =>
                    !$row['is_encoded']
                    &&
                    !$row['has_voucher']
            )
            ->count();

    $alreadyConfirmedCount =
        $displayRows
            ->filter(
                fn ($row) =>
                    $row['is_encoded']
                    &&
                    !$row['has_voucher']
            )
            ->count();

    $voucherGeneratedCount =
        $displayRows
            ->filter(
                fn ($row) =>
                    $row['has_voucher']
            )
            ->count();

    // ---------------- CONFIRMED BENEFICIARIES ----------------

    $confirmedBeneficiaries =
        $encodedBeneficiaries
            ->filter(
                fn ($beneficiary) =>
                    strtolower(
                        (string) (
                            $beneficiary->beneficiary_status
                            ?? ''
                        )
                    ) === 'confirmed'
            )
            ->values();

    /*
     * Only confirmed beneficiaries without an active voucher
     * should be sent to voucher generation.
     */
    $voucherReadyBeneficiaries =
        $confirmedBeneficiaries
            ->filter(
                function ($beneficiary) {

                    $status =
                        strtolower(
                            (string) (
                                $beneficiary->assistance_status
                                ?? 'awaiting_assistance'
                            )
                        );

                    return in_array(
                        $status,
                        [
                            'awaiting_assistance',
                        ],
                        true
                    );
                }
            )
            ->values();
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">

<style>
    /* =========================================================
       Beneficiary encoding — balanced light & dark mode.
       All colors live in tokens so both themes keep strong
       contrast for text, borders, buttons and badges.
       ========================================================= */
    .bn {
        /* text & surfaces */
        --ink: #1c1b33;
        --ink-2: #4f4d69;
        --grey: #615f7c;
        --line: #dedbec;
        --line-strong: #bdb9d8;
        --wash: #f4f3fa;
        --card: #ffffff;
        --field: #ffffff;
        --hover: #f4f1fd;
        --track: #d8d4ec;

        /* brand */
        --ube: #5a3fd6;
        --ube-soft: #ebe7fb;
        --btn-bg: #5a3fd6;
        --btn-fg: #ffffff;
        --btn-hover: #432ca8;
        --btn-off-bg: #dedbec;
        --btn-off-fg: #5a5873;
        --mango: #d98e00;
        --mango-soft: #fff1d0;
        --green: #13724d;
        --green-soft: #dcf3e8;
        --red: #cf3d3d;
        --red-soft: #fbe4e4;
        --grey-soft: #ebeaf3;

        /* badge / alert ink */
        --badge-grey-ink: #4f4d69;
        --badge-mango-ink: #7a5000;
        --ok-ink: #0f5c3f;
        --info-ink: #3a2a91;
        --err-ink: #7d2020;
        --note-ink: #5a4410;

        --shadow-up: 0 -8px 20px rgba(28, 27, 51, .08);
        --select-arrow: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%231c1b33' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");

        max-width: 1320px;
        margin: 0 auto;
        color: var(--ink);
        font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif;
    }
    html[data-theme="dark"] .bn {
        --ink: #f1f0fa;
        --ink-2: #c3c1da;
        --grey: #aeacc6;
        --line: #3a3858;
        --line-strong: #5b5880;
        --wash: #1a1930;
        --card: #232139;
        --field: #15142a;
        --hover: #2b2a4a;
        --track: #3a3858;

        --ube: #b9acff;
        --ube-soft: #322e5e;
        --btn-bg: #b9acff;
        --btn-fg: #170f3d;
        --btn-hover: #d3cbff;
        --btn-off-bg: #33324f;
        --btn-off-fg: #aeacc6;
        --mango: #f2c14e;
        --mango-soft: #3b2f12;
        --green: #72e2b2;
        --green-soft: #1b3a2d;
        --red: #ff9aa3;
        --red-soft: #43202a;
        --grey-soft: #302f4a;

        --badge-grey-ink: #cfcde4;
        --badge-mango-ink: #f6dc96;
        --ok-ink: #a6efcd;
        --info-ink: #d0c8ff;
        --err-ink: #ffc3c9;
        --note-ink: #f6dc96;

        --shadow-up: 0 -8px 20px rgba(0, 0, 0, .35);
        --select-arrow: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23f1f0fa' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    }

    .bn *,
    .bn *::before,
    .bn *::after {
        box-sizing: border-box;
    }

    .bn h1,
    .bn h2,
    .bn p {
        margin: 0;
        padding: 0;
    }

    .bn a:focus-visible,
    .bn button:focus-visible,
    .bn input:focus-visible,
    .bn select:focus-visible {
        outline: 3px solid var(--mango);
        outline-offset: 2px;
    }

    .bn-display {
        font-family: 'Unbounded', 'Figtree', system-ui, sans-serif;
        letter-spacing: -.02em;
    }

    /* Header */

    .bn-crumb {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        font-size: 13px;
        color: var(--ink-2);
    }

    .bn-crumb a {
        color: var(--ink-2);
        text-decoration: none;
        font-weight: 600;
    }

    .bn-crumb a:hover {
        color: var(--ube);
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .bn-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 20px;
    }

    .bn-hero h1 {
        font-size: 28px;
        line-height: 1.15;
        font-weight: 800;
        color: var(--ink);
    }

    .bn-hero p {
        margin-top: 8px;
        max-width: 560px;
        font-size: 14.5px;
        line-height: 1.6;
        color: var(--ink-2);
    }

    .bn-ghost {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 18px;
        border: 1.5px solid var(--line-strong);
        border-radius: 999px;
        background: var(--card);
        color: var(--ink);
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
        transition: border-color .15s, background .15s;
    }

    .bn-ghost:hover {
        border-color: var(--ube);
        background: var(--ube-soft);
        color: var(--ink);
    }

    /* Alerts */

    .bn-alert {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 16px;
        padding: 14px 16px;
        border-radius: 14px;
        font-size: 13.5px;
        line-height: 1.5;
    }

    .bn-alert i.lead {
        flex: none;
        margin-top: 1px;
        font-size: 17px;
    }

    .bn-alert .x {
        margin-left: auto;
        padding: 0 4px;
        border: 0;
        background: transparent;
        font-size: 20px;
        line-height: 1;
        color: inherit;
        opacity: .7;
        cursor: pointer;
    }

    .bn-alert .x:hover {
        opacity: 1;
    }

    .bn-alert ul {
        margin: 6px 0 0;
        padding-left: 20px;
    }

    .bn-alert.is-ok {
        background: var(--green-soft);
        color: var(--ok-ink);
    }

    .bn-alert.is-info {
        background: var(--ube-soft);
        color: var(--info-ink);
    }

    .bn-alert.is-err {
        background: var(--red-soft);
        color: var(--err-ink);
    }

    .bn-alert.is-note {
        background: var(--mango-soft);
        color: var(--note-ink);
    }

    /* Summary */

    .bn-strip {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) repeat(3, minmax(0, 1fr));
        border: 1px solid var(--line);
        border-radius: 18px;
        background: var(--card);
        margin-bottom: 20px;
    }

    .bn-cell {
        padding: 20px 22px;
    }

    .bn-cell + .bn-cell {
        border-left: 1px solid var(--line);
    }

    .bn-cell .k {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--grey);
    }

    .bn-cell .v {
        margin-top: 6px;
        font-family: 'Unbounded', 'Figtree', sans-serif;
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -.02em;
        line-height: 1.1;
        color: var(--ink);
    }

    .bn-cell .v.is-good {
        color: var(--green);
    }

    .bn-cell .u {
        margin-top: 3px;
        font-size: 12.5px;
        color: var(--ink-2);
    }

    .bn-op .name {
        margin-top: 6px;
        font-size: 17px;
        font-weight: 700;
        line-height: 1.3;
        color: var(--ink);
    }

    .bn-op .meta {
        margin-top: 5px;
        font-size: 13px;
        color: var(--ink-2);
    }

    .bn-bar {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 22px;
        border-top: 1px solid var(--line);
        background: var(--wash);
        border-radius: 0 0 18px 18px;
        font-size: 13px;
        color: var(--ink-2);
    }

    .bn-bar .track {
        flex: 1;
        height: 8px;
        border-radius: 999px;
        background: var(--track);
        overflow: hidden;
    }

    .bn-bar .track i {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: var(--ube);
    }

    .bn-bar b {
        color: var(--ink);
    }

    /* Panels */

    .bn-panel {
        border: 1px solid var(--line);
        border-radius: 18px;
        background: var(--card);
        margin-bottom: 20px;
    }

    .bn-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 20px 24px 16px;
    }

    .bn-head h2 {
        font-size: 17px;
        font-weight: 800;
        color: var(--ink);
    }

    .bn-head p {
        margin-top: 4px;
        font-size: 13px;
        line-height: 1.5;
        color: var(--ink-2);
        max-width: 600px;
    }

    .bn-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .bn-filter {
        padding: 16px 24px 20px;
        border-top: 1px solid var(--line);
        background: var(--wash);
    }

    .bn-filter form {
        display: grid;
        grid-template-columns:
            minmax(0, 1.4fr)
            minmax(0, 1fr)
            minmax(0, 1fr)
            auto;
        gap: 14px;
        align-items: end;
    }

    .bn-filter label {
        display: block;
        margin-bottom: 6px;
        font-size: 12.5px;
        font-weight: 700;
        color: var(--ink);
    }

    .bn .form-select,
    .bn .form-control {
        width: 100%;
        min-height: 44px;
        padding: 10px 14px;
        border: 1.5px solid var(--line-strong);
        border-radius: 12px;
        background-color: var(--field);
        color: var(--ink);
        font-size: 14px;
        font-weight: 600;
        font-family: inherit;
        box-shadow: none;
    }

    .bn .form-control::placeholder {
        color: var(--ink-2);
        opacity: .8;
        font-weight: 500;
    }

    .bn .form-select {
        appearance: none;
        -webkit-appearance: none;
        background-image: var(--select-arrow);
        background-repeat: no-repeat;
        background-position: right .9rem center;
        background-size: 16px;
        padding-right: 2.4rem;
    }

    .bn .form-select option {
        background: var(--card);
        color: var(--ink);
    }

    .bn .form-select:hover,
    .bn .form-control:hover {
        border-color: var(--ube);
    }

    .bn .form-select:focus,
    .bn .form-control:focus {
        background-color: var(--field);
        color: var(--ink);
        border-color: var(--ube);
        box-shadow: 0 0 0 3px var(--ube-soft);
        outline: 0;
    }

    .bn-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        padding: 0 22px;
        border: 0;
        border-radius: 999px;
        background: var(--btn-bg);
        color: var(--btn-fg);
        font: inherit;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: background .15s, transform .15s;
    }

    .bn-btn:hover {
        background: var(--btn-hover);
        color: var(--btn-fg);
    }

    .bn-btn:active {
        transform: scale(.98);
    }

    .bn-btn:disabled {
        background: var(--btn-off-bg);
        color: var(--btn-off-fg);
        cursor: not-allowed;
        transform: none;
    }

    .bn-mini {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border: 1.5px solid var(--line-strong);
        border-radius: 999px;
        background: var(--card);
        color: var(--ink);
        font: inherit;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: border-color .15s, background .15s;
    }

    .bn-mini:hover {
        border-color: var(--ube);
        background: var(--ube-soft);
    }

    .bn-tools {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 24px;
        border-top: 1px solid var(--line);
    }

    .bn-tools .t {
        font-size: 15px;
        font-weight: 800;
        color: var(--ink);
    }

    .bn-tools .t small {
        display: block;
        margin-top: 2px;
        font-size: 12.5px;
        font-weight: 500;
        color: var(--ink-2);
    }

    /* Tables */

    .bn-scroll {
        overflow-x: auto;
        border-top: 1px solid var(--line);
    }

    .bn-table {
        width: 100%;
        min-width: 1100px;
        border-collapse: collapse;
        color: var(--ink);
    }

    .bn-table th {
        padding: 12px 14px;
        background: var(--wash);
        border-bottom: 1px solid var(--line);
        font-size: 12.5px;
        font-weight: 700;
        color: var(--ink-2);
        text-align: left;
        white-space: nowrap;
    }

    .bn-table td {
        padding: 14px;
        border-bottom: 1px solid var(--line);
        font-size: 13.5px;
        vertical-align: middle;
        color: var(--ink);
    }

    .bn-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .bn-table tbody tr:hover {
        background: var(--hover);
    }

    .bn-table .pick {
        width: 64px;
        padding-left: 24px;
    }

    .bn-table tr[data-row] {
        cursor: pointer;
    }

    .bn-table tr[data-row].is-locked {
        cursor: default;
    }

    .bn-table tr.is-picked {
        background: var(--ube-soft);
        box-shadow: inset 4px 0 0 var(--ube);
    }

    .bn-table tr.is-picked:hover {
        background: var(--ube-soft);
    }

    .bn-table input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin: 0;
        accent-color: var(--ube);
        cursor: pointer;
    }

    .bn-table input[type="checkbox"]:disabled {
        cursor: not-allowed;
        opacity: .55;
    }

    .bn-strong {
        font-weight: 700;
        color: var(--ink);
    }

    .bn-muted {
        margin-top: 2px;
        font-size: 12px;
        color: var(--ink-2);
    }

    .bn-match {
        display: grid;
        gap: 4px;
    }

    .bn-match div {
        display: flex;
        align-items: baseline;
        gap: 6px;
        font-size: 13px;
    }

    .bn-match i {
        color: var(--grey);
        font-size: 12px;
    }

    .bn-match small {
        font-size: 12px;
        color: var(--ink-2);
    }

    .bn-loc {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 11px;
        border: 1px solid var(--line-strong);
        border-radius: 999px;
        background: var(--wash);
        color: var(--ink);
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .bn-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 4px 11px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .bn-badge::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .bn-badge.is-grey {
        background: var(--grey-soft);
        color: var(--badge-grey-ink);
    }

    .bn-badge.is-ube {
        background: var(--ube-soft);
        color: var(--ube);
    }

    .bn-badge.is-mango {
        background: var(--mango-soft);
        color: var(--badge-mango-ink);
    }

    .bn-badge.is-green {
        background: var(--green-soft);
        color: var(--green);
    }

    /* Sticky confirm bar */

    .bn-confirm {
        position: sticky;
        bottom: 0;
        z-index: 20;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 14px 24px;
        border-top: 1px solid var(--line);
        border-radius: 0 0 18px 18px;
        background: var(--card);
        box-shadow: var(--shadow-up);
    }

    .bn-confirm .c {
        font-size: 15px;
        font-weight: 700;
        color: var(--ink);
    }

    .bn-confirm .c b {
        font-family: 'Unbounded', 'Figtree', sans-serif;
        font-size: 20px;
        color: var(--ube);
        margin-right: 4px;
    }

    .bn-confirm .s {
        margin-top: 2px;
        font-size: 12.5px;
        color: var(--ink-2);
    }

    .bn-empty {
        padding: 52px 22px;
        text-align: center;
    }

    .bn-empty .ic {
        width: 60px;
        height: 60px;
        margin: 0 auto 14px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: var(--ube-soft);
        color: var(--ube);
        font-size: 24px;
    }

    .bn-empty h3 {
        margin: 0 0 6px;
        font-size: 15px;
        font-weight: 700;
        color: var(--ink);
    }

    .bn-empty p {
        max-width: 420px;
        margin: 0 auto;
        font-size: 13.5px;
        line-height: 1.6;
        color: var(--ink-2);
    }

    .bn-bulk {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 24px;
        border-top: 1px solid var(--line);
        background: var(--wash);
        border-radius: 0 0 18px 18px;
    }

    .bn-bulk .t {
        font-size: 14.5px;
        font-weight: 700;
        color: var(--ink);
    }

    .bn-bulk .s {
        margin-top: 3px;
        font-size: 13px;
        color: var(--ink-2);
        max-width: 560px;
    }

    @media (max-width: 1099.98px) {

        .bn-strip {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .bn-op {
            grid-column: 1 / -1;
            border-bottom: 1px solid var(--line);
        }

        .bn-cell.bn-op + .bn-cell {
            border-left: 0;
        }

        .bn-filter form {
            grid-template-columns: 1fr 1fr;
        }

        .bn-filter .go {
            grid-column: 1 / -1;
        }

        .bn-filter .go .bn-btn {
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {

        .bn-hero h1 {
            font-size: 22px;
        }

        .bn-filter form {
            grid-template-columns: 1fr;
        }

        .bn-head,
        .bn-tools,
        .bn-confirm,
        .bn-bulk {
            padding-left: 16px;
            padding-right: 16px;
        }

        .bn-filter {
            padding: 14px 16px 18px;
        }

        .bn-confirm .bn-btn,
        .bn-bulk .bn-btn {
            width: 100%;
        }

        .bn-strip {
            grid-template-columns: 1fr 1fr;
        }

        .bn-cell {
            padding: 16px;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .bn *,
        .bn *::before,
        .bn *::after {
            transition: none !important;
        }
    }
</style>

<div class="container-fluid py-4 bn">

    {{-- ============ HEADER ============ --}}

    <header class="bn-hero">

        <div>

            <nav class="bn-crumb" aria-label="Breadcrumb">

                <a href="{{ route('relief-vouchers.index') }}">
                    Relief Assistance
                </a>

                <span aria-hidden="true">/</span>

                <a href="{{ route('relief-assistance.show', $operation) }}">
                    Operation
                </a>

                <span aria-hidden="true">/</span>

                <span>Beneficiary Encoding</span>

            </nav>

            <h1 class="bn-display">
                Beneficiary Encoding
            </h1>

            <p>
                Select the residents confirmed during the field assessment.
                Each resident is encoded individually while their household
                information remains attached to the record.
            </p>

        </div>

        <a
            href="{{ route('relief-assistance.show', $operation) }}"
            class="bn-ghost"
        >
            <i class="bi bi-arrow-left"></i>
            Back to operation
        </a>

    </header>

    {{-- ============ FLASH MESSAGES ============ --}}

    @if (session('success'))

        <div class="bn-alert is-ok" role="alert">

            <i class="bi bi-check-circle-fill lead"></i>

            <div>
                {{ session('success') }}
            </div>

            <button
                type="button"
                class="x"
                aria-label="Dismiss"
                onclick="this.parentElement.remove()"
            >
                &times;
            </button>

        </div>

    @endif

    @if (session('info'))

        <div class="bn-alert is-info" role="alert">

            <i class="bi bi-info-circle-fill lead"></i>

            <div>
                {{ session('info') }}
            </div>

            <button
                type="button"
                class="x"
                aria-label="Dismiss"
                onclick="this.parentElement.remove()"
            >
                &times;
            </button>

        </div>

    @endif

    @if ($errors->any())

        <div class="bn-alert is-err" role="alert">

            <i class="bi bi-exclamation-triangle-fill lead"></i>

            <div>

                <strong>
                    Please correct the following:
                </strong>

                <ul>

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

            <button
                type="button"
                class="x"
                aria-label="Dismiss"
                onclick="this.parentElement.remove()"
            >
                &times;
            </button>

        </div>

    @endif

    {{-- ============ OPERATION + STOCK ============ --}}

    <section
        class="bn-strip"
        aria-label="Operation summary"
    >

        <div class="bn-cell bn-op">

            <div class="k">
                Operation
            </div>

            <div class="name">
                {{ $operationName }}
            </div>

            <div class="meta">
                {{ $operationCode }}
                &nbsp;&middot;&nbsp;
                {{ $assistanceType }}
            </div>

        </div>

        <div class="bn-cell">

            <div class="k">
                Quantity received
            </div>

            <div class="v">
                {{ number_format($quantityReceived, 0) }}
            </div>

            <div class="u">
                {{ $quantityUnit }}
            </div>

        </div>

        <div class="bn-cell">

            <div class="k">
                Allocated
            </div>

            <div class="v">
                {{ number_format($quantityAllocated, 0) }}
            </div>

            <div class="u">
                {{ $quantityUnit }}
            </div>

        </div>

        <div class="bn-cell">

            <div class="k">
                Remaining
            </div>

            <div class="v is-good">
                {{ number_format($remainingQuantity, 0) }}
            </div>

            <div class="u">
                {{ $quantityUnit }}
            </div>

        </div>

        @if ($quantityReceived > 0)

            <div class="bn-bar">

                <span>
                    <b>{{ $allocatedPercent }}%</b>
                    allocated
                </span>

                <div
                    class="track"
                    role="img"
                    aria-label="{{ $allocatedPercent }} percent allocated"
                >
                    <i
                        style="width: {{ $allocatedPercent }}%"
                    ></i>
                </div>

                <span>
                    {{ number_format($remainingQuantity, 0) }}
                    {{ $quantityUnit }}
                    left
                </span>

            </div>

        @endif

    </section>

    <div class="bn-alert is-note">

        <i class="bi bi-person-check-fill lead"></i>

        <div>

            The system only lists residents identified by the
            masterlist filters.

            <strong>
                It does not decide who receives assistance.
            </strong>

            Select only the residents that authorized personnel
            confirmed in the field.

        </div>

    </div>

    {{-- ============ RESIDENT SELECTION ============ --}}

    <section
        class="bn-panel"
        aria-labelledby="bnResidentsTitle"
    >

        <div class="bn-head">

            <div>

                <h2
                    id="bnResidentsTitle"
                    class="bn-display"
                >
                    Residents to confirm
                </h2>

                <p>
                    Each resident can be selected independently,
                    even when several matching residents belong
                    to the same household.
                </p>

            </div>

            <div class="bn-pills">

                <span class="bn-badge is-grey">
                    {{ $availableForEncodingCount }}
                    awaiting
                </span>

                <span class="bn-badge is-ube">
                    {{ $alreadyConfirmedCount }}
                    confirmed
                </span>

                <span class="bn-badge is-green">
                    {{ $voucherGeneratedCount }}
                    with voucher
                </span>

            </div>

        </div>

        {{-- Filter form --}}

        <div class="bn-filter">

            <form
                method="GET"
                action="{{ route('relief-assistance.beneficiaries', $operation) }}"
                id="beneficiaryFilterForm"
            >

                <div>

                    <label for="search">
                        Search resident / household
                    </label>

                    <input
                        type="text"
                        id="search"
                        name="search"
                        class="form-control"
                        value="{{ $search }}"
                        placeholder="Name, household number..."
                    >

                </div>

                <div>

                    <label for="filter">
                        Target group
                    </label>

                    <select
                        id="filter"
                        name="filter"
                        class="form-select"
                    >

                        @foreach ($filterOptions as $value => $label)

                            <option
                                value="{{ $value }}"
                                @selected($filter === $value)
                            >
                                {{ $label }}
                            </option>

                        @endforeach

                    </select>

                </div>

                <div>

                    <label for="purok">
                        Purok
                    </label>

                    <select
                        id="purok"
                        name="purok"
                        class="form-select"
                    >

                        <option value="">
                            All Puroks
                        </option>

                        @foreach ($purokOptions as $purokOption)

                            <option
                                value="{{ $purokOption }}"
                                @selected(
                                    (string) $purok
                                    ===
                                    (string) $purokOption
                                )
                            >
                                Purok {{ $purokOption }}
                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="go">

                    <button
                        type="submit"
                        class="bn-btn"
                    >
                        <i class="bi bi-search"></i>
                        Apply filters
                    </button>

                </div>

            </form>

        </div>

        {{-- Selection form --}}

        <form
            method="POST"
            action="{{ route('relief-assistance.beneficiaries.confirm', $operation) }}"
            id="beneficiarySelectionForm"
        >

            @csrf

            <div class="bn-tools">

                <div class="t">

                    Select residents

                    <small>
                        Tap a row or its checkbox.
                        Confirmed residents are locked.
                    </small>

                </div>

                <div class="bn-pills">

                    <button
                        type="button"
                        class="bn-mini"
                        id="selectAllBtn"
                    >
                        <i class="bi bi-check2-square"></i>
                        Select all
                    </button>

                    <button
                        type="button"
                        class="bn-mini"
                        id="clearSelectionBtn"
                    >
                        <i class="bi bi-x-lg"></i>
                        Clear
                    </button>

                </div>

            </div>

            <div class="bn-scroll">

                <table class="bn-table">

                    <thead>

                        <tr>

                            <th class="pick">
                                Select
                            </th>

                            <th>
                                Resident
                            </th>

                            <th>
                                Relationship
                            </th>

                            <th>
                                Household
                            </th>

                            <th>
                                Household head
                            </th>

                            <th>
                                Purok
                            </th>

                            <th>
                                Address
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($displayRows as $row)

                            @php

                                $residentId =
                                    (int) $row['resident_id'];

                                $householdId =
                                    (int) $row['household_id'];

                                $disabled =
                                    $row['is_encoded']
                                    ||
                                    $row['has_voucher'];

                                $resident =
                                    $row['resident'];

                                $residentAge =
                                    $row['resident_age'];

                            @endphp

                            <tr
                                data-row
                                class="{{ $disabled ? 'is-locked' : '' }}"
                            >

                                <td class="pick">

                                    @if ($disabled)

                                        <input
                                            type="checkbox"
                                            class="beneficiary-checkbox"
                                            disabled
                                            aria-label="Already confirmed"
                                        >

                                    @else

                                        <input
                                            type="checkbox"
                                            class="beneficiary-checkbox"
                                            name="resident_ids[]"
                                            value="{{ $residentId }}"
                                            aria-label="Select {{ $row['resident_name'] }}"
                                        >

                                    @endif

                                </td>

                                <td>

                                    <div class="bn-strong">
                                        {{ $row['resident_name'] }}
                                    </div>

                                    <div class="bn-muted">

                                        Resident ID:
                                        {{ $residentId }}

                                        @if ($residentAge !== null)

                                            &middot;
                                            Age {{ $residentAge }}

                                        @endif

                                        @if ($resident && $resident->sex)

                                            &middot;
                                            {{ $resident->sex }}

                                        @endif

                                    </div>

                                </td>

                                <td>

                                    @if (
                                        trim(
                                            (string) (
                                                $row['relationship']
                                                ?? ''
                                            )
                                        ) !== ''
                                    )

                                        {{ $row['relationship'] }}

                                    @else

                                        <span class="bn-muted">
                                            Household Member
                                        </span>

                                    @endif

                                </td>

                                <td>

                                    <div class="bn-strong">
                                        {{ $row['household_number'] }}
                                    </div>

                                    <div class="bn-muted">
                                        Household ID:
                                        {{ $householdId }}
                                    </div>

                                </td>

                                <td>

                                    <div class="bn-strong">
                                        {{ $row['household_head_name'] }}
                                    </div>

                                </td>

                                <td>

                                    <span class="bn-loc">

                                        <i class="bi bi-geo-alt-fill"></i>

                                        @if (
                                            trim(
                                                (string) (
                                                    $row['purok']
                                                    ?? ''
                                                )
                                            ) !== ''
                                        )

                                            Purok
                                            {{ $row['purok'] }}

                                        @else

                                            Unassigned

                                        @endif

                                    </span>

                                </td>

                                <td>

                                    @if (
                                        trim(
                                            (string) (
                                                $row['address']
                                                ?? ''
                                            )
                                        ) !== ''
                                    )

                                        {{ $row['address'] }}

                                    @else

                                        <span
                                            class="bn-muted"
                                            style="margin:0"
                                        >
                                            No address recorded
                                        </span>

                                    @endif

                                </td>

                                <td>

                                    <span
                                        class="bn-badge {{ $row['status_class'] }}"
                                    >
                                        {{ $row['display_status'] }}
                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8">

                                    <div class="bn-empty">

                                        <div class="ic">
                                            <i class="bi bi-search"></i>
                                        </div>

                                        <h3 class="bn-display">
                                            No matching resident records
                                        </h3>

                                        <p>
                                            No active resident records
                                            match the selected target group,
                                            Purok, or search. Try other
                                            filters.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="bn-confirm">

                <div>

                    <div class="c">

                        <b id="selectedCount">
                            0
                        </b>

                        resident(s) selected

                    </div>

                    <div class="s">
                        Select only residents confirmed through
                        the completed field assessment.
                    </div>

                </div>

                <button
                    type="submit"
                    class="bn-btn"
                    id="confirmBeneficiariesBtn"
                    disabled
                >
                    <i class="bi bi-person-check-fill"></i>
                    Confirm selected beneficiaries
                </button>

            </div>

        </form>

    </section>

    {{-- ============ CONFIRMED BENEFICIARIES ============ --}}

    <section
        class="bn-panel"
        aria-labelledby="bnConfirmedTitle"
    >

        <div class="bn-head">

            <div>

                <h2
                    id="bnConfirmedTitle"
                    class="bn-display"
                >
                    Confirmed beneficiaries
                </h2>

                <p>
                    Manually encoded from the completed field assessment.
                </p>

            </div>

            <div class="bn-pills">

                <span class="bn-badge is-green">
                    {{ $confirmedBeneficiaries->count() }}
                    confirmed
                </span>

                @if ($voucherGeneratedCount > 0)

                    <span class="bn-badge is-ube">
                        {{ $voucherGeneratedCount }}
                        voucher generated
                    </span>

                @endif

            </div>

        </div>

        @if ($confirmedBeneficiaries->isEmpty())

            <div
                class="bn-empty"
                style="border-top:1px solid var(--line)"
            >

                <div class="ic">
                    <i class="bi bi-person-x"></i>
                </div>

                <h3 class="bn-display">
                    No confirmed beneficiaries yet
                </h3>

                <p>
                    Select residents above after the field assessment
                    is complete.
                </p>

            </div>

        @else

            <div class="bn-scroll">

                <table class="bn-table">

                    <thead>

                        <tr>

                            <th class="pick">
                                #
                            </th>

                            <th>
                                Beneficiary resident
                            </th>

                            <th>
                                Household
                            </th>

                            <th>
                                Household head
                            </th>

                            <th>
                                Purok
                            </th>

                            <th>
                                Verification
                            </th>

                            <th>
                                Assistance
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach (
                            $confirmedBeneficiaries
                            as $index => $beneficiary
                        )

                            @php

                                $beneficiaryHousehold =
                                    $beneficiary->household
                                    ?? null;

                                $beneficiaryResident =
                                    $beneficiary->resident
                                    ?? null;

                                $beneficiaryHead =
                                    $beneficiaryHousehold
                                        ? $beneficiaryHousehold->householdHead
                                        : null;

                                $beneficiaryStatus =
                                    strtolower(
                                        (string) (
                                            $beneficiary->assistance_status
                                            ?? 'awaiting_assistance'
                                        )
                                    );

                                $beneficiaryStatusMeta =
                                    match (
                                        $beneficiaryStatus
                                    ) {

                                        'allocated' => [
                                            'label' => 'Allocated',
                                            'class' => 'is-ube',
                                        ],

                                        'voucher_generated' => [
                                            'label' => 'Voucher Generated',
                                            'class' => 'is-ube',
                                        ],

                                        'distributed' => [
                                            'label' => 'Distributed',
                                            'class' => 'is-mango',
                                        ],

                                        'claimed' => [
                                            'label' => 'Claimed',
                                            'class' => 'is-green',
                                        ],

                                        default => [
                                            'label' => 'Awaiting Assistance',
                                            'class' => 'is-mango',
                                        ],
                                    };

                            @endphp

                            <tr>

                                <td
                                    class="pick"
                                    style="color:var(--grey)"
                                >
                                    {{ $index + 1 }}
                                </td>

                                <td>

                                    @if ($beneficiaryResident)

                                        <div class="bn-strong">
                                            {{ $fullName($beneficiaryResident) }}
                                        </div>

                                        <div class="bn-muted">

                                            Resident ID:
                                            {{ $beneficiaryResident->id }}

                                            @php
                                                $confirmedAge =
                                                    $getAge(
                                                        $beneficiaryResident
                                                    );
                                            @endphp

                                            @if ($confirmedAge !== null)

                                                &middot;
                                                Age
                                                {{ $confirmedAge }}

                                            @endif

                                        </div>

                                    @else

                                        <div class="bn-strong">
                                            Resident record unavailable
                                        </div>

                                        <div class="bn-muted">
                                            Legacy household-based record
                                        </div>

                                    @endif

                                </td>

                                <td>

                                    <div class="bn-strong">
                                        {{
                                            $beneficiaryHousehold
                                                ->household_number
                                                ?? '—'
                                        }}
                                    </div>

                                    <div class="bn-muted">
                                        Household ID:
                                        {{ $beneficiary->household_id ?? '—' }}
                                    </div>

                                </td>

                                <td>

                                    {{
                                        $beneficiaryHead
                                            ? $fullName(
                                                $beneficiaryHead
                                            )
                                            : 'No household head assigned'
                                    }}

                                </td>

                                <td>

                                    <span class="bn-loc">

                                        <i class="bi bi-geo-alt"></i>

                                        Purok
                                        {{
                                            $beneficiaryHousehold->purok
                                            ?? '—'
                                        }}

                                    </span>

                                </td>

                                <td>

                                    @if ($beneficiary->validated_at)

                                        <div class="bn-strong">

                                            {{
                                                \Carbon\Carbon::parse(
                                                    $beneficiary->validated_at
                                                )->format('M d, Y')
                                            }}

                                        </div>

                                        <div class="bn-muted">

                                            {{
                                                \Carbon\Carbon::parse(
                                                    $beneficiary->validated_at
                                                )->format('h:i A')
                                            }}

                                        </div>

                                    @else

                                        <span
                                            class="bn-muted"
                                            style="margin:0"
                                        >
                                            Not recorded
                                        </span>

                                    @endif

                                </td>

                                <td>

                                    @if (
                                        (int) (
                                            $beneficiary->allocated_quantity
                                            ?? 0
                                        ) > 0
                                    )

                                        <span class="bn-strong">

                                            {{
                                                number_format(
                                                    (float) (
                                                        $beneficiary->allocated_quantity
                                                    ),
                                                    0
                                                )
                                            }}

                                        </span>

                                        {{
                                            $operation->quantity_unit
                                            ?? 'unit(s)'
                                        }}

                                    @else

                                        <span
                                            class="bn-muted"
                                            style="margin:0"
                                        >
                                            Not allocated
                                        </span>

                                    @endif

                                </td>

                                <td>

                                    <span
                                        class="bn-badge {{ $beneficiaryStatusMeta['class'] }}"
                                    >
                                        {{
                                            $beneficiaryStatusMeta['label']
                                        }}
                                    </span>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            {{-- Bulk voucher area --}}

            <div class="bn-bulk">

                <div>

                    <div class="t">

                        <i
                            class="bi bi-ticket-perforated-fill"
                            style="color:var(--ube)"
                        ></i>

                        Ready for vouchers

                    </div>

                    <div class="s">

                        <strong>
                            {{ $voucherReadyBeneficiaries->count() }}
                        </strong>

                        {{
                            $voucherReadyBeneficiaries->count() === 1
                                ? 'resident is'
                                : 'residents are'
                        }}
                        ready.

                        Residents that already have an active
                        voucher are left out.

                    </div>

                </div>

                @if (
                    $voucherReadyBeneficiaries->isNotEmpty()
                )

                    <form
                        method="POST"
                        action="{{ route('relief-vouchers.generate') }}"
                        id="bulkVoucherGenerationForm"
                        class="m-0"
                    >

                        @csrf

                        <input
                            type="hidden"
                            name="operation_id"
                            value="{{ $operation->id }}"
                        >

                        @foreach (
                            $voucherReadyBeneficiaries
                            as $beneficiary
                        )

                            <input
                                type="hidden"
                                name="beneficiary_ids[]"
                                value="{{ $beneficiary->id }}"
                            >

                        @endforeach

                        <button
                            type="submit"
                            class="bn-btn"
                            id="previewBulkVouchersBtn"
                        >
                            <i class="bi bi-printer-fill"></i>
                            Preview &amp; print bulk vouchers
                        </button>

                    </form>

                @else

                    <span class="bn-badge is-grey">
                        No new vouchers ready
                    </span>

                @endif

            </div>

        @endif

    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    var checkboxes = Array.from(
        document.querySelectorAll(
            '.beneficiary-checkbox:not(:disabled)'
        )
    );

    var selectedCount =
        document.getElementById(
            'selectedCount'
        );

    var confirmButton =
        document.getElementById(
            'confirmBeneficiariesBtn'
        );

    var selectAllButton =
        document.getElementById(
            'selectAllBtn'
        );

    var clearSelectionButton =
        document.getElementById(
            'clearSelectionBtn'
        );

    var selectionForm =
        document.getElementById(
            'beneficiarySelectionForm'
        );

    var bulkVoucherForm =
        document.getElementById(
            'bulkVoucherGenerationForm'
        );

    var bulkVoucherButton =
        document.getElementById(
            'previewBulkVouchersBtn'
        );

    function selectedBoxes() {

        return checkboxes.filter(
            function (checkbox) {
                return checkbox.checked;
            }
        );
    }

    function updateSelection() {

        var count =
            selectedBoxes().length;

        checkboxes.forEach(
            function (checkbox) {

                var row =
                    checkbox.closest('tr');

                if (row) {

                    row.classList.toggle(
                        'is-picked',
                        checkbox.checked
                    );
                }
            }
        );

        if (selectedCount) {

            selectedCount.textContent =
                count;
        }

        if (confirmButton) {

            confirmButton.disabled =
                count === 0;
        }
    }

    if (selectAllButton) {

        selectAllButton.addEventListener(
            'click',
            function () {

                checkboxes.forEach(
                    function (checkbox) {
                        checkbox.checked = true;
                    }
                );

                updateSelection();
            }
        );
    }

    if (clearSelectionButton) {

        clearSelectionButton.addEventListener(
            'click',
            function () {

                checkboxes.forEach(
                    function (checkbox) {
                        checkbox.checked = false;
                    }
                );

                updateSelection();
            }
        );
    }

    checkboxes.forEach(
        function (checkbox) {

            checkbox.addEventListener(
                'change',
                updateSelection
            );

        }
    );

    /*
     * Tap anywhere on an unlocked row to toggle its checkbox.
     */
    document
        .querySelectorAll('tr[data-row]')
        .forEach(
            function (row) {

                row.addEventListener(
                    'click',
                    function (event) {

                        if (
                            event.target.closest(
                                'a, button, input, label'
                            )
                        ) {
                            return;
                        }

                        var checkbox =
                            row.querySelector(
                                '.beneficiary-checkbox:not(:disabled)'
                            );

                        if (checkbox) {

                            checkbox.checked =
                                !checkbox.checked;

                            updateSelection();
                        }
                    }
                );

            }
        );

    if (selectionForm) {

        selectionForm.addEventListener(
            'submit',
            function (event) {

                var selected =
                    selectedBoxes();

                if (selected.length === 0) {

                    event.preventDefault();

                    alert(
                        'Please select at least one resident that was confirmed during the field assessment.'
                    );

                    return;
                }

                var confirmed =
                    confirm(
                        'You selected '
                        + selected.length
                        + ' resident(s) as actual beneficiaries.\n\n'
                        + 'Continue with beneficiary encoding?'
                    );

                if (!confirmed) {

                    event.preventDefault();
                }
            }
        );
    }

    if (bulkVoucherForm) {

        bulkVoucherForm.addEventListener(
            'submit',
            function () {

                if (bulkVoucherButton) {

                    bulkVoucherButton.disabled =
                        true;

                    bulkVoucherButton.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>Preparing vouchers...';
                }
            }
        );
    }

    updateSelection();
});
</script>

@endsection