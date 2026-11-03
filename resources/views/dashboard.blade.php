@extends('layouts.app')

@section('title', 'Dashboard | e-Poblacion')

@push('styles')
<style>
/* =========================================================
   e-POBLACION: Dashboard (minimal · watermelon)
   Light / dark / system. Pure SVG + CSS, fully offline.
   Palette: watermelon flesh (coral), rind (green), seed (ink).
   ========================================================= */

.db {
    --font: system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;
    --bg:#fffdfc; --surface:#faf4f2; --line:#eee2de; --line-strong:#dccdc8;
    --ink:#2b1f22; --text:#4f3d41; --soft:#80696e;
    --coral:#d93a50; --coral-soft:#fceaed;
    --green:#2e7d40; --green-soft:#e6f2e4;
    --amber:#a9650a; --amber-soft:#fbf0d9;
    --mint:#2f9a73;
    --accent:var(--coral);
    --focus:rgba(217,58,80,.28);
    --shadow:0 1px 2px rgba(43,31,34,.04);
    font-family:var(--font); color:var(--text);
    display:grid; grid-template-columns:minmax(0,1fr) 300px; gap:0 20px; align-items:start;
}
[data-theme="dark"] .db {
    --bg:#1d1719; --surface:#171214; --line:#33282b; --line-strong:#4a3b3f;
    --ink:#f6ecee; --text:#d8c8cb; --soft:#a69195;
    --coral:#ff7f8f; --coral-soft:#3a1d23;
    --green:#80d68c; --green-soft:#1c3323;
    --amber:#f0c05c; --amber-soft:#372d15;
    --mint:#5fd0a2;
    --focus:rgba(255,127,143,.35);
    --shadow:none;
}
@media (prefers-color-scheme: dark) {
    [data-theme="system"] .db {
        --bg:#1d1719; --surface:#171214; --line:#33282b; --line-strong:#4a3b3f;
        --ink:#f6ecee; --text:#d8c8cb; --soft:#a69195;
        --coral:#ff7f8f; --coral-soft:#3a1d23;
        --green:#80d68c; --green-soft:#1c3323;
        --amber:#f0c05c; --amber-soft:#372d15;
        --mint:#5fd0a2;
        --focus:rgba(255,127,143,.35);
        --shadow:none;
    }
}
.db *, .db *::before, .db *::after { box-sizing:border-box; }
.db a { color:inherit; text-decoration:none; }
.db h1,.db h2,.db h3,.db p { margin:0; }
.db :focus-visible { outline:3px solid var(--focus); outline-offset:2px; border-radius:8px; }
.db-main { min-width:0; display:flex; flex-direction:column; gap:16px; }
.sr-only { position:absolute; left:-9999px; }

.ic { width:1em; height:1em; fill:none; stroke:currentColor; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; vertical-align:-.125em; flex-shrink:0; }

/* ---------- Card base ---------- */
.card { background:var(--bg); border:1px solid var(--line); border-radius:16px; padding:20px; box-shadow:var(--shadow); min-width:0; }
.card-h { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:16px; }
.card-h h3 { font-size:1rem; font-weight:700; letter-spacing:-.01em; color:var(--ink); }
.sub { font-size:.8rem; color:var(--soft); margin-top:3px; }
.link { font-size:.83rem; font-weight:600; color:var(--coral); white-space:nowrap; }
.link:hover { text-decoration:underline !important; }
.empty { padding:28px 8px; text-align:center; font-size:.88rem; color:var(--soft); }

