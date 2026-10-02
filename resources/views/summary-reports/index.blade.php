@extends('layouts.app')

@section('title', 'Summary Reports | e-Poblacion')

@section('content')
@php
    $fromDate = \Carbon\Carbon::parse($from);
    $toDate = \Carbon\Carbon::parse($to);
    $selectedReports = $selectedReports ?? [];
    $reports = $reports ?? [];
    $reportTypes = $reportTypes ?? [];
    $printMode = $printMode ?? false;
    $selectedCount = count($selectedReports);
    $days = (int) round($fromDate->copy()->startOfDay()->diffInDays($toDate->copy()->startOfDay())) + 1;
    $period = $fromDate->format('F d, Y') . ' – ' . $toDate->format('F d, Y');
    $preparedBy = auth()->user()->name ?? 'Authorized Barangay Personnel';
    $n = fn ($v) => number_format((int) $v);
    $seal = fn ($f) => file_exists(public_path("images/report/$f.png")) ? asset("images/report/$f.png") : asset('icon.png');

    /* One definition per report: add or change a summary here only */
    $defs = [
        'population' => ['title' => 'Population Summary', 'icon' => 'bi-people-fill',
            'stats' => [['Records in Period', 'total'], ['Active', 'active'], ['Archived', 'archived'], ['Current Total Residents', 'current_total']],
            'tables' => [
                ['title' => 'Sex Distribution', 'head' => 'Sex', 'rows' => [['Male', 'male'], ['Female', 'female'], ['Other / Unspecified', 'other']]],
                ['title' => 'Age Group Distribution', 'head' => 'Age Group', 'map' => 'age_groups'],
            ]],
        'households' => ['title' => 'Household Summary', 'icon' => 'bi-house-fill',
            'stats' => [['Records in Period', 'total'], ['Active', 'active'], ['Mapped', 'mapped'], ['Current Total Households', 'current_total']],
            'tables' => [['title' => 'Households by Purok', 'head' => 'Purok', 'count' => 'Households', 'map' => 'by_purok']]],
        'documents' => ['title' => 'Barangay Documents Summary', 'icon' => 'bi-file-earmark-text-fill',
            'stats' => [['Total Requests', 'total'], ['Processed', 'processed'], ['Printed', 'printed'], ['Released', 'released']],
            'tables' => [
                ['title' => 'By Document Type', 'head' => 'Document Type', 'map' => 'by_type'],
                ['title' => 'By Status', 'head' => 'Status', 'map' => 'by_status'],
                ['title' => 'By Workflow Stage', 'head' => 'Workflow Stage', 'map' => 'by_workflow'],
            ]],
        'complaints' => ['title' => 'Reports & Complaints Summary', 'icon' => 'bi-shield-exclamation',
            'stats' => [['Total Records', 'total'], ['Active', 'active'], ['Resolved', 'resolved'], ['Closed', 'closed']],
            'tables' => [
                ['title' => 'By Status', 'head' => 'Status', 'map' => 'by_status'],
                ['title' => 'By Report Type', 'head' => 'Report Type', 'map' => 'by_type'],
                ['title' => 'By Category', 'head' => 'Category', 'map' => 'by_category'],
                ['title' => 'By Purok', 'head' => 'Purok', 'map' => 'by_purok'],
            ]],
        'relief' => ['title' => 'Relief Assistance Summary', 'icon' => 'bi-box2-heart-fill',
            'stats' => [['Total Assistance Records', 'total'], ['Issued', 'issued'], ['Claimed', 'claimed'], ['Released', 'released']],
            'tables' => [
                ['title' => 'Verification Summary', 'head' => 'Verification / Status', 'rows' => [['Requirements Verified', 'requirements_verified'], ['ID Verified', 'id_verified'], ['Proof Verified', 'proof_verified'], ['QR Verified', 'qr_verified'], ['Released', 'released']]],
                ['title' => 'Assistance Status', 'head' => 'Status', 'rows' => [['Pending', 'pending'], ['Issued', 'issued'], ['Claimed', 'claimed'], ['Cancelled', 'cancelled']]],
                ['title' => 'By Assistance Type', 'head' => 'Assistance Type', 'map' => 'by_assistance_type'],
                ['title' => 'By Purok', 'head' => 'Purok', 'map' => 'by_purok'],
            ]],
    ];

    /* Cover-page rows built only from the reports actually selected */
    [$p, $h, $d, $c, $r] = array_map(fn ($k) => $reports[$k] ?? null, ['population', 'households', 'documents', 'complaints', 'relief']);
    $acc = $met = $att = [];
    if ($p) { $acc[] = ['Resident Records', $n($p['total']) . ' resident records in period', 'Current resident total: ' . $n($p['current_total'])]; $met[] = ['Resident Records', $n($p['total']), 'Recorded in the selected period']; }
    if ($h) { $acc[] = ['Household Records', $n($h['total']) . ' household records in period', 'Current household total: ' . $n($h['current_total'])]; $met[] = ['Household Records', $n($h['total']), 'Recorded in the selected period']; }
    if ($d) { $acc[] = ['Barangay Documents', $n($d['total']) . ' document requests', $n($d['released']) . ' marked as released']; $met[] = ['Barangay Document Requests', $n($d['total']), $n($d['processed']) . ' processed; ' . $n($d['released']) . ' released']; $att[] = ['Barangay Documents', $n(max(0, $d['total'] - $d['released'])) . ' requests not yet marked as released', 'Review current request status and complete pending workflow steps where applicable.']; }
    if ($c) { $acc[] = ['Reports & Complaints', $n($c['total']) . ' records reported', $n($c['resolved']) . ' resolved']; $met[] = ['Reports & Complaints', $n($c['total']), $n($c['resolved']) . ' resolved; ' . $n($c['closed']) . ' closed']; $att[] = ['Reports & Complaints', $n($c['active']) . ' active records', 'Review active cases and update resolution or closure records when action is completed.']; }
    if ($r) { $acc[] = ['Relief Assistance', $n($r['total']) . ' assistance records', $n($r['claimed']) . ' claimed']; $met[] = ['Relief Assistance', $n($r['total']), $n($r['issued']) . ' issued; ' . $n($r['claimed']) . ' claimed']; $att[] = ['Relief Assistance', $n(max(0, $r['issued'] - $r['claimed'])) . ' issued records not yet marked as claimed', 'Review distribution records and update claim/release information where applicable.']; }
    if ($h) { $att[] = ['Household Mapping', $n(max(0, $h['total'] - $h['mapped'])) . ' records without mapped coordinates in the period', 'Review household location information where mapping is required.']; }

    $coverTables = [
        ['II. Key Operational Accomplishments', ['Operational Area', 'Actual System Record / Result', 'Operational Indicator'], $acc],
        ['III. Summary Metrics & Deliverables', ['Key Activity / Operational Area', 'Records / Output', 'Result'], $met],
        ['IV. Operational Areas for Attention', ['Operational Area', 'Records Requiring Attention', 'Suggested Administrative Follow-up'], $att],
    ];
    $presentKeys = array_values(array_filter(array_keys($defs), fn ($k) => isset($reports[$k])));
    $pageCount = 1 + count($presentKeys);
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
/* ============================================================
   SUMMARY REPORTS
   Builder (screen) + paper preview + print
   Paper sizes: A4 / Short (Letter) / Long (8.5x13) / Legal
   ============================================================ */

