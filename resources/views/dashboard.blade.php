@extends('layouts.app')

@section('title', 'Dashboard | e-Poblacion')

@push('styles')
<style>
/* =========================================================
   e-POBLACION — "overview" dashboard
   Blue KPI cards, soft grey chart cards, right activity rail.
   Charts are pure SVG/CSS built from your real data (no libs).
   ========================================================= */

.pf {
    --font:'Inter','Segoe UI',system-ui,-apple-system,Roboto,Helvetica,Arial,sans-serif;
    --blue:#0b46c4; --blue-2:#2f7bff; --ink:#111318; --soft:#7b8190; --bg:#fff;
    --card:#f6f6f8; --line:#ececf1; --ok:#16a06a; --bad:#d23f35; --warn:#d98a00;
    font-family:var(--font); color:var(--ink);
    display:grid; grid-template-columns:minmax(0,1fr) 300px; gap:0 28px;
}
html[data-theme="dark"] .pf {
    --blue:#2f6bff; --blue-2:#6f9bff; --ink:#f1f3f8; --soft:#98a0b3; --bg:#12151d;
    --card:#1a1e29; --line:#262b38; --ok:#4fd6a0; --bad:#ff8a80; --warn:#ffc24d;
}
.pf a { color:inherit; text-decoration:none; }
.pf :focus-visible { outline:3px solid var(--blue-2); outline-offset:2px; }
.pf-main { min-width:0; }
.pf h2,.pf h3 { margin:0; }

/* ---------- Top bar ---------- */
.pf-top { display:flex; align-items:center; justify-content:space-between; gap:16px; padding-bottom:16px; border-bottom:1px solid var(--line); margin-bottom:22px; }
.crumb { font-size:14px; color:var(--soft); }
.crumb b { margin-left:6px; color:var(--ink); font-weight:600; }
.pf-search { position:relative; width:min(100%,260px); }
.pf-search svg { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--soft); font-size:13px; }
.pf-search input { width:100%; height:36px; padding:0 34px 0 34px; border:0; border-radius:999px; background:var(--card); color:var(--ink); font:500 13.5px var(--font); }
.pf-search input:focus { outline:2px solid var(--blue-2); }
.pf-search kbd { position:absolute; right:8px; top:50%; transform:translateY(-50%); padding:1px 6px; border-radius:5px; background:var(--bg); color:var(--soft); font:600 11px var(--font); }

