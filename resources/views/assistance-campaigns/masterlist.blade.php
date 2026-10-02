@extends('layouts.app')

@section('title', 'Masterlist Workspace | e-Poblacion')

@section('content')

@php
    // ---------------- SAFE OPERATION ----------------
    $operation = $operation ?? null;

    if (!$operation) {
        abort(404);
    }

    $operationCode = $operation->operation_code ?? ('RA-' . str_pad($operation->id, 4, '0', STR_PAD_LEFT));
    $operationName = $operation->operation_name ?? 'Relief Assistance Operation';

    // ---------------- FILTER VALUES ----------------
    $currentSearch = $search ?? request('search', '');
    $currentPurok = $purok ?? request('purok', '');
    $currentFilter = $filter ?? request('filter', 'all');

    $filterOptions = $filterOptions ?? [
        'all' => 'All',
        'household_heads' => 'Household Heads',
        'students' => 'Students',
        'senior_citizens' => 'Senior Citizens',
    ];

    $filterIcons = [
        'all' => 'bi-people',
        'household_heads' => 'bi-person-badge',
        'students' => 'bi-mortarboard',
        'senior_citizens' => 'bi-person-heart',
    ];

    $filterHints = [
        'all' => 'All matching active residents.',
        'household_heads' => 'Residents recorded as household heads.',
        'students' => 'Residents with occupation recorded as Student.',
        'senior_citizens' => 'Residents aged 60 and above.',
    ];

    // ---------------- DATA SAFETY ----------------
    $masterlistRecords = $masterlistRecords ?? collect();
    $households = $households ?? collect();
    $purokOptions = $purokOptions ?? [];
    $selectedHouseholdIds = $selectedHouseholdIds ?? [];

    if (!($masterlistRecords instanceof \Illuminate\Support\Collection)) {
        $masterlistRecords = collect($masterlistRecords);
    }

    $recordCount = $masterlistRecords->count();

    $householdCount = $masterlistRecords->pluck('household_id')->filter()->unique()->count();

    $filterLabel = $filterOptions[$currentFilter] ?? ucfirst(str_replace('_', ' ', $currentFilter));

    // Opens the system-generated preview. It does NOT open the print dialog.
    $masterlistPreviewUrl = route('relief-assistance.masterlist.print', [
        'operation_id' => $operation->id,
        'search' => $currentSearch,
        'purok' => $currentPurok,
        'filter' => $currentFilter,
    ]);

    $masterlistUrl = route('relief-assistance.masterlist', ['operation_id' => $operation->id]);

    $joinName = fn ($person) => trim(implode(' ', array_filter([
        data_get($person, 'first_name'),
        data_get($person, 'middle_name'),
        data_get($person, 'last_name'),
        data_get($person, 'suffix'),
    ])));
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">