.sr{
    --deep:#0c3b44;--teal:#0f766e;--sun:#e9a80c;--coral:#e0573a;--indigo:#4f5fd1;
    --ink:#14232a;--mut:#5d7078;--rule:#dbe3e5;--soft:#f3f7f7;
    --ar:210/297;--sw:794px;--ph:297mm;
    --paper-display:'Bricolage Grotesque','Figtree',system-ui,sans-serif;
    --paper-body:'Figtree','Segoe UI',system-ui,sans-serif;
}
.sr[data-paper="short"]{--ar:8.5/11;--sw:816px;--ph:279.4mm}
.sr[data-paper="long"]{--ar:8.5/13;--sw:816px;--ph:330.2mm}
.sr[data-paper="legal"]{--ar:8.5/14;--sw:816px;--ph:355.6mm}

.sr-head{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-end;gap:1rem;margin-bottom:1.25rem}
.sr-head h1{font-size:1.75rem;font-weight:800;margin:0}
.sr-head p{margin:.25rem 0 0;color:var(--muted-text)}
.sr-meta{display:flex;gap:.5rem;flex-wrap:wrap}
.sr-pill{display:inline-flex;gap:.4rem;align-items:center;padding:.3rem .8rem;border-radius:99px;background:var(--accent-soft);color:var(--primary);font-size:.82rem;font-weight:700}
.sr-grid{display:grid;grid-template-columns:340px minmax(0,1fr);gap:1.5rem;align-items:start}
@media(max-width:1199.98px){.sr-grid{grid-template-columns:1fr}}

/* ---------- Builder ---------- */
.sr-builder{position:sticky;top:80px;max-height:calc(100vh - 100px);overflow-y:auto;overflow-x:hidden;display:flex;flex-direction:column;gap:1rem;padding:2px;min-width:0}
@media(max-width:1199.98px){.sr-builder{position:static;max-height:none}}
.sr-card{min-width:0;background:var(--surface);border:1px solid var(--border-color);border-radius:18px;padding:1.1rem;box-shadow:var(--shadow)}
.sr-card h2{font-size:.95rem;font-weight:800;margin:0 0 .15rem;display:flex;align-items:center;gap:.5rem}
.sr-card h2 i{color:var(--primary)}
.sr-card .hint{color:var(--muted-text);font-size:.8rem;margin-bottom:.8rem}

.sr-chips{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.45rem;margin-bottom:1rem}
.sr-chip{width:100%;text-align:center;border:1px solid var(--border-soft);background:var(--surface-secondary);color:var(--text-dark);border-radius:10px;padding:.42rem .5rem;font-size:.78rem;font-weight:600;transition:border-color .15s,color .15s,background .15s}
.sr-chip:hover{border-color:var(--primary);color:var(--primary)}
.sr-chip.active{background:var(--primary);border-color:var(--primary);color:var(--on-accent)}
.sr-dates{display:grid;grid-template-columns:minmax(0,1fr);gap:.65rem}
.sr-dates > div{min-width:0}
.sr-dates label{display:block;font-size:.75rem;font-weight:700;color:var(--muted-text);margin-bottom:.25rem}
.sr-dates .form-control{width:100%;min-width:0;max-width:100%;font-size:.88rem;padding:.5rem .7rem}
.sr-range{display:flex;align-items:center;gap:.5rem;margin-top:.8rem;padding:.5rem .7rem;border-radius:10px;background:var(--accent-soft);color:var(--primary);font-size:.78rem;font-weight:700;line-height:1.3}
.sr-range i{flex:0 0 auto}