.pf-ov { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
.pf-ov h2 { font-size:15px; font-weight:600; }
.pf-ov span { font-size:13px; color:var(--soft); }
.pf-hello { margin:0 0 18px; font-size:13.5px; color:var(--soft); }
.pf-hello b { color:var(--ink); }

/* ---------- KPI cards ---------- */
.kpis { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:20px; }
.kpi {
    display:block; padding:18px 20px; border-radius:18px; color:#fff !important;
    background:linear-gradient(135deg,var(--blue),#0a38a0); transition:transform .18s,box-shadow .18s;
}
.kpi:hover { transform:translateY(-3px); box-shadow:0 14px 28px rgba(11,70,196,.28); }
.kpi-l { font-size:13px; font-weight:500; opacity:.92; }
.kpi-row { display:flex; align-items:baseline; justify-content:space-between; gap:8px; margin-top:12px; }
.kpi-n { font-size:30px; font-weight:600; letter-spacing:-.02em; line-height:1; }
.kpi-d { font-size:12px; font-weight:500; opacity:.9; white-space:nowrap; }

/* ---------- Cards ---------- */
.row { display:grid; gap:16px; margin-bottom:16px; }
.r-21 { grid-template-columns:minmax(0,2fr) minmax(0,1fr); }
.r-11 { grid-template-columns:1fr 1fr; }
.card-s { padding:20px; border-radius:18px; background:var(--card); min-width:0; }
.card-s h3 { font-size:14px; font-weight:600; }
.card-h { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:16px; }
.legend { display:flex; gap:14px; font-size:12px; color:var(--soft); }
.legend i { display:inline-block; width:7px; height:7px; margin-right:6px; border-radius:50%; background:var(--blue); }
.empty { padding:34px 8px; text-align:center; font-size:13.5px; color:var(--soft); }

/* monthly completion */
.mc-big { display:flex; align-items:baseline; flex-wrap:wrap; gap:4px 6px; margin-bottom:16px; }
.mc-big b { font-size:56px; font-weight:600; letter-spacing:-.03em; line-height:1; }
.mc-big span { font-size:24px; font-weight:600; color:var(--soft); }
.mc-big small { flex-basis:100%; margin-top:6px; font-size:13px; color:var(--soft); }
.meter { display:grid; grid-template-columns:repeat(20,1fr); gap:4px; }
.meter i { height:30px; border-radius:4px; background:var(--line); }
.meter i.on { background:var(--blue); }
.mc-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-top:20px; }
.mc-stats > * { display:block; padding:14px; border-radius:14px; background:var(--bg); transition:transform .15s; }
.mc-stats a:hover { transform:translateY(-2px); }
.mc-stats b { display:block; font-size:20px; font-weight:600; letter-spacing:-.01em; }
.mc-stats span { display:block; margin-top:3px; font-size:12px; color:var(--soft); }

/* icons (inline SVG: works with no icon font / no internet) */
.ic { width:1em; height:1em; fill:none; stroke:currentColor; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; vertical-align:-.125em; }

/* connection chip + toast (fixed size: never shifts the layout) */
.top-r { display:flex; align-items:center; gap:10px; }
.conn { display:inline-flex; align-items:center; justify-content:center; gap:7px; min-width:92px; height:36px; padding:0 12px; border-radius:999px; background:var(--card); font-size:12.5px; font-weight:600; }
.conn i { width:8px; height:8px; border-radius:50%; background:var(--ok); }
.conn[data-state="offline"] i { background:var(--bad); }
.conn[data-state="offline"] { color:var(--bad); }
.toast { position:fixed; left:50%; bottom:22px; z-index:1080; transform:translate(-50%,20px); padding:11px 18px; border-radius:12px; background:#111318; color:#fff; font-size:13.5px; font-weight:500; box-shadow:0 10px 30px rgba(0,0,0,.25); opacity:0; pointer-events:none; transition:opacity .25s,transform .25s; }
.toast.show { opacity:1; transform:translate(-50%,0); }
html[data-theme="dark"] .toast { background:#f1f3f8; color:#111318; }

/* status bars */
.hb { display:grid; grid-template-columns:90px 1fr 28px; align-items:center; gap:10px; margin-bottom:16px; font-size:13px; }
.hb:last-child { margin-bottom:0; }
.hb-t { height:6px; border-radius:99px; background:var(--line); overflow:hidden; }
.hb-t i { display:block; width:0; height:100%; border-radius:inherit; transition:width 1s cubic-bezier(.2,.8,.2,1); }
.hb b { text-align:right; font-weight:600; }

/* donut */
.donut-wrap { display:flex; align-items:center; gap:26px; }
.donut { position:relative; width:150px; height:150px; flex-shrink:0; }
.donut svg { width:100%; height:100%; transform:rotate(-90deg); }
.donut circle { fill:none; stroke-width:16; transition:stroke-width .15s; }
.donut circle.seg:hover { stroke-width:19; }
.donut-c { position:absolute; inset:0; display:grid; place-content:center; text-align:center; }
.donut-c b { font-size:24px; font-weight:600; letter-spacing:-.02em; }
.donut-c small { font-size:11.5px; color:var(--soft); }
.dl { flex:1; min-width:0; }
.dl a { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:6px 0; font-size:13px; }
.dl a:hover b { color:var(--blue-2); }
.dl i { display:inline-block; width:8px; height:8px; margin-right:8px; border-radius:50%; }
.dl b { font-weight:600; }

/* vertical bars */
.vb { display:flex; align-items:flex-end; justify-content:space-between; gap:12px; height:170px; padding-top:8px; }
.vb-c { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; height:100%; min-width:0; }
.vb-b { width:100%; max-width:38px; height:0; border-radius:8px; background:var(--blue); transition:height 1s cubic-bezier(.2,.8,.2,1),filter .15s; }
.vb-c:hover .vb-b { filter:brightness(1.25); }
.vb-v { margin-bottom:6px; font-size:12px; font-weight:600; }
.vb-l { margin-top:8px; width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; text-align:center; font-size:11px; color:var(--soft); }

/* table */
.tbl-tools { display:flex; align-items:center; gap:10px; }
.pf-table { width:100%; border-collapse:collapse; font-size:13.5px; }
.pf-table th { padding:10px 12px; text-align:left; font-size:12px; font-weight:600; color:var(--soft); white-space:nowrap; border-bottom:1px solid var(--line); }
.pf-table td { padding:14px 12px; border-bottom:1px solid var(--line); }
.pf-table tbody tr:last-child td { border-bottom:0; }
.pf-table tr[data-href] { cursor:pointer; transition:background-color .12s; }
.pf-table tr[data-href]:hover { background:var(--bg); }
.pf-table .code { font-weight:600; white-space:nowrap; }
.pf-table .muted { color:var(--soft); }
.pill { display:inline-block; padding:3px 11px; border-radius:99px; font-size:12px; font-weight:600; white-space:nowrap; }
.pill.ok { background:color-mix(in srgb,var(--ok) 15%,transparent); color:var(--ok); }
.pill.bad { background:color-mix(in srgb,var(--bad) 15%,transparent); color:var(--bad); }
.pill.warn { background:color-mix(in srgb,var(--warn) 17%,transparent); color:var(--warn); }
.go { display:inline-grid; place-items:center; width:30px; height:30px; border-radius:50%; background:var(--bg); color:var(--soft); transition:all .15s; }
tr:hover .go { background:var(--blue); color:#fff; }
.scroll-x { overflow-x:auto; }
#docNone { display:none; }

/* ---------- Right rail ---------- */
.pf-rail { padding-left:28px; border-left:1px solid var(--line); }
.rail-sec { margin-bottom:28px; }
.rail-sec h3 { margin-bottom:14px; font-size:14px; font-weight:600; }
.n-item,.a-item { display:flex; gap:12px; align-items:flex-start; padding:8px 8px; margin:0 -8px; border-radius:12px; transition:background-color .15s; }
.n-item:hover,.a-item:hover { background:var(--card); }
.n-ic { flex-shrink:0; display:grid; place-items:center; width:28px; height:28px; border-radius:8px; background:var(--blue); color:#fff; font-size:13px; }
.n-ic.red { background:var(--bad); } .n-ic.green { background:var(--ok); }
.rt { font-size:13.5px; line-height:1.35; }
.rs { font-size:12px; color:var(--soft); }
.av { flex-shrink:0; display:grid; place-items:center; width:28px; height:28px; border-radius:50%; color:#fff; font-size:11px; font-weight:700; }

/* ---------- Responsive ---------- */
@media (max-width:1399.98px) { .pf { grid-template-columns:minmax(0,1fr); } .pf-rail { margin-top:12px; padding:24px 0 0; border-left:0; border-top:1px solid var(--line); display:grid; grid-template-columns:repeat(3,1fr); gap:24px; } .rail-sec { margin-bottom:0; } }
@media (max-width:1100px) { .mc-stats { grid-template-columns:repeat(2,1fr); } .kpis { grid-template-columns:repeat(2,1fr); } .r-21,.r-11 { grid-template-columns:1fr; } }
@media (max-width:767.98px) { .kpis { grid-template-columns:1fr; } .pf-rail { grid-template-columns:1fr; } .mc-big b { font-size:44px; } .donut-wrap { flex-direction:column; align-items:flex-start; } .pf-top { flex-wrap:wrap; } }
@media (prefers-reduced-motion:reduce) { .pf *,.chart .ln { transition:none !important; animation:none !important; stroke-dashoffset:0 !important; } }
@media print { .pf-search,.tbl-tools { display:none !important; } }
</style>
@endpush


@section('content')

@php
    use Illuminate\Support\Str;
    use App\Models\DocumentRequest as DR;

    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $firstName = Str::of(auth()->user()->name)->before(' ');

    $rate = max(0, min(100, (float) $monthlyCompletionRate));
    $activePct = $residentCount > 0 ? round(($activeResidentCount / $residentCount) * 100) : 0;

    $palette = ['#0b46c4', '#7a4bd1', '#1b8a5f', '#d98a00', '#c8372d', '#0a8aa6'];
    $tint = fn ($s) => $palette[crc32((string) $s) % count($palette)];
    $initials = fn ($n) => Str::of((string) $n)->explode(' ')->filter()->map(fn ($p) => Str::substr($p, 0, 1))->take(2)->implode('');

    $stateOf = fn ($d) => $d->workflow_stage === DR::STAGE_RELEASED ? 'released'
        : ($d->workflow_stage === DR::STAGE_THIRD_PARTY_DECLINED ? 'declined' : 'progress');

    /* ---- workflow donut ---- */
    $workflow = [
        ['name' => 'For Inspection',     'count' => $workflowCounts['forInspection'],    'color' => '#d98a00'],
        ['name' => 'For Approval',       'count' => $workflowCounts['forApproval'],      'color' => '#2f7bff'],
        ['name' => 'Ready for Printing', 'count' => $workflowCounts['readyForPrinting'], 'color' => '#7a4bd1'],
        ['name' => 'Ready for Release',  'count' => $workflowCounts['readyForRelease'],  'color' => '#16a06a'],
    ];
    $wfTotal = array_sum(array_column($workflow, 'count'));
    $C = 251.33; $acc = 0;

    /* ---- status + type breakdowns ---- */
    $states = $latestDocuments->map($stateOf)->countBy();
    $statusRows = [
        ['In progress',   $states->get('progress', 0), '#d98a00'],
        ['Released',      $states->get('released', 0), '#16a06a'],
        ['Not released',  $states->get('declined', 0), '#d23f35'],
    ];
    $statusMax = max(array_merge([1], array_column($statusRows, 1)));
    $byType = $latestDocuments->groupBy('document_type')->map->count()->sortDesc()->take(6);
    $typeMax = max(array_merge([1], $byType->values()->all()));

    /* ---- notifications ---- */
    $notes = [];
    if ($documentsAwaitingAction > 0) $notes[] = ['hourglass', '', number_format($documentsAwaitingAction) . ' ' . Str::plural('document', $documentsAwaitingAction) . ' need your action', 'Today'];
    if ($workflowCounts['readyForRelease'] > 0) $notes[] = ['box', 'green', $workflowCounts['readyForRelease'] . ' ready for release', 'Waiting at the counter'];
    if ($workflowCounts['thirdPartyDeclined'] > 0) $notes[] = ['userx', 'red', $workflowCounts['thirdPartyDeclined'] . ' third-party ' . Str::plural('request', $workflowCounts['thirdPartyDeclined']) . ' declined', now()->format('F')];
    $notes[] = ['check', 'green', number_format($documentsReleasedThisMonth) . ' released this month', now()->format('F Y')];
@endphp

<div class="pf">

<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false"><defs>
    <symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></symbol>
    <symbol id="i-up" viewBox="0 0 24 24"><path d="M7 17 17 7M8 7h9v9"/></symbol>
    <symbol id="i-right" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6"/></symbol>
    <symbol id="i-hourglass" viewBox="0 0 24 24"><path d="M6 3h12M6 21h12M7 3c0 5 5 6 5 9s-5 4-5 9M17 3c0 5-5 6-5 9s5 4 5 9"/></symbol>
    <symbol id="i-box" viewBox="0 0 24 24"><path d="m21 8-9-5-9 5v8l9 5 9-5zM3 8l9 5 9-5M12 13v8"/></symbol>
    <symbol id="i-userx" viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0M17 8l5 5m0-5-5 5"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></symbol>
</defs></svg>


<div class="pf-main">

    {{-- ===================== TOP BAR ===================== --}}
    <div class="pf-top">
        <div class="crumb">Dashboards <span aria-hidden="true">/</span> <b>Default</b></div>

        <div class="top-r">
        <span class="conn" id="connChip" data-state="online" role="status" aria-live="polite"><i></i><em style="font-style:normal">Online</em></span>

        @if ($latestDocuments->isNotEmpty())
            <label class="pf-search">
                <svg class="ic" aria-hidden="true"><use href="#i-search"/></svg>
                <input type="search" id="docSearch" placeholder="Search requests" autocomplete="off" aria-label="Search requests">
                <kbd aria-hidden="true">/</kbd>
            </label>
        @endif
        </div>
    </div>

    <div class="pf-ov">
        <h2>Overview</h2>
        <span>{{ now()->format('l, F j') }}</span>
    </div>
    <p class="pf-hello">{{ $greeting }}, <b>{{ $firstName }}</b>.
        @if ($documentsAwaitingAction > 0)
            {{ number_format($documentsAwaitingAction) }} {{ Str::plural('document', $documentsAwaitingAction) }} need your action today.
        @else
            You are all caught up.
        @endif
    </p>


    {{-- ===================== KPI CARDS ===================== --}}
    <section class="kpis">
        <a href="{{ route('residents.index') }}" class="kpi">
            <div class="kpi-l">Registered Residents</div>
            <div class="kpi-row">
                <span class="kpi-n" data-countup="{{ $residentCount }}">{{ number_format($residentCount) }}</span>
                <span class="kpi-d">All records <svg class="ic" aria-hidden="true"><use href="#i-up"/></svg></span>
            </div>
        </a>

        <a href="{{ route('residents.index', ['status' => 'Active']) }}" class="kpi">
            <div class="kpi-l">Active Residents</div>
            <div class="kpi-row">
                <span class="kpi-n" data-countup="{{ $activeResidentCount }}">{{ number_format($activeResidentCount) }}</span>
                <span class="kpi-d">{{ $activePct }}% <svg class="ic" aria-hidden="true"><use href="#i-up"/></svg></span>
            </div>
        </a>

        <a href="{{ route('documents.index') }}" class="kpi">
            <div class="kpi-l">Awaiting Action</div>
            <div class="kpi-row">
                <span class="kpi-n" data-countup="{{ $documentsAwaitingAction }}">{{ number_format($documentsAwaitingAction) }}</span>
                <span class="kpi-d">Needs processing <svg class="ic" aria-hidden="true"><use href="#i-up"/></svg></span>
            </div>
        </a>

        <a href="{{ route('documents.index', ['status' => 'released']) }}" class="kpi">
            <div class="kpi-l">Released This Month</div>
            <div class="kpi-row">
                <span class="kpi-n" data-countup="{{ $documentsReleasedThisMonth }}">{{ number_format($documentsReleasedThisMonth) }}</span>
                <span class="kpi-d">{{ $rate }}% done <svg class="ic" aria-hidden="true"><use href="#i-up"/></svg></span>
            </div>
        </a>
    </section>


    {{-- ===================== TREND + STATUS ===================== --}}
    <section class="row r-21">

        <div class="card-s">
            <div class="card-h">
                <h3>Monthly Completion</h3>
                <span class="rs">Updated {{ now()->format('g:i A') }}</span>
            </div>

            <div class="mc-big">
                <b data-countup="{{ $rate }}">{{ $rate }}</b><span>%</span>
                <small>of requests created this month are already released</small>
            </div>

            <div class="meter" role="img" aria-label="{{ $rate }} percent completed">
                @for ($i = 1; $i <= 20; $i++)
                    <i class="{{ $i <= round($rate / 5) ? 'on' : '' }}"></i>
                @endfor
            </div>

            <div class="mc-stats">
                <a href="{{ route('documents.index', ['status' => 'released']) }}">
                    <b>{{ number_format($documentsReleasedThisMonth) }}</b><span>Released this month</span>
                </a>
                <a href="{{ route('documents.index') }}">
                    <b>{{ number_format($documentsAwaitingAction) }}</b><span>Awaiting action</span>
                </a>
                <div>
                    <b>{{ $averageProcessingHours !== null ? $averageProcessingHours . ' h' : '—' }}</b><span>Avg. processing time</span>
                </div>
                <a href="{{ route('residents.index', ['status' => 'Active']) }}">
                    <b>{{ $activePct }}%</b><span>Residents active</span>
                </a>
            </div>
        </div>

        <div class="card-s">
            <div class="card-h"><h3>Requests by Status</h3></div>

            @foreach ($statusRows as $s)
                <div class="hb">
                    <span>{{ $s[0] }}</span>
                    <span class="hb-t"><i style="background:{{ $s[2] }}" data-width="{{ round(($s[1] / $statusMax) * 100) }}"></i></span>
                    <b>{{ $s[1] }}</b>
                </div>
            @endforeach

            <p class="rs" style="margin:18px 0 0">
                @if ($averageProcessingHours !== null)
                    Average processing time: <b style="color:var(--ink)">{{ $averageProcessingHours }} hour(s)</b>
                @else
                    No released documents this month yet.
                @endif
            </p>
        </div>

    </section>


    {{-- ===================== WORKFLOW + TYPES ===================== --}}
    <section class="row r-11">

        <div class="card-s">
            <div class="card-h"><h3>Document Workflow</h3><a href="{{ route('documents.index') }}" class="rs">View all</a></div>

            <div class="donut-wrap">
                <div class="donut">
                    <svg viewBox="0 0 100 100" aria-hidden="true">
                        <circle cx="50" cy="50" r="40" stroke="var(--line)"/>
                        @if ($wfTotal > 0)
                            @foreach ($workflow as $w)
                                @if ($w['count'] > 0)
                                    @php $len = ($w['count'] / $wfTotal) * $C; @endphp
                                    <circle class="seg" cx="50" cy="50" r="40" stroke="{{ $w['color'] }}"
                                            stroke-dasharray="{{ round(max(0, $len - 2), 2) }} {{ $C }}"
                                            stroke-dashoffset="{{ round(-$acc, 2) }}">
                                        <title>{{ $w['name'] }}: {{ $w['count'] }}</title>
                                    </circle>
                                    @php $acc += $len; @endphp
                                @endif
                            @endforeach
                        @endif
                    </svg>
                    <div class="donut-c"><b>{{ $wfTotal }}</b><small>in queue</small></div>
                </div>

                <div class="dl">
                    @foreach ($workflow as $w)
                        <a href="{{ route('documents.index') }}">
                            <span><i style="background:{{ $w['color'] }}"></i>{{ $w['name'] }}</span>
                            <b>{{ $wfTotal > 0 ? round(($w['count'] / $wfTotal) * 100, 1) : 0 }}%</b>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card-s">
            <div class="card-h"><h3>Requests by Document Type</h3></div>

            @if ($byType->isNotEmpty())
                <div class="vb">
                    @foreach ($byType as $type => $count)
                        <div class="vb-c" title="{{ $type }}: {{ $count }}">
                            <span class="vb-v">{{ $count }}</span>
                            <span class="vb-b" data-height="{{ round(($count / $typeMax) * 100) }}"></span>
                            <span class="vb-l">{{ Str::limit($type, 11, '…') }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty">No document requests yet.</div>
            @endif
        </div>

    </section>


    {{-- ===================== LATEST REQUESTS ===================== --}}
    <section class="card-s">

        <div class="card-h">
            <div>
                <h3>Latest Document Requests</h3>
                <div class="rs" style="margin-top:3px">Click a request to see its processing journey.</div>
            </div>
            <div class="tbl-tools"><a href="{{ route('documents.index') }}" class="rs">Open Documents →</a></div>
        </div>

        <div class="scroll-x">
            <table class="pf-table">
                <thead>
                    <tr>
                        <th>Request Code</th>
                        <th>Resident</th>
                        <th>Document</th>
                        <th>Current Stage</th>
                        <th>Status</th>
                        <th><span style="position:absolute;left:-9999px">View</span></th>
                    </tr>
                </thead>

                <tbody id="docBody">

                    @forelse ($latestDocuments as $document)

                        <tr data-href="{{ route('documents.show', $document) }}">
                            <td class="code">{{ $document->request_code }}</td>
                            <td>{{ $document->resident?->full_name ?? 'Resident unavailable' }}</td>
                            <td>{{ $document->document_type }}</td>
                            <td class="muted">{{ DR::workflowSteps()[$document->workflow_stage] ?? 'Request Created' }}</td>
                            <td>
                                @if ($stateOf($document) === 'released')
                                    <span class="pill ok">Released</span>
                                @elseif ($stateOf($document) === 'declined')
                                    <span class="pill bad">Not Released</span>
                                @else
                                    <span class="pill warn">{{ $document->status }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('documents.show', $document) }}" class="go"
                                   title="View processing journey" aria-label="View {{ $document->request_code }}">
                                    <svg class="ic" aria-hidden="true"><use href="#i-right"/></svg>
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr><td colspan="6" class="empty">No document requests found.</td></tr>
                    @endforelse

                    <tr id="docNone"><td colspan="6" class="empty">No requests match your search.</td></tr>

                </tbody>
            </table>
        </div>

    </section>

</div>


{{-- ===================== RIGHT RAIL ===================== --}}
<aside class="pf-rail">

    <div class="rail-sec">
        <h3>Notifications</h3>
        @foreach ($notes as $note)
            <a href="{{ route('documents.index') }}" class="n-item">
                <span class="n-ic {{ $note[1] }}"><svg class="ic" aria-hidden="true"><use href="#i-{{ $note[0] }}"/></svg></span>
                <span>
                    <span class="rt d-block" style="display:block">{{ $note[2] }}</span>
                    <span class="rs">{{ $note[3] }}</span>
                </span>
            </a>
        @endforeach
    </div>

    <div class="rail-sec">
        <h3>Activities</h3>
        @forelse ($latestDocuments->take(5) as $document)
            @php $who = $document->resident?->full_name ?? 'Resident unavailable'; @endphp
            <a href="{{ route('documents.show', $document) }}" class="a-item">
                <span class="av" style="background:{{ $tint($who) }}">{{ $initials($who) }}</span>
                <span>
                    <span class="rt" style="display:block">{{ $document->document_type }} requested</span>
                    <span class="rs">{{ $document->requested_at?->diffForHumans() }}</span>
                </span>
            </a>
        @empty
            <p class="rs">No document activity yet.</p>
        @endforelse
    </div>

    <div class="rail-sec">
        <h3>Recent Residents</h3>
        @forelse ($latestDocuments->filter(fn ($d) => $d->resident)->unique(fn ($d) => $d->resident->full_name)->take(6) as $document)
            @php $who = $document->resident->full_name; @endphp
            <a href="{{ route('documents.show', $document) }}" class="a-item">
                <span class="av" style="background:{{ $tint($who) }}">{{ $initials($who) }}</span>
                <span class="rt" style="align-self:center">{{ $who }}</span>
            </a>
        @empty
            <p class="rs">No residents yet.</p>
        @endforelse
    </div>

</aside>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

</div>

@endsection


@push('scripts')
<script>
(function () {
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- count-up numbers ---------- */
    function countUp(el) {
        const target = parseFloat(el.dataset.countup);
        if (isNaN(target) || reduce) return;
        const t0 = performance.now(), dur = 900;
        (function tick(now) {
            const t = Math.min(1, (now - t0) / dur);
            el.textContent = Math.round(target * (1 - Math.pow(1 - t, 3))).toLocaleString();
            if (t < 1) requestAnimationFrame(tick);
        })(t0);
    }
    document.querySelectorAll('[data-countup]').forEach(countUp);

    /* ---------- bars grow in ---------- */
    requestAnimationFrame(() => requestAnimationFrame(() => {
        document.querySelectorAll('[data-width]').forEach(b => { b.style.width = b.dataset.width + '%'; });
        document.querySelectorAll('[data-height]').forEach(b => { b.style.height = Math.max(4, b.dataset.height * 0.8) + '%'; });
    }));

    /* ---------- online / offline awareness ---------- */
    const chip = document.getElementById('connChip');
    const toast = document.getElementById('toast');
    let toastTimer;

    function showToast(msg, sticky) {
        toast.textContent = msg;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        if (!sticky) toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
    }
    function setConn(online, announce) {
        chip.dataset.state = online ? 'online' : 'offline';
        chip.querySelector('em').textContent = online ? 'Online' : 'Offline';
        if (!announce) return;
        online ? showToast('Back online.', false) : showToast('You are offline. Showing the last loaded data.', true);
    }
    setConn(navigator.onLine, !navigator.onLine);
    window.addEventListener('online',  () => setConn(true, true));
    window.addEventListener('offline', () => setConn(false, true));

    /* while offline, keep the user on this screen instead of hitting the browser's error page */
    function offlineGuard(e) {
        if (navigator.onLine) return;
        const link = e.target.closest('a[href]');
        const row = e.target.closest('tr[data-href]');
        if (link || row) {
            e.preventDefault();
            e.stopPropagation();
            showToast('You are offline. This page opens again once you are back online.', false);
        }
    }
    document.addEventListener('click', offlineGuard, true);

    /* ---------- search + clickable rows ---------- */
    const rows = Array.from(document.querySelectorAll('#docBody tr[data-href]'));
    const none = document.getElementById('docNone');
    const search = document.getElementById('docSearch');

    rows.forEach(r => r.addEventListener('click', e => {
        if (e.target.closest('a')) return;
        window.location.href = r.dataset.href;
    }));

    if (search) {
        search.addEventListener('input', () => {
            const q = search.value.trim().toLowerCase();
            let shown = 0;
            rows.forEach(r => {
                const ok = q === '' || r.textContent.toLowerCase().includes(q);
                r.style.display = ok ? '' : 'none';
                if (ok) shown++;
            });
            none.style.display = shown === 0 ? 'table-row' : 'none';
        });

        document.addEventListener('keydown', e => {
            const tag = (e.target.tagName || '').toLowerCase();
            if (e.key === '/' && !['input', 'textarea', 'select'].includes(tag)) { e.preventDefault(); search.focus(); }
            if (e.key === 'Escape' && document.activeElement === search) { search.value = ''; search.dispatchEvent(new Event('input')); search.blur(); }
        });
    }
})();
</script>
@endpush