<style>
    .ml {
        --ink: #1c1b33; --ink-2: #5e5c78; --line: #e3e1ee; --wash: #f5f4fa; --card: #fff;
        --ube: #5a3fd6; --ube-soft: #ebe7fb; --mango: #ffb020; --mango-soft: #fff1d0; --grey: #8a889f;
        max-width: 1320px; margin: 0 auto; color: var(--ink);
        font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif;
    }
    .ml *, .ml *::before, .ml *::after { box-sizing: border-box; }
    .ml h1, .ml h2, .ml p, .ml ol { margin: 0; padding: 0; }
    .ml a:focus-visible, .ml button:focus-visible, .ml summary:focus-visible { outline: 3px solid var(--mango); outline-offset: 2px; }
    .ml-display { font-family: 'Unbounded', 'Figtree', system-ui, sans-serif; letter-spacing: -.02em; }

    /* Header */
    .ml-crumb { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 12px; font-size: 13px; color: var(--ink-2); }
    .ml-crumb a { color: var(--ink-2); text-decoration: none; font-weight: 600; }
    .ml-crumb a:hover { color: var(--ube); text-decoration: underline; text-underline-offset: 3px; }
    .ml-hero { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 18px; margin-bottom: 18px; }
    .ml-hero h1 { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 14px; font-size: 28px; line-height: 1.15; font-weight: 800; }
    .ml-code { padding: 4px 12px; border-radius: 999px; background: var(--ube-soft); color: var(--ube); font-family: 'Figtree', sans-serif; letter-spacing: 0; font-size: 12.5px; font-weight: 700; }
    .ml-hero p { margin-top: 8px; font-size: 14.5px; color: var(--ink-2); }
    .ml-ghost { display: inline-flex; align-items: center; gap: 7px; padding: 10px 18px; border: 1.5px solid var(--line); border-radius: 999px; background: #fff; color: var(--ink); font-size: 13.5px; font-weight: 600; text-decoration: none; transition: border-color .15s, background .15s; }
    .ml-ghost:hover { border-color: var(--ube); background: var(--ube-soft); color: var(--ink); }

    .ml-note { display: flex; gap: 12px; align-items: flex-start; margin-bottom: 20px; padding: 13px 16px; border-radius: 14px; background: var(--mango-soft); font-size: 13px; line-height: 1.55; }
    .ml-note i { flex: none; margin-top: 1px; font-size: 17px; color: #9a6a00; }

    /* Panels */
    .ml-panel { border: 1px solid var(--line); border-radius: 18px; background: var(--card); margin-bottom: 20px; }
    .ml-filter { padding: 22px 24px; }
    .ml-label { display: block; margin-bottom: 9px; font-size: 13.5px; font-weight: 700; }

    /* Target group chips */
    .ml-groups { display: flex; flex-wrap: wrap; gap: 8px; }
    .ml-group { position: relative; margin: 0; cursor: pointer; }
    .ml-group input { position: absolute; inset: 0; width: 100%; height: 100%; margin: 0; opacity: 0; cursor: pointer; }
    .ml-group span { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border: 1.5px solid var(--line); border-radius: 999px; background: #fff; font-size: 13.5px; font-weight: 600; color: var(--ink-2); transition: border-color .15s, background .15s, color .15s; }
    .ml-group:hover span { border-color: var(--ube); }
    .ml-group input:checked + span { background: var(--ink); border-color: var(--ink); color: #fff; }
    .ml-group input:focus-visible + span { outline: 3px solid var(--mango); outline-offset: 2px; }
    .ml-hint { margin-top: 9px; font-size: 12.5px; color: var(--ink-2); min-height: 18px; }

    .ml-row { display: grid; grid-template-columns: 220px minmax(0, 1fr) auto; gap: 16px; align-items: end; margin-top: 20px; }
    .ml .form-select, .ml .form-control {
        width: 100%; min-height: 44px; padding: 10px 14px; border: 1.5px solid var(--line); border-radius: 12px; background-color: #fff;
        color: var(--ink); font-size: 14px; font-family: inherit; box-shadow: none;
    }
    .ml .form-select:focus, .ml .form-control:focus { border-color: var(--ube); box-shadow: 0 0 0 3px rgba(90, 63, 214, .15); outline: 0; }
    .ml-search { position: relative; }
    .ml-search i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--grey); font-size: 14px; pointer-events: none; }
    .ml-search .form-control { padding-left: 38px; }
    .ml-actions { display: flex; gap: 8px; }
    .ml-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 22px; border: 0; border-radius: 999px; background: var(--ube); color: #fff; font: inherit; font-size: 14px; font-weight: 700; text-decoration: none; cursor: pointer; transition: background .15s, transform .15s; }
    .ml-btn:hover { background: var(--ink); color: #fff; }
    .ml-btn:active { transform: scale(.98); }
    .ml-btn.is-off { background: #cfcce0; cursor: not-allowed; }
    .ml-clear { display: inline-flex; align-items: center; gap: 6px; min-height: 44px; padding: 0 16px; border-radius: 999px; border: 1.5px solid var(--line); background: #fff; color: var(--ink-2); font-size: 13.5px; font-weight: 600; text-decoration: none; }
    .ml-clear:hover { border-color: var(--ube); color: var(--ink); }

    /* Result bar */
    .ml-result { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 18px; padding: 20px 24px; }
    .ml-count { display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; }
    .ml-count b { font-family: 'Unbounded', 'Figtree', sans-serif; font-size: 30px; font-weight: 700; letter-spacing: -.02em; line-height: 1; }
    .ml-count span { font-size: 14px; color: var(--ink-2); }
    .ml-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
    .ml-tag { padding: 3px 11px; border: 1px solid var(--line); border-radius: 999px; background: var(--wash); font-size: 12.5px; font-weight: 600; }
    .ml-tag b { font-weight: 700; }
    .ml-preview-hint { margin-top: 8px; font-size: 12.5px; color: var(--ink-2); text-align: right; max-width: 300px; }

    /* Table */
    .ml-panel-head { padding: 20px 24px 16px; }
    .ml-panel-head h2 { font-size: 17px; font-weight: 800; }
    .ml-panel-head p { margin-top: 4px; font-size: 13px; color: var(--ink-2); line-height: 1.5; max-width: 640px; }
    .ml-scroll { overflow-x: auto; border-top: 1px solid var(--line); }
    .ml-table { width: 100%; min-width: 860px; border-collapse: collapse; }
    .ml-table th { padding: 12px 16px; background: var(--wash); border-bottom: 1px solid var(--line); font-size: 12.5px; font-weight: 600; color: var(--ink-2); text-align: left; white-space: nowrap; }
    .ml-table td { padding: 14px 16px; border-bottom: 1px solid var(--line); font-size: 13.5px; vertical-align: middle; }
    .ml-table tbody tr:last-child td { border-bottom: 0; }
    .ml-table tbody tr:hover { background: #faf9fe; }
    .ml-table .n { width: 56px; padding-left: 24px; color: var(--grey); }
    .ml-strong { font-weight: 700; }
    .ml-muted { margin-top: 2px; font-size: 12px; color: var(--ink-2); }
    .ml-rel { display: inline-block; padding: 2px 10px; border: 1px solid var(--line); border-radius: 999px; background: var(--wash); font-size: 12px; font-weight: 600; }
    .ml-foot { padding: 14px 24px; border-top: 1px solid var(--line); background: var(--wash); border-radius: 0 0 18px 18px; font-size: 13px; color: var(--ink-2); }
    .ml-foot b { color: var(--ink); }

    .ml-empty { padding: 56px 22px; text-align: center; border-top: 1px solid var(--line); }
    .ml-empty .ic { width: 60px; height: 60px; margin: 0 auto 14px; border-radius: 50%; display: grid; place-items: center; background: var(--ube-soft); color: var(--ube); font-size: 24px; }
    .ml-empty h3 { margin: 0 0 8px; font-size: 16px; font-weight: 700; }
    .ml-empty p { max-width: 440px; margin: 0 auto 18px; font-size: 13.5px; line-height: 1.6; color: var(--ink-2); }

    /* Help */
    .ml-help { border-top: 1px solid var(--line); }
    .ml-help summary { cursor: pointer; padding: 16px 0; font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 9px; list-style: none; }
    .ml-help summary::-webkit-details-marker { display: none; }
    .ml-help summary .chev { margin-left: auto; color: var(--grey); transition: transform .2s; }
    .ml-help[open] summary .chev { transform: rotate(180deg); }
    .ml-help ol { padding: 0 0 14px 22px; display: grid; gap: 8px; font-size: 13.5px; line-height: 1.55; color: var(--ink-2); }
    .ml-help ol b { color: var(--ink); }

    @media (max-width: 991.98px) {
        .ml-row { grid-template-columns: 1fr 1fr; }
        .ml-actions { grid-column: 1 / -1; }
        .ml-btn { flex: 1; }
        .ml-preview-hint { text-align: left; }
    }
    @media (max-width: 575.98px) {
        .ml-hero h1 { font-size: 22px; }
        .ml-row { grid-template-columns: 1fr; }
        .ml-filter, .ml-result { padding: 18px 16px; }
        .ml-panel-head { padding: 18px 16px 14px; }
        .ml-groups { flex-wrap: nowrap; overflow-x: auto; padding-bottom: 2px; }
        .ml-group { flex: none; }
        .ml-result .ml-btn { width: 100%; }
    }
    @media (prefers-reduced-motion: reduce) { .ml *, .ml *::before, .ml *::after { transition: none !important; } }
</style>

<div class="container-fluid py-4 ml">

    {{-- ============ HEADER ============ --}}
    <header class="ml-hero">
        <div>
            <nav class="ml-crumb" aria-label="Breadcrumb">
                <a href="{{ route('relief-vouchers.index') }}">Relief Assistance</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('relief-assistance.show', $operation) }}">Operation Details</a>
                <span aria-hidden="true">/</span>
                <span>Masterlist Workspace</span>
            </nav>
            <h1 class="ml-display">Masterlist Workspace <span class="ml-code">{{ $operationCode }}</span></h1>
            <p>{{ $operationName }}</p>
        </div>

        <a href="{{ route('relief-assistance.show', $operation) }}" class="ml-ghost">
            <i class="bi bi-arrow-left"></i> Back to operation
        </a>
    </header>

    <div class="ml-note">
        <i class="bi bi-person-check-fill"></i>
        <div>Use the filters to find residents for the printed field list. <strong>A match is not an approval.</strong> Staff choose the beneficiaries after the field assessment.</div>
    </div>

    {{-- ============ FILTERS ============ --}}
    <section class="ml-panel ml-filter" aria-label="Find records">
        <form method="GET" action="{{ $masterlistUrl }}">
            <input type="hidden" name="operation_id" value="{{ $operation->id }}">

            <fieldset style="border:0;padding:0;margin:0;min-width:0">
                <legend class="ml-label" style="float:none;padding:0">Target group</legend>
                <div class="ml-groups">
                    @foreach ($filterOptions as $value => $label)
                        <label class="ml-group">
                            <input type="radio" name="filter" value="{{ $value }}"
                                   data-hint="{{ $filterHints[$value] ?? 'Available records.' }}"
                                   {{ $currentFilter === $value ? 'checked' : '' }}>
                            <span><i class="bi {{ $filterIcons[$value] ?? 'bi-funnel' }}"></i> {{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="ml-hint" id="mlHint">{{ $filterHints[$currentFilter] ?? '' }}</div>
            </fieldset>

            <div class="ml-row">
                <div>
                    <label for="purok" class="ml-label">Purok</label>
                    <select name="purok" id="purok" class="form-select">
                        <option value="">All Puroks</option>
                        @foreach ($purokOptions as $purokValue => $purokLabel)
                            @php
                                // Supports ['1' => 'Purok 1'] and ['Purok 1', 'Purok 2']
                                $optionValue = is_array($purokOptions) ? $purokValue : $purokLabel;
                                $optionLabel = $purokLabel;
                            @endphp
                            <option value="{{ $optionValue }}" {{ (string) $currentPurok === (string) $optionValue ? 'selected' : '' }}>
                                {{ $optionLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="search" class="ml-label">Search records</label>
                    <div class="ml-search">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" id="search" class="form-control" value="{{ $currentSearch }}"
                               placeholder="Household no., resident, head, address...">
                    </div>
                </div>

                <div class="ml-actions">
                    <button type="submit" class="ml-btn"><i class="bi bi-funnel"></i> Apply filter</button>
                    <a href="{{ $masterlistUrl }}" class="ml-clear" title="Clear filters"><i class="bi bi-arrow-counterclockwise"></i> Clear</a>
                </div>
            </div>
        </form>
    </section>

    {{-- ============ RESULT + PREVIEW ============ --}}
    <section class="ml-panel ml-result" aria-label="Current result">
        <div>
            <div class="ml-count">
                <b>{{ number_format($recordCount) }}</b>
                <span>matching resident {{ $recordCount === 1 ? 'record' : 'records' }} from
                    <strong>{{ number_format($householdCount) }}</strong> {{ $householdCount === 1 ? 'household' : 'households' }}</span>
            </div>
            <div class="ml-tags">
                <span class="ml-tag">Group: <b>{{ $filterLabel }}</b></span>
                @if ($currentPurok !== '')
                    <span class="ml-tag">Purok: <b>{{ $currentPurok }}</b></span>
                @endif
                @if ($currentSearch !== '')
                    <span class="ml-tag">Search: <b>"{{ $currentSearch }}"</b></span>
                @endif
            </div>
        </div>

        <div>
            @if ($recordCount > 0)
                <a href="{{ $masterlistPreviewUrl }}" target="_blank" rel="noopener" class="ml-btn">
                    <i class="bi bi-eye"></i> Preview masterlist
                </a>
            @else
                <button type="button" class="ml-btn is-off" disabled><i class="bi bi-eye"></i> Preview masterlist</button>
            @endif
            <div class="ml-preview-hint">Opens a review page. Printing starts only when you press Print there.</div>
        </div>
    </section>

    {{-- ============ RESULTS TABLE ============ --}}
    <section class="ml-panel" aria-labelledby="mlTableTitle">
        <div class="ml-panel-head">
            <h2 id="mlTableTitle" class="ml-display">Matching resident records</h2>
            <p>One row is one resident. Household number, head, Purok, and address help BHW/Staff find the right household during the visit.</p>
        </div>

        @if ($recordCount > 0)

            <div class="ml-scroll">
                <table class="ml-table">
                    <thead>
                        <tr>
                            <th class="n">#</th>
                            <th>Target resident</th>
                            <th>Age</th>
                            <th>Relationship</th>
                            <th>Household no.</th>
                            <th>Household head</th>
                            <th>Purok</th>
                            <th>Address</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($masterlistRecords as $index => $record)

                            @php
                                $resident = data_get($record, 'resident');
                                $household = data_get($record, 'household');
                                $householdHead = data_get($record, 'household_head');

                                $residentName = $joinName($resident);
                                $headName = $joinName($householdHead);

                                $householdNumber = data_get($record, 'household_number') ?? data_get($household, 'household_number');
                                $purokName = data_get($record, 'purok') ?? data_get($household, 'purok');
                                $address = data_get($record, 'address') ?? data_get($household, 'address');
                                $relationship = data_get($record, 'relationship') ?? '—';

                                $age = data_get($resident, 'age');

                                if ($age === null && !empty(data_get($resident, 'birth_date'))) {
                                    try {
                                        $age = \Illuminate\Support\Carbon::parse(data_get($resident, 'birth_date'))->age;
                                    } catch (\Throwable $exception) {
                                        $age = null;
                                    }
                                }
                            @endphp

                            <tr>
                                <td class="n">{{ $index + 1 }}</td>
                                <td><div class="ml-strong">{{ $residentName ?: '—' }}</div></td>
                                <td>{{ $age !== null ? $age : '—' }}</td>
                                <td><span class="ml-rel">{{ $relationship }}</span></td>
                                <td class="ml-strong">{{ $householdNumber ?: '—' }}</td>
                                <td>{{ $headName ?: '—' }}</td>
                                <td>{{ $purokName ?: '—' }}</td>
                                <td><span class="ml-muted" style="margin:0">{{ $address ?: '—' }}</span></td>
                            </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="ml-foot">
                Showing <b>{{ number_format($recordCount) }}</b> matching resident {{ $recordCount === 1 ? 'record' : 'records' }}
                across <b>{{ number_format($householdCount) }}</b> {{ $householdCount === 1 ? 'household' : 'households' }}.
            </div>

        @else

            <div class="ml-empty">
                <div class="ic"><i class="bi bi-search"></i></div>
                <h3 class="ml-display">No matching records found</h3>
                <p>No active resident records matched the current target group, Purok, or search. Try another filter or clear the search.</p>
                <a href="{{ $masterlistUrl }}" class="ml-ghost"><i class="bi bi-arrow-counterclockwise"></i> Clear filters</a>
            </div>

        @endif
    </section>

    {{-- ============ WHAT HAPPENS NEXT (one place only) ============ --}}
    <details class="ml-help">
        <summary>
            <i class="bi bi-question-circle"></i> What happens after this list?
            <i class="bi bi-chevron-down chev"></i>
        </summary>
        <ol>
            <li><b>Preview and print.</b> Review the list on the preview page, then press Print there.</li>
            <li><b>Field assessment.</b> BHW/Staff bring the printed list to the households and verify each one.</li>
            <li><b>Return the list.</b> The completed masterlist goes back to the Barangay Hall.</li>
            <li><b>Encode beneficiaries.</b> Authorized staff open this operation's Beneficiary Encoding and select the actual recipients. Vouchers are made only after that.</li>
        </ol>
    </details>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var radios = document.querySelectorAll('.ml-group input[type="radio"]');
        var hint = document.getElementById('mlHint');
        if (!hint) { return; }

        radios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                hint.textContent = radio.dataset.hint || '';
            });
        });
    });
</script>

@endsection