.sr-tiles{display:grid;gap:.5rem}
.tile{position:relative;display:flex;align-items:center;gap:.75rem;padding:.6rem .75rem;border:1.5px solid var(--border-color);border-radius:14px;background:var(--surface-secondary);cursor:pointer;transition:border-color .15s,background .15s,transform .15s}
.tile:hover{border-color:var(--primary);transform:translateX(2px)}
.tile input{position:absolute;opacity:0;pointer-events:none}
.tile-ico{width:36px;height:36px;border-radius:11px;display:grid;place-items:center;background:var(--surface-tertiary);color:var(--muted-text);transition:.15s;flex:0 0 36px}
.tile-txt{font-weight:600;font-size:.88rem;line-height:1.25;flex:1}
.tile-check{color:var(--primary);opacity:0;transform:scale(.6);transition:.15s}
.tile input:checked ~ .tile-ico{background:var(--primary);color:var(--on-accent)}
.tile input:checked ~ .tile-check{opacity:1;transform:none}
.tile:has(input:checked){border-color:var(--primary);background:var(--accent-soft)}
.tile input:focus-visible ~ .tile-ico{outline:3px solid #ffb627}
.sr-all{display:flex;justify-content:space-between;align-items:center;margin-bottom:.7rem}
.sr-actions{display:grid;gap:.5rem}
.sr-status{display:flex;align-items:center;gap:.5rem;font-size:.8rem;color:var(--muted-text);min-height:1.2rem}

.sr-paper{display:grid;gap:.3rem}
.sr-paper label{font-size:.75rem;font-weight:700;color:var(--muted-text)}
.sr-paper .form-select{font-size:.88rem;padding:.5rem .7rem}

/* ---------- Desk (screen preview) ---------- */
.sr-desk{background:var(--surface-tertiary);background-image:radial-gradient(var(--border-soft) 1px,transparent 1px);background-size:18px 18px;border-radius:22px;padding:1.25rem;min-width:0}
.sr-bar{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:.6rem;max-width:var(--sw);margin:0 auto 1rem}
.sr-bar strong{font-size:.95rem}
.sr-bar .sr-tags{display:flex;gap:.4rem;flex-wrap:wrap;align-items:center}
.sr-loading{opacity:.45;pointer-events:none;transition:opacity .2s}

/* ---------- Paper ---------- */
.sheet{
    --pad-x:46px;--pad-top:44px;
    position:relative;background:#fff;color:var(--ink);
    width:100%;max-width:var(--sw);aspect-ratio:var(--ar);
    margin:0 auto 1.5rem;padding:var(--pad-top) var(--pad-x) 24px;
    border-radius:6px;display:flex;flex-direction:column;
    font:12px/1.5 var(--paper-body);
    box-shadow:0 1px 3px rgba(0,0,0,.14),0 22px 40px -14px rgba(20,20,60,.3);
}
.sheet::before{
    content:'';position:absolute;top:0;left:0;right:0;height:6px;border-radius:6px 6px 0 0;
    background:linear-gradient(90deg,var(--teal) 0,var(--teal) 34%,var(--sun) 34%,var(--sun) 67%,var(--coral) 67%,var(--coral) 100%);
}
.sheet *{box-sizing:border-box}

/* Letterhead */
.lh{display:grid;grid-template-columns:76px 1fr 76px;gap:12px;align-items:center;padding-bottom:12px;border-bottom:4px double var(--deep);text-align:center}
.doc-seal{width:74px;height:74px;object-fit:contain;margin:auto}
.lh-line{font:400 10.5px/1.3 Georgia,'Times New Roman',serif;color:#2b3a40}
.lh-rep{font-size:9.5px;letter-spacing:.14em;text-transform:uppercase}
.lh-brgy{font:700 19px/1.15 Georgia,'Times New Roman',serif;letter-spacing:.03em;color:var(--deep);margin:3px 0 2px;text-transform:uppercase}
.lh-rms{font:700 8.5px var(--paper-body);letter-spacing:.24em;color:var(--teal);margin-top:2px}

/* Title block */
.ttl{display:flex;justify-content:space-between;align-items:flex-end;gap:14px;margin:16px 0 10px}
.ttl-kick{font:700 9.5px var(--paper-body);letter-spacing:.2em;text-transform:uppercase;color:var(--teal)}
.ttl h1{margin:3px 0 0;font:800 23px/1.08 var(--paper-display);letter-spacing:-.012em;color:var(--deep)}
.ttl-period{flex:none;text-align:right;font-size:10.5px;color:var(--mut)}
.ttl-period b{display:block;font:800 12px var(--paper-display);color:var(--deep)}

.meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1px;background:var(--rule);border:1px solid var(--rule);border-left:4px solid var(--teal);border-radius:4px;overflow:hidden;margin-bottom:4px}
.meta div{background:var(--soft);padding:6px 10px;min-width:0}
.meta small{display:block;font:700 8.5px var(--paper-body);letter-spacing:.12em;text-transform:uppercase;color:var(--mut)}
.meta span{display:block;font-size:11px;font-weight:600;color:var(--ink);overflow-wrap:anywhere}

/* Section heading */
.sec{display:flex;align-items:center;gap:9px;margin:15px 0 7px;break-after:avoid}
.sec-n{flex:none;padding:2px 8px;border-radius:4px;background:var(--deep);color:#fff;font:700 10px ui-monospace,Consolas,monospace;letter-spacing:.06em}
.sec-t{flex:none;font:800 11.5px var(--paper-display);letter-spacing:.05em;text-transform:uppercase;color:var(--deep)}
.sec::after{content:'';flex:1;height:1px;background:var(--rule)}

.lead{margin:0 0 4px;padding:8px 12px;border-left:3px solid var(--teal);background:var(--soft);border-radius:0 4px 4px 0;font-size:11px;color:#2b3a40}
.note{margin:0;font-size:10.5px;color:var(--mut);font-style:italic}

/* Tables */
.sheet table{width:100%;border-collapse:collapse;font-size:10.5px;margin-bottom:4px}
.sheet th{background:var(--deep);color:#fff;font:700 9px var(--paper-body);letter-spacing:.08em;text-transform:uppercase;text-align:left;padding:6px 8px}
.sheet td{padding:5px 8px;border-bottom:1px solid var(--rule);vertical-align:top}
.sheet tbody tr:nth-child(even) td{background:#f8fbfb}
.sheet td:first-child{font-weight:600;color:var(--deep)}
.sheet td.num{text-align:right;font-weight:700;white-space:nowrap;font-variant-numeric:tabular-nums;background:linear-gradient(90deg,#cfeae6 var(--w,0%),transparent 0)!important}
.sheet .empty{text-align:center;color:var(--mut);font-weight:400}

/* Report pages */
.rp-top{display:flex;justify-content:space-between;align-items:center;gap:12px;padding-bottom:8px;border-bottom:1px solid var(--rule);font:700 9px var(--paper-body);letter-spacing:.12em;text-transform:uppercase;color:var(--mut)}
.rp-top span:first-child{display:flex;align-items:center;gap:8px}
.rp-top img{width:22px;height:22px;object-fit:contain}
.rp-title{display:flex;align-items:center;gap:14px;margin:16px 0 4px}
.rp-ico{flex:none;width:46px;height:46px;border-radius:12px;display:grid;place-items:center;background:linear-gradient(135deg,var(--deep),var(--teal));color:#fff;font-size:1.3rem}
.rp-title h2{margin:0;font:800 21px/1.1 var(--paper-display);letter-spacing:-.01em;color:var(--deep)}
.rp-title p{margin:3px 0 0;font-size:10.5px;color:var(--mut)}

.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:9px;margin:14px 0 16px}
.stat{position:relative;border:1px solid var(--rule);border-radius:6px;padding:11px 11px 9px;overflow:hidden;background:#fff}
.stat::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:var(--k,var(--teal))}
.stat:nth-child(1){--k:var(--teal)}.stat:nth-child(2){--k:var(--sun)}.stat:nth-child(3){--k:var(--coral)}.stat:nth-child(4){--k:var(--indigo)}
.stat small{display:block;min-height:24px;font:700 8.8px/1.25 var(--paper-body);letter-spacing:.07em;text-transform:uppercase;color:var(--mut)}
.stat strong{display:block;margin-top:3px;font:800 25px/1 var(--paper-display);letter-spacing:-.02em;color:var(--deep);font-variant-numeric:tabular-nums}

.tbls{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px}
.tbl h3{margin:0 0 6px;padding-left:8px;border-left:3px solid var(--teal);font:800 11px var(--paper-display);letter-spacing:.03em;text-transform:uppercase;color:var(--deep)}

/* Signatures */
.sign{display:grid;grid-template-columns:1fr 1fr;gap:34px;margin-top:22px;font-size:11px;text-align:center;break-inside:avoid}
.sign .lbl{text-align:left;font:700 9px var(--paper-body);letter-spacing:.14em;text-transform:uppercase;color:var(--mut)}
.sign .line{border-bottom:1px solid var(--deep);margin:28px 6% 5px}
.sign strong{color:var(--deep)}
.sign div div:last-child{color:var(--mut);font-size:10px}

/* Footer */
.sheet-foot{margin-top:auto;padding-top:10px;display:grid;grid-template-columns:1fr auto 1fr;gap:10px;font-size:9.5px;color:var(--mut);border-top:1px solid var(--rule)}
.sheet-foot span:nth-child(2){text-align:center}
.sheet-foot span:last-child{text-align:right;font-weight:700;color:var(--deep)}
.sheet .grow{flex:1 1 auto;min-height:14px}

/* Empty state */
.sr-empty{max-width:794px;margin:0 auto;background:var(--surface);border:2px dashed var(--border-soft);border-radius:20px;padding:3rem 1.5rem;text-align:center}
.sr-empty .big{width:76px;height:76px;margin:0 auto 1rem;border-radius:22px;display:grid;place-items:center;background:var(--accent-soft);color:var(--primary);font-size:2rem}

@media(max-width:767.98px){
    .sheet{--pad-x:18px;--pad-top:28px;aspect-ratio:auto;min-height:0}
    .stats{grid-template-columns:1fr 1fr}
    .lh{grid-template-columns:52px 1fr 52px}
    .doc-seal{width:50px;height:50px}
    .lh-brgy{font-size:15px}
    .ttl{flex-direction:column;align-items:flex-start}.ttl-period{text-align:left}
    .meta{grid-template-columns:1fr}
    .sign{grid-template-columns:1fr}
    .sheet-foot{grid-template-columns:1fr}.sheet-foot span{text-align:left!important}
}

/* ============================================================
   PRINT
   @page margin is 0 so the browser adds no URL/date header.
   Spacing lives inside each sheet. Size is set from the paper
   picker (JS) and is always portrait.
   ============================================================ */
@page{margin:0}

@media print{
    html,body{
        display:block!important;height:auto!important;min-height:0!important;margin:0!important;padding:0!important;
        overflow:visible!important;background:#fff!important;
        -webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;
    }
    .sidebar,.topbar,.sidebar-backdrop,.no-print,.alert{display:none!important}
    .main-content{margin:0!important}.page-content{padding:0!important}

    /* added by JS: isolate the report from the app shell */
    .sr-print-hide{display:none!important}
    .sr-print-ancestor{
        display:block!important;position:static!important;float:none!important;width:auto!important;max-width:none!important;
        height:auto!important;min-height:0!important;margin:0!important;padding:0!important;overflow:visible!important;
        transform:none!important;background:transparent!important;border:0!important;box-shadow:none!important;
    }

    .sr,.sr.container-fluid{padding:0!important;margin:0!important;max-width:none!important}
    .sr-grid{display:block!important}
    .sr-desk{background:none!important;padding:0!important;border-radius:0!important}
    *{text-shadow:none!important}

    .sheet{
        --pad-x:13mm;--pad-top:13mm;
        width:100%;max-width:none;aspect-ratio:auto;
        min-height:calc(var(--ph) - 1.5mm);
        margin:0;padding:var(--pad-top) var(--pad-x) 9mm;
        border-radius:0;box-shadow:none;font-size:8.8pt;
        break-after:page;page-break-after:always;
    }
    .sheet:last-child{break-after:auto;page-break-after:auto}
    .sheet::before{border-radius:0;height:2.2mm}
    .doc-seal{width:19mm;height:19mm}
    .lh{grid-template-columns:19mm 1fr 19mm}

    .sheet th,.sheet td.num,.sheet tbody tr:nth-child(even) td,.sec-n,.rp-ico,.stat::before,.sheet::before,.lead,.meta div{
        -webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;
    }
    .tbl,.stat,.sheet tr,.sign,.lh,.ttl,.meta,.lead{break-inside:avoid;page-break-inside:avoid}
    .sheet table{font-size:8.4pt}
    .stat strong{font-size:20pt}
    .ttl h1{font-size:19pt}
    .rp-title h2{font-size:17pt}
}
</style>

<div class="container-fluid sr" data-paper="a4">

    <div class="sr-head no-print">
        <div>
            <h1>Summary Reports</h1>
            <p>Pick a period and the reports you need. The paper preview updates by itself, then print.</p>
        </div>
        <div class="sr-meta">
            <span class="sr-pill"><i class="bi bi-calendar3"></i>{{ $days }} {{ $days === 1 ? 'day' : 'days' }}</span>
            <span class="sr-pill"><i class="bi bi-files"></i><span id="selectionIndicatorText">{{ $selectedCount }} selected</span></span>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger no-print">
            <div class="fw-semibold mb-1">Please check the following:</div>
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="sr-grid">

        {{-- ================= BUILDER ================= --}}
        <aside class="sr-builder no-print">
            <form id="summaryReportForm" method="GET" action="{{ route('summary-reports.index') }}" class="d-contents" style="display:contents">

                <div class="sr-card">
                    <h2><i class="bi bi-calendar-range"></i>Reporting period</h2>
                    <div class="hint">Choose a shortcut or set exact dates.</div>
                    <div class="sr-chips">
                        <button type="button" class="sr-chip" data-preset="month">This month</button>
                        <button type="button" class="sr-chip" data-preset="lastmonth">Last month</button>
                        <button type="button" class="sr-chip" data-preset="quarter">This quarter</button>
                        <button type="button" class="sr-chip" data-preset="ytd">Year to date</button>
                    </div>
                    <div class="sr-dates">
                        <div><label for="from">From</label><input type="date" class="form-control" id="from" name="from" value="{{ $fromDate->format('Y-m-d') }}"></div>
                        <div><label for="to">To</label><input type="date" class="form-control" id="to" name="to" value="{{ $toDate->format('Y-m-d') }}"></div>
                    </div>
                    <div class="sr-range" id="srRange"><i class="bi bi-calendar-check"></i><span>{{ $period }} · {{ $days }} {{ $days === 1 ? 'day' : 'days' }}</span></div>
                </div>

                <div class="sr-card">
                    <h2><i class="bi bi-ui-checks-grid"></i>Reports to include</h2>
                    <div class="hint">Each one becomes its own page.</div>
                    <div class="sr-all">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="selectAllReports">
                            <label class="form-check-label fw-semibold" for="selectAllReports">Select all</label>
                        </div>
                    </div>
                    <div class="sr-tiles">
                        @foreach ($reportTypes as $key => $label)
                            <label class="tile" for="report_{{ $key }}">
                                <input class="report-checkbox" type="checkbox" name="reports[]" value="{{ $key }}" id="report_{{ $key }}" {{ in_array($key, $selectedReports, true) ? 'checked' : '' }}>
                                <span class="tile-ico"><i class="bi {{ $defs[$key]['icon'] ?? 'bi-file-earmark-bar-graph' }}"></i></span>
                                <span class="tile-txt">{{ $label }}</span>
                                <i class="bi bi-check-circle-fill tile-check"></i>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="sr-card">
                    <div class="sr-actions">

                        <div class="sr-paper">
                            <label for="srPaper"><i class="bi bi-file-earmark-ruled me-1"></i>Paper size</label>
                            <select id="srPaper" class="form-select" aria-label="Paper size">
                                <option value="a4">A4 &middot; 210 &times; 297 mm</option>
                                <option value="short">Short &middot; 8.5 &times; 11 in</option>
                                <option value="long">Long &middot; 8.5 &times; 13 in</option>
                                <option value="legal">Legal &middot; 8.5 &times; 14 in</option>
                            </select>
                        </div>

                        <div class="sr-status" id="srStatus" aria-live="polite"></div>

                        @if ($selectedCount > 0)
                            <a href="{{ route('summary-reports.print', ['from' => $fromDate->format('Y-m-d'), 'to' => $toDate->format('Y-m-d'), 'reports' => $selectedReports]) }}" class="btn btn-primary" target="_blank">
                                <i class="bi bi-printer me-1"></i>Print {{ $pageCount }} {{ $pageCount === 1 ? 'page' : 'pages' }}
                            </a>
                        @endif
                        <button type="submit" class="btn btn-outline-primary" name="generate" value="1"><i class="bi bi-arrow-clockwise me-1"></i>Refresh preview</button>
                        <button type="button" class="btn btn-link text-decoration-none text-muted" id="resetReports"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</button>
                    </div>
                </div>

            </form>
        </aside>

        {{-- ================= PAPER DESK ================= --}}
        <section class="sr-desk print-area" id="reportPreviewArea">

            @if ($selectedCount === 0)

                <div class="sr-empty no-print">
                    <div class="big"><i class="bi bi-file-earmark-bar-graph"></i></div>
                    <h5 class="fw-bold">Your report is a blank page</h5>
                    <p class="text-muted mb-3">Pick at least one report on the left and its page appears here.</p>
                    <button type="button" class="btn btn-primary" id="quickAll"><i class="bi bi-check2-all me-1"></i>Select all reports</button>
                </div>

            @else

                <div class="sr-bar no-print">
                    <strong><i class="bi bi-eye me-1"></i>Preview · {{ $pageCount }} {{ $pageCount === 1 ? 'page' : 'pages' }}</strong>
                    <span class="sr-tags">
                        <span class="sr-pill" id="srPaperBadge">A4</span>
                        <span class="text-muted small">{{ $period }}</span>
                    </span>
                </div>

                {{-- COVER PAGE --}}
                <article class="sheet">

                    <header class="lh">
                        <img src="{{ $seal('municipality-bani-seal') }}" class="doc-seal" alt="Municipality of Bani Seal">
                        <div>
                            <div class="lh-line lh-rep">Republic of the Philippines</div>
                            <div class="lh-line">Province of Pangasinan &middot; Municipality of Bani</div>
                            <div class="lh-brgy">Barangay Poblacion</div>
                            <div class="lh-rms">RECORD MANAGEMENT SYSTEM</div>
                        </div>
                        <img src="{{ $seal('barangay-poblacion-seal') }}" class="doc-seal" alt="Barangay Poblacion Seal">
                    </header>

                    <div class="ttl">
                        <div>
                            <div class="ttl-kick">Official Report</div>
                            <h1>Barangay Operations Summary Report</h1>
                        </div>
                    </div>

                    <div class="meta">
                        <div><small>Coverage Period</small><span>{{ $period }}</span></div>
                        <div><small>Date Generated</small><span>{{ now()->format('F d, Y') }}</span></div>
                        <div><small>Barangay</small><span>Barangay Poblacion, Municipality of Bani, Province of Pangasinan</span></div>
                        <div><small>Prepared By</small><span>{{ $preparedBy }}</span></div>
                    </div>

                    <div class="sec"><span class="sec-n">I</span><span class="sec-t">Executive Summary</span></div>
                    <p class="lead">During the selected reporting period, the Barangay Poblacion Record Management System recorded and summarized the operational activities represented by the selected report areas. The figures below are calculated from the actual records available in the system and are presented to support administrative monitoring and reporting.</p>

                    @foreach ($coverTables as $ti => $t)
                        @php [$secNum, $secTitle] = array_pad(explode('. ', $t[0], 2), 2, ''); @endphp
                        <div class="sec"><span class="sec-n">{{ $secNum }}</span><span class="sec-t">{{ $secTitle }}</span></div>
                        <table>
                            <thead><tr>@foreach ($t[1] as $hi => $th)<th style="{{ $ti === 1 && $hi === 1 ? 'text-align:right' : '' }}">{{ $th }}</th>@endforeach</tr></thead>
                            <tbody>
                                @forelse ($t[2] as $row)
                                    <tr>@foreach ($row as $ci => $cell)<td style="{{ $ti === 1 && $ci === 1 ? 'text-align:right;font-weight:700' : '' }}">{{ $cell }}</td>@endforeach</tr>
                                @empty
                                    <tr><td colspan="3" class="empty">No indicators are available for the selected reports.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endforeach

                    <div class="sec"><span class="sec-n">V</span><span class="sec-t">Financial Summary</span></div>
                    <p class="note">Financial allocation, expenditure, and balance figures are not included in this generated summary because the current Record Management System does not contain a financial budget and expenditure module. No financial values are invented or estimated by this report.</p>

                    <div class="sec"><span class="sec-n">VI</span><span class="sec-t">Administrative Next Steps</span></div>
                    <p class="lead">Authorized barangay personnel may use the operational indicators above to review pending records, update incomplete transaction statuses, and complete follow-up actions reflected in the system. The report is intended as an administrative summary of recorded RMS activities for the selected reporting period.</p>

                    <div class="sign">
                        <div><div class="lbl">Prepared by</div><div class="line"></div><strong>{{ $preparedBy }}</strong><div>Authorized Barangay Personnel</div></div>
                        <div><div class="lbl">Noted by</div><div class="line"></div><strong>Punong Barangay</strong><div>Barangay Poblacion</div></div>
                    </div>

                    <div class="grow"></div>

                    <div class="sheet-foot">
                        <span>Barangay Poblacion Record Management System</span>
                        <span>{{ $period }}</span>
                        <span>Page 1 of {{ $pageCount }}</span>
                    </div>
                </article>

                {{-- ONE PAGE PER SELECTED REPORT --}}
                @foreach ($presentKeys as $pi => $key)
                    @php $def = $defs[$key]; $data = $reports[$key]; @endphp
                    <article class="sheet">

                        <div class="rp-top">
                            <span><img src="{{ $seal('barangay-poblacion-seal') }}" alt="">Barangay Poblacion &middot; Operations Summary Report</span>
                            <span>{{ $period }}</span>
                        </div>

                        <div class="rp-title">
                            <div class="rp-ico"><i class="bi {{ $def['icon'] }}"></i></div>
                            <div>
                                <h2>{{ $def['title'] }}</h2>
                                <p>Reporting period: {{ $period }}</p>
                            </div>
                        </div>

                        <div class="stats">
                            @foreach ($def['stats'] as $st)
                                <div class="stat"><small>{{ $st[0] }}</small><strong>{{ $n($data[$st[1]] ?? 0) }}</strong></div>
                            @endforeach
                        </div>

                        <div class="tbls">
                            @foreach ($def['tables'] as $t)
                                @php
                                    $rows = isset($t['map'])
                                        ? collect($data[$t['map']] ?? [])->map(fn ($v, $k) => [$k, $v])->values()->all()
                                        : array_map(fn ($x) => [$x[0], $data[$x[1]] ?? 0], $t['rows']);
                                    $max = max(array_merge([1], array_map(fn ($x) => (int) $x[1], $rows)));
                                @endphp
                                <section class="tbl">
                                    <h3>{{ $t['title'] }}</h3>
                                    <table>
                                        <thead><tr><th>{{ $t['head'] }}</th><th style="text-align:right">{{ $t['count'] ?? 'Count' }}</th></tr></thead>
                                        <tbody>
                                            @forelse ($rows as $row)
                                                <tr><td>{{ $row[0] }}</td><td class="num" style="--w:{{ round(((int) $row[1]) / $max * 100) }}%">{{ $n($row[1]) }}</td></tr>
                                            @empty
                                                <tr><td colspan="2" class="empty">No records.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </section>
                            @endforeach
                        </div>

                        <div class="grow"></div>

                        <div class="sheet-foot">
                            <span>Barangay Poblacion Record Management System</span>
                            <span>Generated {{ now()->format('M d, Y') }}</span>
                            <span>Page {{ $pi + 2 }} of {{ $pageCount }}</span>
                        </div>
                    </article>
                @endforeach

            @endif
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('summaryReportForm');
    const selectAll = document.getElementById('selectAllReports');
    const boxes = Array.from(document.querySelectorAll('.report-checkbox'));
    const label = document.getElementById('selectionIndicatorText');
    const status = document.getElementById('srStatus');
    const from = document.getElementById('from');
    const to = document.getElementById('to');
    const rangeLine = document.querySelector('#srRange span');
    const chips = document.querySelectorAll('[data-preset]');
    let timer;

    const pad = v => String(v).padStart(2, '0');
    const fmt = d => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    const nice = iso => new Date(iso + 'T00:00:00').toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' });

    /* Safe status writer: a missing #srStatus can never stop the preview again */
    function setStatus(html) { if (status) status.innerHTML = html; }

    function refreshIndicator() {
        const n = boxes.filter(b => b.checked).length;
        if (label) label.textContent = n === boxes.length && n > 0 ? 'All reports' : n + ' selected';
        if (selectAll) {
            selectAll.checked = n === boxes.length && n > 0;
            selectAll.indeterminate = n > 0 && n < boxes.length;
        }
    }

    /* Date shortcuts */
    function presetRange(key) {
        const t = new Date(), y = t.getFullYear(), m = t.getMonth();
        return {
            month: [new Date(y, m, 1), new Date(y, m + 1, 0)],
            lastmonth: [new Date(y, m - 1, 1), new Date(y, m, 0)],
            quarter: [new Date(y, Math.floor(m / 3) * 3, 1), new Date(y, Math.floor(m / 3) * 3 + 3, 0)],
            ytd: [new Date(y, 0, 1), t]
        }[key];
    }

    function markActivePreset() {
        chips.forEach(function (c) {
            const r = presetRange(c.dataset.preset);
            c.classList.toggle('active', fmt(r[0]) === from.value && fmt(r[1]) === to.value);
        });
    }

    function refreshRangeLine() {
        if (!rangeLine || !from.value || !to.value || from.value > to.value) return;
        const days = Math.round((new Date(to.value) - new Date(from.value)) / 86400000) + 1;
        rangeLine.textContent = nice(from.value) + ' – ' + nice(to.value) + ' · ' + days + (days === 1 ? ' day' : ' days');
    }

    /* Waits a moment after the last click so several ticks cause one reload */
    function schedulePreview() {
        refreshIndicator();
        markActivePreset();
        if (from.value && to.value && from.value > to.value) {
            setStatus('<i class="bi bi-exclamation-triangle-fill text-danger"></i>The start date must be before the end date.');
            return;
        }
        refreshRangeLine();
        setStatus('<span class="spinner-border spinner-border-sm"></span>Updating preview...');
        const area = document.getElementById('reportPreviewArea');
        if (area) area.classList.add('sr-loading');
        clearTimeout(timer);
        timer = setTimeout(loadPreview, 700);
    }

    function loadPreview() {
        const params = new URLSearchParams();
        if (from.value) params.set('from', from.value);
        if (to.value) params.set('to', to.value);
        boxes.filter(b => b.checked).forEach(b => params.append('reports[]', b.value));
        params.set('preview', '1');
        window.location.href = form.action + '?' + params.toString();
    }

    boxes.forEach(b => b.addEventListener('change', schedulePreview));
    if (selectAll) selectAll.addEventListener('change', function () { boxes.forEach(b => b.checked = selectAll.checked); schedulePreview(); });
    [from, to].forEach(el => el.addEventListener('change', schedulePreview));

    const quickAll = document.getElementById('quickAll');
    if (quickAll) quickAll.addEventListener('click', function () { boxes.forEach(b => b.checked = true); schedulePreview(); });

    chips.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const r = presetRange(btn.dataset.preset);
            from.value = fmt(r[0]);
            to.value = fmt(r[1]);
            schedulePreview();
        });
    });

    document.getElementById('resetReports').addEventListener('click', function () { window.location.href = form.action; });

    refreshIndicator();
    markActivePreset();


    /* =========================================================
       PAPER SIZE  (A4 / Short / Long / Legal, always portrait)
       Preview shape follows data-paper; print size is injected
       as @page. The choice is remembered, so the print tab
       opened from the button uses the same paper.
    ========================================================== */

    const PAPERS = {
        a4:    { size: 'A4 portrait', badge: 'A4' },
        short: { size: '8.5in 11in',  badge: 'Short · 8.5 × 11 in' },
        long:  { size: '8.5in 13in',  badge: 'Long · 8.5 × 13 in' },
        legal: { size: '8.5in 14in',  badge: 'Legal · 8.5 × 14 in' }
    };

    const srRoot = document.querySelector('.sr');
    const paperSel = document.getElementById('srPaper');
    const paperBadge = document.getElementById('srPaperBadge');
    const pageStyle = document.createElement('style');
    document.head.appendChild(pageStyle);

    function applyPaper(key) {
        if (!PAPERS[key]) key = 'a4';
        srRoot.setAttribute('data-paper', key);
        pageStyle.textContent = '@page{size:' + PAPERS[key].size + ';margin:0}';
        if (paperSel) paperSel.value = key;
        if (paperBadge) paperBadge.textContent = PAPERS[key].badge;
        try { localStorage.setItem('sr-paper', key); } catch (e) {}
    }

    let savedPaper = 'a4';
    try { savedPaper = localStorage.getItem('sr-paper') || 'a4'; } catch (e) {}
    applyPaper(savedPaper);

    if (paperSel) paperSel.addEventListener('change', function () { applyPaper(paperSel.value); });


    /* =========================================================
       PRINT: isolate the report from the app shell
       (sidebar, topbar and wrappers never reach the paper)
    ========================================================== */

    function prepareShell() {
        const area = document.getElementById('reportPreviewArea');
        let node = area;
        while (node && node !== document.body) {
            const parent = node.parentElement;
            if (!parent) break;
            Array.from(parent.children).forEach(function (sib) {
                if (sib !== node) sib.classList.add('sr-print-hide');
            });
            parent.classList.add('sr-print-ancestor');
            node = parent;
        }
    }

    function restoreShell() {
        document.querySelectorAll('.sr-print-hide').forEach(el => el.classList.remove('sr-print-hide'));
        document.querySelectorAll('.sr-print-ancestor').forEach(el => el.classList.remove('sr-print-ancestor'));
    }

    window.addEventListener('beforeprint', prepareShell);
    window.addEventListener('afterprint', restoreShell);

    @if ($printMode)
        document.body.classList.add('summary-print-mode');
        setTimeout(function () { window.print(); }, 300);
    @endif
});
</script>
@endsection