/* ---------- Hero: quiet card with a thin watermelon rind line ---------- */
.hero {
    position:relative; overflow:hidden; border-radius:16px; padding:22px 24px 24px;
    background:var(--bg); border:1px solid var(--line); box-shadow:var(--shadow);
    display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;
}
.hero::before {
    content:''; position:absolute; left:0; right:0; top:0; height:5px;
    background:linear-gradient(90deg,var(--green) 0 22%,#f3ecd0 22% 26%,var(--coral) 26% 100%);
    opacity:.9;
}
[data-theme="dark"] .hero::before { background:linear-gradient(90deg,var(--green) 0 22%,#5a5136 22% 26%,var(--coral) 26% 100%); opacity:.85; }
.brand { display:flex; align-items:center; gap:14px; min-width:0; }
.brand-mark { width:54px; height:50px; flex-shrink:0; overflow:visible; }
.brand-mark .eye { transform-box:fill-box; transform-origin:center; animation:blink 5s infinite; }
@keyframes blink { 0%,92%,100% { transform:scaleY(1); } 95% { transform:scaleY(.12); } }
.hero-kicker { font-size:.72rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--soft); }
.hero h1 { margin-top:2px; font-size:clamp(1.35rem,2.4vw,1.7rem); font-weight:800; letter-spacing:-.02em; line-height:1.2; color:var(--ink); }
.hero-msg { margin-top:4px; font-size:.92rem; color:var(--text); }
.hero-msg b { font-weight:800; color:var(--coral); }
.chips { display:flex; gap:8px; flex-wrap:wrap; }
.chip { display:inline-flex; align-items:center; gap:8px; height:32px; padding:0 13px; border-radius:999px; background:var(--surface); border:1px solid var(--line); font-size:.78rem; font-weight:600; color:var(--ink); }
.chip i { width:8px; height:8px; border-radius:50%; background:var(--green); box-shadow:0 0 0 3px var(--green-soft); }
.chip[data-state="offline"] i { background:var(--coral); box-shadow:0 0 0 3px var(--coral-soft); }

/* ---------- KPI tiles ---------- */
.kpis { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
.kpi {
    display:block; padding:18px; border-radius:16px;
    background:var(--bg); border:1px solid var(--line); box-shadow:var(--shadow);
    transition:border-color .15s, background-color .15s;
}
.kpi:hover { border-color:var(--tone); }
.kpi.c { --tone:var(--coral); --tone-soft:var(--coral-soft); }
.kpi.g { --tone:var(--green); --tone-soft:var(--green-soft); }
.kpi-top { display:flex; align-items:center; justify-content:space-between; gap:10px; }
.kpi-ic { width:36px; height:36px; border-radius:10px; display:grid; place-items:center; background:var(--tone-soft); color:var(--tone); font-size:1.1rem; }
.kpi-go { color:var(--soft); font-size:.95rem; transition:color .15s; }
.kpi:hover .kpi-go { color:var(--tone); }
.kpi-n { margin-top:14px; font-size:2rem; font-weight:800; letter-spacing:-.03em; line-height:1; color:var(--ink); font-variant-numeric:tabular-nums; }
.kpi-l { margin-top:8px; font-size:.88rem; font-weight:600; color:var(--ink); }
.kpi-d { margin-top:2px; font-size:.76rem; color:var(--soft); }

/* ---------- Grids ---------- */
.grid { display:grid; gap:16px; }
.g-21 { grid-template-columns:minmax(0,2fr) minmax(0,1fr); }
.g-12 { grid-template-columns:minmax(0,1fr) minmax(0,1.4fr); }
.g-11 { grid-template-columns:minmax(0,1fr) minmax(0,1fr); }

/* ---------- Age chart ---------- */
.legend { display:flex; align-items:center; gap:10px; font-size:.76rem; color:var(--soft); font-weight:600; }
.legend .k { display:inline-flex; align-items:center; gap:6px; }
.legend .k i { width:9px; height:9px; border-radius:50%; background:var(--coral); }
.lg-btn { display:inline-flex; align-items:center; gap:6px; padding:4px 11px; border:1px solid var(--line-strong); border-radius:99px; background:transparent; color:var(--soft); font:600 .76rem var(--font); cursor:pointer; }
.lg-btn .d { width:9px; height:9px; border-radius:50%; background:var(--green); }
.lg-btn[aria-pressed="false"] { opacity:.55; }
.lg-btn:hover { border-color:var(--soft); }
.chart { width:100%; height:auto; display:block; overflow:visible; }
.chart .gl { stroke:var(--line); stroke-width:1; }
.chart text { fill:var(--soft); font:600 11px var(--font); }
.chart .adult-bg { fill:var(--green); opacity:.08; }
.chart .divider { stroke:var(--green); stroke-width:1.5; stroke-dasharray:5 5; opacity:.8; }
.chart .zone { fill:var(--green); font-weight:700; }
.chart .ln { fill:none; stroke:var(--coral); stroke-width:2.5; stroke-linecap:round; stroke-dasharray:1400; stroke-dashoffset:1400; animation:draw 1.2s ease forwards; }
.chart .ln2 { fill:none; stroke:var(--green); stroke-width:2; stroke-dasharray:6 6; stroke-linecap:round; animation:fade .8s .4s ease both; }
.chart .dot { fill:var(--bg); stroke:var(--coral); stroke-width:2.5; cursor:pointer; transition:r .15s; }
.chart .dot2 { fill:var(--bg); stroke:var(--green); stroke-width:2; cursor:pointer; }
.chart .dot:hover,.chart .dot2:hover { r:7; }
.chart.hide-next .ln2,.chart.hide-next .dot2 { display:none; }
@keyframes draw { to { stroke-dashoffset:0; } }
@keyframes fade { from { opacity:0; } to { opacity:1; } }

/* ---------- Legal age ---------- */
.big { display:flex; align-items:baseline; gap:12px; flex-wrap:wrap; margin-bottom:16px; padding:14px 16px; border-radius:12px; background:var(--amber-soft); }
.big b { font-size:2.4rem; font-weight:800; letter-spacing:-.03em; line-height:1; color:var(--amber); }
.big span { font-size:.82rem; color:var(--ink); font-weight:600; flex:1; min-width:120px; }

/* ---------- Bars ---------- */
.hb { display:grid; grid-template-columns:92px 1fr auto; align-items:center; gap:12px; margin-bottom:13px; font-size:.83rem; font-weight:500; color:var(--text); }
.hb:last-child { margin-bottom:0; }
.hb-t { height:8px; border-radius:99px; background:var(--surface); border:1px solid var(--line); overflow:hidden; }
.hb-t i { display:block; width:0; height:100%; border-radius:inherit; transition:width .9s cubic-bezier(.2,.8,.2,1); }
.hb b { font-weight:700; text-align:right; min-width:26px; color:var(--ink); font-variant-numeric:tabular-nums; }

/* ---------- Pipeline ---------- */
.pipe { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
.stage {
    position:relative; display:block; padding:16px; border-radius:12px;
    background:var(--surface); border:1px solid var(--line);
    transition:border-color .15s;
}
.stage:hover { border-color:var(--tone); }
.stage-dot { display:inline-block; width:10px; height:10px; border-radius:50%; background:var(--tone); }
.stage-n { margin-top:10px; font-size:1.8rem; font-weight:800; letter-spacing:-.02em; line-height:1; color:var(--ink); font-variant-numeric:tabular-nums; }
.stage-l { margin-top:7px; font-size:.84rem; font-weight:600; color:var(--ink); }
.stage-p { margin-top:2px; font-size:.76rem; color:var(--soft); }
.stack { display:flex; gap:3px; height:8px; border-radius:99px; overflow:hidden; background:var(--surface); border:1px solid var(--line); margin-top:16px; }
.stack i { display:block; height:100%; transition:flex-grow .8s; }
.stack-note { margin-top:10px; font-size:.82rem; color:var(--soft); }
.stack-note b { color:var(--ink); }

/* ---------- Completion ring ---------- */
.ring-wrap { display:flex; align-items:center; gap:20px; flex-wrap:wrap; }
.ring { position:relative; width:136px; height:136px; flex-shrink:0; }
.ring svg { width:100%; height:100%; transform:rotate(-90deg); }
.ring circle { fill:none; stroke-width:10; stroke-linecap:round; }
.ring .tr { stroke:var(--line); }
.ring .pg { stroke:var(--coral); transition:stroke-dasharray 1.1s cubic-bezier(.2,.8,.2,1); }
.ring-c { position:absolute; inset:0; display:grid; place-content:center; text-align:center; }
.ring-c b { font-size:1.9rem; font-weight:800; letter-spacing:-.03em; line-height:1; color:var(--ink); }
.ring-c small { font-size:.7rem; color:var(--soft); margin-top:3px; font-weight:600; }
.mini { flex:1; min-width:180px; display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.mini > * { display:block; padding:12px 14px; border-radius:12px; background:var(--surface); border:1px solid var(--line); transition:border-color .15s; }
.mini a:hover { border-color:var(--coral); }
.mini b { display:block; font-size:1.2rem; font-weight:800; letter-spacing:-.01em; color:var(--ink); }
.mini span { display:block; margin-top:2px; font-size:.76rem; color:var(--soft); font-weight:500; }

/* ---------- Table ---------- */
.scroll-x { overflow-x:auto; }
.tbl { width:100%; border-collapse:separate; border-spacing:0; font-size:.87rem; }
.tbl th { padding:10px 14px; text-align:left; font-size:.7rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--soft); background:var(--surface); white-space:nowrap; }
.tbl th:first-child { border-radius:10px 0 0 10px; } .tbl th:last-child { border-radius:0 10px 10px 0; }
.tbl td { padding:13px 14px; border-bottom:1px solid var(--line); vertical-align:middle; color:var(--text); }
.tbl tbody tr:last-child td { border-bottom:0; }
.tbl tr[data-href] { cursor:pointer; transition:background-color .12s; }
.tbl tr[data-href]:hover td { background:var(--surface); }
.tbl .code { font-weight:700; white-space:nowrap; color:var(--ink); }
.tbl .muted { color:var(--soft); }
.who { display:flex; align-items:center; gap:10px; min-width:0; }
.av { flex-shrink:0; display:grid; place-items:center; width:30px; height:30px; border-radius:50%; color:#fff; font-size:.7rem; font-weight:700; }
.pill { display:inline-block; padding:3px 12px; border-radius:99px; font-size:.75rem; font-weight:700; white-space:nowrap; }
.pill.ok { background:var(--green-soft); color:var(--green); }
.pill.bad { background:var(--coral-soft); color:var(--coral); }
.pill.warn { background:var(--amber-soft); color:var(--amber); }
.viewbtn { display:inline-flex; align-items:center; justify-content:center; padding:5px 15px; border-radius:9px; background:transparent; border:1px solid var(--line-strong); color:var(--ink) !important; font-size:.79rem; font-weight:600; transition:background .15s,color .15s,border-color .15s; }
.viewbtn:hover { background:var(--coral); border-color:var(--coral); color:var(--bg) !important; }

/* ---------- Right rail ---------- */
.rail { display:flex; flex-direction:column; gap:16px; position:sticky; top:16px; }
.rail .card { padding:18px; }
.rail h3 { font-size:.95rem; font-weight:700; margin-bottom:12px; color:var(--ink); }
.qa { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.qa a { display:flex; flex-direction:column; gap:8px; padding:12px; border-radius:12px; background:var(--surface); border:1px solid var(--line); font-size:.8rem; font-weight:600; line-height:1.25; color:var(--ink); transition:border-color .15s,background .15s; }
.qa a:hover { border-color:var(--tone); background:var(--bg); }
.qa .q-ic { width:32px; height:32px; border-radius:9px; display:grid; place-items:center; font-size:1rem; background:var(--tone-soft); color:var(--tone); }
.qa .c { --tone:var(--coral); --tone-soft:var(--coral-soft); }
.qa .g { --tone:var(--green); --tone-soft:var(--green-soft); }
.qa .a { --tone:var(--amber); --tone-soft:var(--amber-soft); }
.item { display:flex; gap:11px; align-items:center; padding:8px; margin:0 -8px; border-radius:10px; transition:background .15s; }
.item:hover { background:var(--surface); }
.rt { font-size:.85rem; line-height:1.3; font-weight:600; display:block; color:var(--ink); }
.rs { font-size:.75rem; color:var(--soft); display:block; }

/* ---------- Toast ---------- */
.toast { position:fixed; left:50%; bottom:22px; z-index:1080; transform:translate(-50%,20px); padding:10px 18px; border-radius:12px; background:var(--ink); color:var(--bg); font-size:.86rem; font-weight:600; box-shadow:0 10px 30px rgba(0,0,0,.2); opacity:0; pointer-events:none; transition:opacity .25s,transform .25s; }
.toast.show { opacity:1; transform:translate(-50%,0); }

/* ---------- Responsive ---------- */
@media (max-width:1399.98px) {
    .db { grid-template-columns:minmax(0,1fr); gap:16px; }
    .rail { position:static; display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); align-items:start; }
}
@media (max-width:1100px) {
    .kpis { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .g-21,.g-12,.g-11 { grid-template-columns:1fr; }
    .pipe { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .rail { grid-template-columns:repeat(2,minmax(0,1fr)); }
}
@media (max-width:767.98px) {
    .hero { padding:20px 18px 22px; }
    .kpis { grid-template-columns:1fr; }
    .rail { grid-template-columns:1fr; }
    .hb { grid-template-columns:78px 1fr auto; }
}
@media (prefers-reduced-motion:reduce) { .db *,.chart .ln,.chart .ln2 { transition:none !important; animation:none !important; stroke-dashoffset:0 !important; } }
@media print { .rail { display:none !important; } }
</style>
@endpush


@section('content')

@php
    use Illuminate\Support\Str;
    use App\Models\DocumentRequest as DR;

    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $firstName = Str::of(auth()->user()->name)->before(' ');
    $isCaptain = auth()->user()->isCaptain();

    $rate = max(0, min(100, (float) $monthlyCompletionRate));
    $activePct = $residentCount > 0 ? round(($activeResidentCount / $residentCount) * 100) : 0;
    $RING = 263.89;

    // avatar colors: mid-tone so white initials stay readable in light and dark
    $palette = ['#c93a4e', '#2e7d40', '#0f766e', '#a9650a', '#8a3b8f', '#4f5fd1'];
    $tint = fn ($s) => $palette[crc32((string) $s) % count($palette)];
    $initials = fn ($n) => Str::of((string) $n)->explode(' ')->filter()->map(fn ($p) => Str::substr($p, 0, 1))->take(2)->implode('');

    $stateOf = fn ($d) => $d->workflow_stage === DR::STAGE_RELEASED ? 'released'
        : ($d->workflow_stage === DR::STAGE_THIRD_PARTY_DECLINED ? 'declined' : 'progress');

    /* ---- workflow pipeline (theme-aware colors) ---- */
    $workflow = [
        ['name' => 'For Inspection',     'count' => $workflowCounts['forInspection'],    'color' => 'var(--amber)'],
        ['name' => 'For Approval',       'count' => $workflowCounts['forApproval'],      'color' => 'var(--coral)'],
        ['name' => 'Ready for Printing', 'count' => $workflowCounts['readyForPrinting'], 'color' => 'var(--mint)'],
        ['name' => 'Ready for Release',  'count' => $workflowCounts['readyForRelease'],  'color' => 'var(--green)'],
    ];
    $wfTotal = array_sum(array_column($workflow, 'count'));

    /* ---- age profile: computed from birthdates TODAY, updates itself ---- */
    $ageBands = [['0–5', 0, 5], ['6–12', 6, 12], ['13–17', 13, 17], ['18–24', 18, 24], ['25–34', 25, 34], ['35–44', 35, 44], ['45–59', 45, 59], ['60+', 60, 200]];
    $ages = collect();
    try {
        $resModel = '\App\Models\Resident';
        if (class_exists($resModel)) {
            $resTable = (new $resModel)->getTable();
            $dobCol = collect(['birthdate', 'birth_date', 'date_of_birth', 'dob', 'birthday'])
                ->first(fn ($c) => \Illuminate\Support\Facades\Schema::hasColumn($resTable, $c));
            if ($dobCol) {
                $ages = $resModel::query()->whereNotNull($dobCol)->pluck($dobCol)
                    ->map(fn ($d) => \Carbon\Carbon::parse($d)->age)->filter(fn ($a) => $a >= 0);
            }
        }
    } catch (\Throwable $e) { $ages = collect(); }

    $inBand = fn ($list, $lo, $hi, $shift = 0) => $list->filter(fn ($a) => ($a + $shift) >= $lo && ($a + $shift) <= $hi)->count();
    $ageNow  = array_map(fn ($b) => $inBand($ages, $b[1], $b[2]), $ageBands);
    $ageNext = array_map(fn ($b) => $inBand($ages, $b[1], $b[2], 1), $ageBands);
    $ageTotal = $ages->count();
    $minors = $ages->filter(fn ($a) => $a < 18)->count();
    $adults = $ageTotal - $minors;
    $turning18 = $ages->filter(fn ($a) => $a === 17)->count();
    $seniors = $ages->filter(fn ($a) => $a >= 60)->count();
    $ageMax = max(array_merge([1], $ageNow, $ageNext));
    $px = fn ($i) => round(50 + $i * (550 / (count($ageBands) - 1)), 1);
    $py = fn ($v) => round(20 + (1 - $v / $ageMax) * 160, 1);
    $smooth = function ($vals) use ($px, $py) {
        $d = ''; $pxv = 0; $pyv = 0;
        foreach ($vals as $i => $v) {
            $x = $px($i); $y = $py($v);
            if ($i === 0) { $d = "M{$x},{$y}"; }
            else { $mx = round(($pxv + $x) / 2, 1); $d .= " C{$mx},{$pyv} {$mx},{$y} {$x},{$y}"; }
            $pxv = $x; $pyv = $y;
        }
        return $d;
    };
    $divX = round(($px(2) + $px(3)) / 2, 1);

    /* ---- status + type breakdowns ---- */
    $states = $latestDocuments->map($stateOf)->countBy();
    $statusRows = [
        ['In progress',  $states->get('progress', 0), 'var(--amber)'],
        ['Released',     $states->get('released', 0), 'var(--green)'],
        ['Not released', $states->get('declined', 0), 'var(--coral)'],
    ];
    $statusMax = max(array_merge([1], array_column($statusRows, 1)));
    $byType = $latestDocuments->groupBy('document_type')->map->count()->sortDesc()->take(6);
    $typeMax = max(array_merge([1], $byType->values()->all()));
@endphp

<div class="db">

{{-- inline icon sprite: works with no icon font / no internet --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false"><defs>
    <symbol id="i-up" viewBox="0 0 24 24"><path d="M7 17 17 7M8 7h9v9"/></symbol>
    <symbol id="i-hourglass" viewBox="0 0 24 24"><path d="M6 3h12M6 21h12M7 3c0 5 5 6 5 9s-5 4-5 9M17 3c0 5-5 6-5 9s5 4 5 9"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></symbol>
    <symbol id="i-users" viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0M17 4a4 4 0 0 1 0 8M22 21a7 7 0 0 0-5-6.7"/></symbol>
    <symbol id="i-userok" viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0M16 11l2 2 4-4"/></symbol>
    <symbol id="i-file" viewBox="0 0 24 24"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8zM14 3v5h5M9 13h6M9 17h6"/></symbol>
    <symbol id="i-home" viewBox="0 0 24 24"><path d="m3 11 9-8 9 8M5 10v10h14V10M10 20v-6h4v6"/></symbol>
    <symbol id="i-alert" viewBox="0 0 24 24"><path d="M12 3 2 20h20zM12 10v5M12 18h.01"/></symbol>
    <symbol id="i-bell" viewBox="0 0 24 24"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 8 3 8H3s3-1 3-8M10 21h4"/></symbol>
    <symbol id="i-gift" viewBox="0 0 24 24"><path d="M20 12v9H4v-9M2 7h20v5H2zM12 21V7M12 7H8a2.5 2.5 0 1 1 0-5c3 0 4 5 4 5zM12 7h4a2.5 2.5 0 1 0 0-5c-3 0-4 5-4 5z"/></symbol>
    <symbol id="i-phone" viewBox="0 0 24 24"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></symbol>
    <symbol id="i-map" viewBox="0 0 24 24"><path d="M9 4 3 6v14l6-2 6 2 6-2V4l-6 2zM9 4v14M15 6v14"/></symbol>
</defs></svg>


{{-- =================================================================
     MAIN COLUMN
     ================================================================= --}}
<div class="db-main">

    {{-- ===================== HERO ===================== --}}
    <header class="hero">
        <div class="brand">
            <svg class="brand-mark" viewBox="0 0 64 60" aria-hidden="true">
                <ellipse cx="32" cy="55" rx="20" ry="3" fill="rgba(0,0,0,.14)"/>
                <defs>
                    <linearGradient id="cuteFlesh" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#ff8c9b"/><stop offset="1" stop-color="#ff5d73"/>
                    </linearGradient>
                </defs>
                <path d="M5 24a27 27 0 0 0 54 0z" fill="#4fae4e"/>
                <path d="M8.5 24a23.5 23.5 0 0 0 47 0z" fill="#e4f2d6"/>
                <path d="M11.5 24a20.5 20.5 0 0 0 41 0z" fill="url(#cuteFlesh)"/>
                <path d="M6.5 24h51" stroke="#3b8a40" stroke-width="2.4" stroke-linecap="round"/>
                <ellipse cx="16" cy="29.5" rx="1.4" ry="2.3" transform="rotate(-28 16 29.5)" fill="#2a1a1d"/>
                <ellipse cx="48" cy="29.5" rx="1.4" ry="2.3" transform="rotate(28 48 29.5)" fill="#2a1a1d"/>
                <ellipse cx="19" cy="38.5" rx="3.6" ry="2.2" fill="#e5384f" opacity=".42"/>
                <ellipse cx="45" cy="38.5" rx="3.6" ry="2.2" fill="#e5384f" opacity=".42"/>
                <g class="eye"><ellipse cx="25" cy="34" rx="2.5" ry="3.1" fill="#3a1f26"/><circle cx="25.9" cy="32.7" r="1" fill="#fff"/></g>
                <g class="eye"><ellipse cx="39" cy="34" rx="2.5" ry="3.1" fill="#3a1f26"/><circle cx="39.9" cy="32.7" r="1" fill="#fff"/></g>
                <path d="M28.6 38.6q3.4 3.8 6.8 0" stroke="#3a1f26" stroke-width="1.9" stroke-linecap="round" fill="none"/>
                <path d="M57 5l1.5 3.7 3.7 1.5-3.7 1.5L57 15.4l-1.5-3.7-3.7-1.5 3.7-1.5z" fill="#ffd35c"/>
            </svg>
            <div>
                <div class="hero-kicker">e-Poblacion · {{ now()->format('l, F j') }}</div>
                <h1>{{ $greeting }}, {{ $firstName }}.</h1>
                <p class="hero-msg">
                    @if ($documentsAwaitingAction > 0)
                        <b>{{ number_format($documentsAwaitingAction) }}</b> {{ Str::plural('document', $documentsAwaitingAction) }} need your action today.
                    @else
                        You are all caught up. Nothing is waiting for you.
                    @endif
                </p>
            </div>
        </div>

        <div class="chips">
            <span class="chip" id="connChip" data-state="online" role="status" aria-live="polite"><i></i><em style="font-style:normal">Online</em></span>
            <span class="chip">{{ $isCaptain ? 'Barangay Captain' : 'Barangay Secretary' }}</span>
        </div>
    </header>


    {{-- ===================== KPI TILES ===================== --}}
    <section class="kpis" aria-label="Key numbers">
        <a href="{{ route('documents.index') }}" class="kpi c">
            <div class="kpi-top">
                <span class="kpi-ic"><svg class="ic" aria-hidden="true"><use href="#i-hourglass"/></svg></span>
                <svg class="ic kpi-go" aria-hidden="true"><use href="#i-up"/></svg>
            </div>
            <div class="kpi-n" data-countup="{{ $documentsAwaitingAction }}">{{ number_format($documentsAwaitingAction) }}</div>
            <div class="kpi-l">Awaiting Action</div>
            <div class="kpi-d">Needs processing</div>
        </a>

        <a href="{{ route('documents.index', ['status' => 'released']) }}" class="kpi c">
            <div class="kpi-top">
                <span class="kpi-ic"><svg class="ic" aria-hidden="true"><use href="#i-check"/></svg></span>
                <svg class="ic kpi-go" aria-hidden="true"><use href="#i-up"/></svg>
            </div>
            <div class="kpi-n" data-countup="{{ $documentsReleasedThisMonth }}">{{ number_format($documentsReleasedThisMonth) }}</div>
            <div class="kpi-l">Released This Month</div>
            <div class="kpi-d">{{ $rate }}% of this month's requests</div>
        </a>

        <a href="{{ route('residents.index') }}" class="kpi g">
            <div class="kpi-top">
                <span class="kpi-ic"><svg class="ic" aria-hidden="true"><use href="#i-users"/></svg></span>
                <svg class="ic kpi-go" aria-hidden="true"><use href="#i-up"/></svg>
            </div>
            <div class="kpi-n" data-countup="{{ $residentCount }}">{{ number_format($residentCount) }}</div>
            <div class="kpi-l">Registered Residents</div>
            <div class="kpi-d">All records</div>
        </a>

        <a href="{{ route('residents.index', ['status' => 'Active']) }}" class="kpi g">
            <div class="kpi-top">
                <span class="kpi-ic"><svg class="ic" aria-hidden="true"><use href="#i-userok"/></svg></span>
                <svg class="ic kpi-go" aria-hidden="true"><use href="#i-up"/></svg>
            </div>
            <div class="kpi-n" data-countup="{{ $activeResidentCount }}">{{ number_format($activeResidentCount) }}</div>
            <div class="kpi-l">Active Residents</div>
            <div class="kpi-d">{{ $activePct }}% of all residents</div>
        </a>
    </section>


    {{-- ===================== DOCUMENT PIPELINE ===================== --}}
    <section class="card">
        <div class="card-h">
            <div>
                <h3>Document Pipeline</h3>
                <div class="sub">Where every open request is right now.</div>
            </div>
            <a href="{{ route('documents.index') }}" class="link">View all →</a>
        </div>

        <div class="pipe">
            @foreach ($workflow as $w)
                <a href="{{ route('documents.index') }}" class="stage" style="--tone:{{ $w['color'] }}">
                    <span class="stage-dot"></span>
                    <div class="stage-n">{{ $w['count'] }}</div>
                    <div class="stage-l">{{ $w['name'] }}</div>
                    <div class="stage-p">{{ $wfTotal > 0 ? round(($w['count'] / $wfTotal) * 100) : 0 }}% of queue</div>
                </a>
            @endforeach
        </div>

        <div class="stack" role="img" aria-label="Share of each workflow stage in the queue">
            @if ($wfTotal > 0)
                @foreach ($workflow as $w)
                    @if ($w['count'] > 0)
                        <i style="flex-grow:{{ $w['count'] }};background:{{ $w['color'] }}" title="{{ $w['name'] }}: {{ $w['count'] }}"></i>
                    @endif
                @endforeach
            @endif
        </div>
        <p class="stack-note"><b>{{ $wfTotal }}</b> {{ Str::plural('request', $wfTotal) }} in the queue right now.</p>
    </section>


    {{-- ===================== AGE + LEGAL AGE ===================== --}}
    <section class="grid g-21">

        <div class="card">
            <div class="card-h">
                <div>
                    <h3>Residents by Age</h3>
                    <div class="sub">Updates automatically as residents get older.</div>
                </div>
                <div class="legend">
                    <span class="k"><i></i>{{ now()->year }}</span>
                    <button type="button" class="lg-btn" id="nextToggle" aria-pressed="true"><span class="d"></span>{{ now()->year + 1 }}</button>
                </div>
            </div>

            @if ($ageTotal > 0)
                <svg class="chart" id="ageChart" viewBox="0 0 620 220" role="img" aria-label="Residents by age group, this year and next year">
                    <defs>
                        <linearGradient id="ageArea" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0" style="stop-color:var(--coral)" stop-opacity=".2"/>
                            <stop offset="1" style="stop-color:var(--coral)" stop-opacity="0"/>
                        </linearGradient>
                    </defs>

                    <rect class="adult-bg" x="{{ $divX }}" y="14" width="{{ 610 - $divX }}" height="166" rx="6"/>

                    @foreach ([0, .25, .5, .75, 1] as $g)
                        <line class="gl" x1="44" x2="610" y1="{{ round(20 + $g * 160, 1) }}" y2="{{ round(20 + $g * 160, 1) }}"/>
                        <text x="36" y="{{ round(24 + $g * 160, 1) }}" text-anchor="end">{{ round($ageMax * (1 - $g)) }}</text>
                    @endforeach

                    <line class="divider" x1="{{ $divX }}" x2="{{ $divX }}" y1="14" y2="180"/>
                    <text class="zone" x="{{ $divX - 8 }}" y="30" text-anchor="end">Minors</text>
                    <text class="zone" x="{{ $divX + 8 }}" y="30">Legal age · 18+</text>

                    <path d="{{ $smooth($ageNow) }} L{{ $px(count($ageBands) - 1) }},180 L{{ $px(0) }},180 Z" fill="url(#ageArea)"/>
                    <path class="ln2" d="{{ $smooth($ageNext) }}"/>
                    <path class="ln" d="{{ $smooth($ageNow) }}"/>

                    @foreach ($ageBands as $i => $b)
                        <circle class="dot2" cx="{{ $px($i) }}" cy="{{ $py($ageNext[$i]) }}" r="4">
                            <title>{{ $b[0] }} in {{ now()->year + 1 }}: {{ number_format($ageNext[$i]) }}</title>
                        </circle>
                        <circle class="dot" cx="{{ $px($i) }}" cy="{{ $py($ageNow[$i]) }}" r="5">
                            <title>{{ $b[0] }} in {{ now()->year }}: {{ number_format($ageNow[$i]) }}</title>
                        </circle>
                        <text x="{{ $px($i) }}" y="203" text-anchor="middle">{{ $b[0] }}</text>
                    @endforeach
                </svg>
            @else
                <div class="empty">No birthdate data found. Make sure residents have a birthdate saved.</div>
            @endif
        </div>

        <div class="card">
            <div class="card-h"><div><h3>Legal Age Watch</h3><div class="sub">Residents near age 18</div></div></div>

            @if ($ageTotal > 0)
                <div class="big">
                    <b data-countup="{{ $turning18 }}">{{ $turning18 }}</b>
                    <span>{{ Str::plural('resident', $turning18) }} will turn 18 within the next year</span>
                </div>

                @foreach ([['Minors', $minors, 'var(--amber)'], ['Adults', $adults, 'var(--coral)'], ['Seniors', $seniors, 'var(--green)']] as $r)
                    <div class="hb" style="grid-template-columns:64px 1fr auto">
                        <span>{{ $r[0] }}</span>
                        <span class="hb-t"><i style="background:{{ $r[2] }}" data-width="{{ round(($r[1] / $ageTotal) * 100) }}"></i></span>
                        <b>{{ number_format($r[1]) }}</b>
                    </div>
                @endforeach

                <p class="sub" style="margin-top:16px">Based on {{ number_format($ageTotal) }} residents with a birthdate.</p>
            @else
                <div class="empty">Waiting for resident birthdates.</div>
            @endif
        </div>

    </section>


    {{-- ===================== COMPLETION + STATUS + TYPES ===================== --}}
    <section class="grid g-12">

        <div class="card">
            <div class="card-h">
                <div><h3>Monthly Completion</h3><div class="sub">Updated {{ now()->format('g:i A') }}</div></div>
            </div>

            <div class="ring-wrap">
                <div class="ring" role="img" aria-label="{{ $rate }} percent completed">
                    <svg viewBox="0 0 100 100" aria-hidden="true">
                        <circle class="tr" cx="50" cy="50" r="42"/>
                        <circle class="pg" cx="50" cy="50" r="42" stroke-dasharray="0 {{ $RING }}" data-dash="{{ round(($rate / 100) * $RING, 2) }}"/>
                    </svg>
                    <div class="ring-c"><b data-countup="{{ $rate }}">{{ $rate }}</b><small>% released</small></div>
                </div>

                <div class="mini">
                    <a href="{{ route('documents.index', ['status' => 'released']) }}">
                        <b>{{ number_format($documentsReleasedThisMonth) }}</b><span>Released this month</span>
                    </a>
                    <a href="{{ route('documents.index') }}">
                        <b>{{ number_format($documentsAwaitingAction) }}</b><span>Awaiting action</span>
                    </a>
                    <div>
                        <b>{{ $averageProcessingHours !== null ? $averageProcessingHours . ' h' : '—' }}</b><span>Avg. processing</span>
                    </div>
                    <a href="{{ route('residents.index', ['status' => 'Active']) }}">
                        <b>{{ $activePct }}%</b><span>Residents active</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid g-11">
            <div class="card">
                <div class="card-h"><div><h3>By Status</h3><div class="sub">Latest requests</div></div></div>
                @foreach ($statusRows as $s)
                    <div class="hb" style="grid-template-columns:84px 1fr auto">
                        <span>{{ $s[0] }}</span>
                        <span class="hb-t"><i style="background:{{ $s[2] }}" data-width="{{ round(($s[1] / $statusMax) * 100) }}"></i></span>
                        <b>{{ $s[1] }}</b>
                    </div>
                @endforeach
                <p class="sub" style="margin-top:16px">
                    @if ($averageProcessingHours !== null)
                        Avg. processing: <b style="color:var(--ink)">{{ $averageProcessingHours }} hour(s)</b>
                    @else
                        No released documents this month yet.
                    @endif
                </p>
            </div>

            <div class="card">
                <div class="card-h"><div><h3>By Document Type</h3><div class="sub">Most requested</div></div></div>
                @if ($byType->isNotEmpty())
                    @foreach ($byType as $type => $count)
                        <div class="hb" style="grid-template-columns:84px 1fr auto" title="{{ $type }}: {{ $count }}">
                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ Str::limit($type, 11, '…') }}</span>
                            <span class="hb-t"><i style="background:{{ $loop->odd ? 'var(--coral)' : 'var(--green)' }}" data-width="{{ round(($count / $typeMax) * 100) }}"></i></span>
                            <b>{{ $count }}</b>
                        </div>
                    @endforeach
                @else
                    <div class="empty">No document requests yet.</div>
                @endif
            </div>
        </div>

    </section>


    {{-- ===================== LATEST REQUESTS ===================== --}}
    <section class="card">
        <div class="card-h">
            <div>
                <h3>Latest Document Requests</h3>
                <div class="sub">Click a request to see its processing journey.</div>
            </div>
            <a href="{{ route('documents.index') }}" class="link">Open Documents →</a>
        </div>

        <div class="scroll-x">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Request Code</th>
                        <th>Resident</th>
                        <th>Document</th>
                        <th>Current Stage</th>
                        <th>Status</th>
                        <th><span class="sr-only">View</span></th>
                    </tr>
                </thead>

                <tbody id="docBody">
                    @forelse ($latestDocuments as $document)
                        @php $who = $document->resident?->full_name ?? 'Resident unavailable'; @endphp
                        <tr data-href="{{ route('documents.show', $document) }}">
                            <td class="code">{{ $document->request_code }}</td>
                            <td>
                                <div class="who">
                                    <span class="av" style="background:{{ $tint($who) }}">{{ $initials($who) }}</span>
                                    <span>{{ $who }}</span>
                                </div>
                            </td>
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
                                <a href="{{ route('documents.show', $document) }}" class="viewbtn" title="View processing journey" aria-label="View {{ $document->request_code }}">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">No document requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>


{{-- =================================================================
     RIGHT RAIL
     ================================================================= --}}
<aside class="rail" aria-label="Shortcuts and recent activity">

    <div class="card">
        <h3>Quick Actions</h3>
        <div class="qa">
            <a href="{{ route('documents.create') }}" class="c">
                <span class="q-ic"><svg class="ic" aria-hidden="true"><use href="#i-file"/></svg></span>New Document Request
            </a>
            <a href="{{ route('residents.create') }}" class="g">
                <span class="q-ic"><svg class="ic" aria-hidden="true"><use href="#i-userok"/></svg></span>Add Resident
            </a>
            <a href="{{ route('households.create') }}" class="g">
                <span class="q-ic"><svg class="ic" aria-hidden="true"><use href="#i-home"/></svg></span>Add Household
            </a>
            <a href="{{ route('reports-complaints.create') }}" class="a">
                <span class="q-ic"><svg class="ic" aria-hidden="true"><use href="#i-alert"/></svg></span>File a Complaint
            </a>
            <a href="{{ route('reminders.create') }}" class="c">
                <span class="q-ic"><svg class="ic" aria-hidden="true"><use href="#i-bell"/></svg></span>New Reminder
            </a>
            <a href="{{ route('relief-vouchers.index') }}" class="a">
                <span class="q-ic"><svg class="ic" aria-hidden="true"><use href="#i-gift"/></svg></span>Relief Assistance
            </a>
            <a href="{{ route('households.map') }}" class="g">
                <span class="q-ic"><svg class="ic" aria-hidden="true"><use href="#i-map"/></svg></span>Household Map
            </a>
            <a href="{{ route('emergency-hotlines.index') }}" class="c">
                <span class="q-ic"><svg class="ic" aria-hidden="true"><use href="#i-phone"/></svg></span>Emergency Hotlines
            </a>
        </div>
    </div>

    <div class="card">
        <h3>Recent Activity</h3>
        @forelse ($latestDocuments->take(5) as $document)
            @php $who = $document->resident?->full_name ?? 'Resident unavailable'; @endphp
            <a href="{{ route('documents.show', $document) }}" class="item">
                <span class="av" style="background:{{ $tint($who) }}">{{ $initials($who) }}</span>
                <span>
                    <span class="rt">{{ $document->document_type }} requested</span>
                    <span class="rs">{{ $who }} · {{ $document->requested_at?->diffForHumans() }}</span>
                </span>
            </a>
        @empty
            <p class="sub">No document activity yet.</p>
        @endforelse
    </div>

    <div class="card">
        <h3>Recent Residents</h3>
        @forelse ($latestDocuments->filter(fn ($d) => $d->resident)->unique(fn ($d) => $d->resident->full_name)->take(6) as $document)
            @php $who = $document->resident->full_name; @endphp
            <a href="{{ route('documents.show', $document) }}" class="item">
                <span class="av" style="background:{{ $tint($who) }}">{{ $initials($who) }}</span>
                <span class="rt">{{ $who }}</span>
            </a>
        @empty
            <p class="sub">No residents yet.</p>
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

    /* ---------- bars + ring grow in ---------- */
    requestAnimationFrame(() => requestAnimationFrame(() => {
        document.querySelectorAll('[data-width]').forEach(b => { b.style.width = b.dataset.width + '%'; });
        document.querySelectorAll('.ring .pg').forEach(c => {
            c.setAttribute('stroke-dasharray', c.dataset.dash + ' 263.89');
        });
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

    /* while offline, keep the user on this screen instead of the browser's error page */
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

    /* ---------- age chart: show / hide next-year line ---------- */
    const nextBtn = document.getElementById('nextToggle');
    const ageChart = document.getElementById('ageChart');
    if (nextBtn && ageChart) {
        nextBtn.addEventListener('click', () => {
            const on = nextBtn.getAttribute('aria-pressed') !== 'true';
            nextBtn.setAttribute('aria-pressed', String(on));
            ageChart.classList.toggle('hide-next', !on);
        });
    }

    /* ---------- clickable table rows ---------- */
    document.querySelectorAll('#docBody tr[data-href]').forEach(r => r.addEventListener('click', e => {
        if (e.target.closest('a')) return;
        window.location.href = r.dataset.href;
    }));
})();
</script>
@endpush