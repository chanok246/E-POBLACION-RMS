@extends('layouts.app')

@section('title', 'Household Map')

@section('content')

@php
    $mapData = collect($mapHouseholds ?? $households ?? []);
@endphp

<div class="container-fluid px-3 px-md-4 pb-4 hm-page">

    {{-- HEADER --}}
    <header class="hm-hero">
        <div class="hm-hero-bg" aria-hidden="true"></div>

        <div class="hm-hero-in">
            <div class="hm-crumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span>
                <a href="{{ route('households.index') }}">Households</a> <span>/</span> Map
            </div>
            <h1><i class="bi bi-map-fill me-2"></i>Household Map</h1>
            <p>Where every household in Barangay Poblacion, Bani, Pangasinan lives, colored by Purok. The dashed red line marks the barangay border. This map is read-only; household locations are set on the Create and Edit Household pages.</p>
        </div>

        <div class="hm-hero-in hm-hero-right">
            <div class="hm-strip" role="group" aria-label="Map summary">
                <div><b>{{ $mapData->count() }}</b><span>Households</span></div>
                <div><b id="statMapped">0</b><span>On the map</span></div>
                <div><b id="statActive">0</b><span>Active</span></div>
                <div><b id="statPuroks">0</b><span>Puroks</span></div>
            </div>
            <a href="{{ route('households.index') }}" class="hm-btn-solid">
                <i class="bi bi-people-fill"></i> Household records
            </a>
        </div>
    </header>

    {{-- MAP WORKSPACE --}}
    <div class="hm-shell dock-open" id="hmShell">

        <div id="householdMap" class="hm-map" aria-label="Household location map"></div>

        {{-- SIDE DOCK: household list, search and Purok filters --}}
        <aside class="hm-dock" id="hmDock" aria-label="Map side panel">

            <div class="hm-dock-bar">
                <div class="hm-dock-title">
                    <i class="bi bi-house-fill"></i>
                    <span>Households</span>
                    <b id="panelCount">0</b>
                </div>

                <div class="hm-dock-actions">
                    <button type="button" id="panelMin" title="Minimize panel" aria-label="Minimize panel" aria-expanded="true">
                        <i class="bi bi-chevron-up"></i>
                    </button>
                    <button type="button" id="panelClose" title="Hide panel" aria-label="Hide panel">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="hm-dock-body">

                <section class="hm-pane hm-pane--list" id="paneHouseholds" aria-label="Households">

                    <div class="hm-search">
                        <i class="bi bi-search"></i>
                        <input type="search" id="householdSearch" placeholder="Search name, address, household no." autocomplete="off" aria-label="Search households">
                        <button type="button" id="clearSearch" class="d-none" aria-label="Clear search"><i class="bi bi-x-lg"></i></button>
                    </div>

                    <div>
                        <div class="small mb-2 hm-muted fw-semibold">Filter by Purok <span class="fw-normal">(select one or more)</span></div>
                        <div class="hm-chips" id="purokChips"><span class="small hm-muted">Loading Puroks…</span></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="fw-bold" id="visibleHouseholdBadge">0 households</span>
                        <button type="button" id="clearFilters" class="hm-linkbtn d-none">Clear filters</button>
                    </div>

                    <div class="hm-list" id="householdList"></div>
                    <div class="hm-note d-none" id="unmappedNote"></div>
                </section>

            </div>
        </aside>

        {{-- MAP TOOLS --}}
        <div class="hm-tools">
            <button type="button" id="panelToggle" class="on" title="Hide side panel" aria-label="Show or hide side panel" aria-pressed="true"><i class="bi bi-layout-sidebar-inset"></i></button>
            <button type="button" id="fullscreenButton" title="Full screen map" aria-label="Toggle full screen map" aria-pressed="false"><i class="bi bi-fullscreen"></i></button>
            <button type="button" id="fitHouseholdsButton" title="Fit all shown households" aria-label="Fit households"><i class="bi bi-arrows-angle-contract"></i></button>
            <button type="button" id="resetMapButton" title="Zoom to Barangay Poblacion border" aria-label="Zoom to Poblacion border"><i class="bi bi-pentagon"></i></button>
            <button type="button" id="locateMeButton" title="Show my current position on the map" aria-label="Show my current position on the map"><i class="bi bi-crosshair"></i></button>
            <button type="button" id="boundaryToggle" class="on" title="Hide Poblacion border" aria-label="Show or hide Poblacion border" aria-pressed="true"><i class="bi bi-bounding-box-circles"></i></button>
            <button type="button" id="layerToggle" class="on" title="Switch to street map" aria-label="Switch base map"><i class="bi bi-layers"></i></button>
            <button type="button" id="labelsToggle" title="Show road and place names" aria-label="Show road and place names" aria-pressed="false"><i class="bi bi-signpost-split"></i></button>
        </div>

        <div class="hm-status" id="mapStatus">Loading map…</div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">

<style>
.hm-page{
    --hm-text:#2b2023;
    --hm-muted:#5f4f54;
    --hm-surface:rgba(255,255,255,.96);
    --hm-surface-solid:#ffffff;
    --hm-soft:#f7f2f3;
    --hm-soft-2:#efe7e9;
    --hm-line:#e3d6d9;
    --hm-accent:#d63e4c;
    --hm-accent-2:#ef6b78;
    --hm-on-accent:#ffffff;
    --hm-accent-soft:#fdeaec;
    --hm-green:#2f8a3a;
    --hm-link:#b4232f;
    --hm-warn-bg:#fff1cf;
    --hm-warn-fg:#7a4e00;
    --hm-warn-line:#e8c26a;
    color:var(--hm-text);
}
html[data-theme="dark"] .hm-page,
[data-bs-theme="dark"] .hm-page{
    --hm-text:#f6eff1;
    --hm-muted:#c3d0c7;
    --hm-surface:rgba(26,42,33,.95);
    --hm-surface-solid:#1a2a21;
    --hm-soft:#223529;
    --hm-soft-2:#2b4033;
    --hm-line:#34503d;
    --hm-accent:#ff8793;
    --hm-accent-2:#ffa9b1;
    --hm-on-accent:#2a0c12;
    --hm-accent-soft:#3a1f26;
    --hm-green:#7fdc8a;
    --hm-link:#ffb3ba;
    --hm-warn-bg:#3a2f1a;
    --hm-warn-fg:#ffd98a;
    --hm-warn-line:#6b5522;
}
.hm-page,.hm-page *{box-sizing:border-box}
.hm-page :focus-visible{outline:3px solid var(--hm-green);outline-offset:2px}
.hm-muted{color:var(--hm-muted)}

/* HERO (compact so the map gets more room) */
.hm-hero{
    position:relative;border-radius:22px;padding:18px 26px;margin-bottom:14px;color:#fff;
    background:linear-gradient(120deg,#d63e4c 0%,#ef6b78 60%,#f59aa2 100%);
    box-shadow:0 14px 28px rgba(214,62,76,.22);
    display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap
}
html[data-theme="dark"] .hm-hero,[data-bs-theme="dark"] .hm-hero{background:linear-gradient(120deg,#8f2f40,#b84557);box-shadow:none}
.hm-hero-bg{position:absolute;inset:0;border-radius:inherit;overflow:hidden;pointer-events:none}
.hm-hero-bg::before{content:'';position:absolute;width:240px;height:240px;right:-60px;top:-110px;border-radius:50%;background:rgba(255,255,255,.13)}
.hm-hero-bg::after{content:'';position:absolute;width:150px;height:150px;right:240px;bottom:-100px;border-radius:50%;background:rgba(105,187,104,.38)}
.hm-hero-in{position:relative;z-index:2}
.hm-crumb{font-size:12px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#fff;opacity:.95;margin-bottom:4px}
.hm-crumb a{color:#fff;text-decoration:none;opacity:.9}
.hm-crumb a:hover{text-decoration:underline;opacity:1}
.hm-crumb span{opacity:.7;margin:0 4px}
.hm-hero h1{margin:0;font-size:clamp(1.4rem,2.6vw,1.85rem);font-weight:800;letter-spacing:-.02em;color:#fff}
.hm-hero p{margin:4px 0 0;font-size:.9rem;color:#fff;opacity:.96;max-width:560px}
.hm-hero-right{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.hm-btn-solid{display:inline-flex;align-items:center;gap:8px;min-height:44px;padding:.55rem 1.2rem;border-radius:14px;background:#fff;color:#b4232f!important;font-weight:800;text-decoration:none;border:0;box-shadow:0 8px 18px rgba(60,10,20,.2);transition:transform .15s,box-shadow .15s,background .15s}
.hm-btn-solid:hover{transform:translateY(-2px);box-shadow:0 12px 22px rgba(60,10,20,.26);background:#fff1f2}
.hm-strip{display:flex;border:1.5px solid rgba(255,255,255,.4);border-radius:16px;background:rgba(255,255,255,.18);overflow:hidden;color:#fff;backdrop-filter:blur(4px)}
.hm-strip>div{padding:.45rem .95rem;text-align:center;min-width:84px}
.hm-strip>div+div{border-left:1px solid rgba(255,255,255,.35)}
.hm-strip b{display:block;font-size:1.25rem;line-height:1.2;color:#fff;font-weight:800;font-variant-numeric:tabular-nums}
.hm-strip span{font-size:.74rem;color:#fff;opacity:.95;font-weight:600}

/* MAP WORKSPACE (taller so the map is the main focus) */
.hm-shell{position:relative;height:calc(100vh - 230px);min-height:620px;border-radius:24px;overflow:hidden;border:1.5px solid var(--hm-line);color:var(--hm-text);box-shadow:0 18px 50px -30px rgba(15,23,42,.55)}
.hm-shell.is-full{position:fixed;inset:0;z-index:2000;height:100vh!important;min-height:0;border-radius:0;border:0;box-shadow:none}
.hm-map{
    position:absolute;inset:0;z-index:0;background:#dfe5ea;
    --hm-text:#2b2023;--hm-muted:#5f4f54;--hm-surface:rgba(255,255,255,.96);
    --hm-surface-solid:#fff;--hm-soft:#f7f2f3;--hm-soft-2:#efe7e9;
    --hm-line:#e3d6d9;--hm-accent:#d63e4c;--hm-accent-2:#ef6b78;
    --hm-on-accent:#fff;--hm-accent-soft:#fdeaec;--hm-green:#2f8a3a;--hm-link:#b4232f;
    color:var(--hm-text)
}
.hm-map .leaflet-tile-pane{filter:none!important}
.hm-map .leaflet-container{-webkit-font-smoothing:antialiased}

/* SIDE DOCK */
.hm-dock{
    position:absolute;z-index:500;top:14px;left:14px;bottom:14px;width:372px;
    display:flex;flex-direction:column;overflow:hidden;
    border-radius:22px;color:var(--hm-text);background:var(--hm-surface);
    backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
    border:1.5px solid var(--hm-line);box-shadow:0 14px 36px -16px rgba(0,0,0,.45);
    transition:transform .3s ease,opacity .3s ease
}
.hm-dock.is-hidden{transform:translateX(-115%);opacity:0;pointer-events:none}
.hm-dock.is-min{bottom:auto}
.hm-dock.is-min .hm-dock-body{display:none}
.hm-dock.is-min .hm-dock-bar{border-bottom:0}

.hm-dock-bar{display:flex;align-items:center;gap:.5rem;padding:10px;border-bottom:1.5px solid var(--hm-line)}
.hm-dock-title{display:flex;align-items:center;gap:.5rem;flex:1;min-width:0;padding:0 .35rem;font-size:.95rem;font-weight:800;color:var(--hm-text)}
.hm-dock-title b{display:inline-grid;place-items:center;min-width:24px;height:20px;padding:0 .4rem;border-radius:999px;background:var(--hm-accent-soft);color:var(--hm-link);font-size:.72rem;font-weight:800;font-variant-numeric:tabular-nums}

.hm-dock-actions{display:flex;gap:6px;flex:none}
.hm-dock-actions button{width:34px;height:34px;display:grid;place-items:center;padding:0;border-radius:10px;border:1.5px solid var(--hm-line);background:var(--hm-soft);color:var(--hm-text);cursor:pointer;transition:background .15s,border-color .15s,color .15s}
.hm-dock-actions button:hover{background:var(--hm-accent-soft);border-color:var(--hm-accent-2);color:var(--hm-accent)}

.hm-dock-body{flex:1;min-height:0;display:flex;flex-direction:column}
.hm-pane{flex:1;min-height:0;display:flex;flex-direction:column;gap:.85rem;padding:14px 14px 16px;overflow-y:auto;scrollbar-width:thin}
.hm-pane--list{overflow:hidden}

.hm-linkbtn{border:0;background:transparent;padding:0;color:var(--hm-link);font-weight:800;font-size:.85rem;cursor:pointer}
.hm-linkbtn:hover{text-decoration:underline}

/* SEARCH */
.hm-search{display:flex;align-items:center;gap:.55rem;min-height:44px;padding:.45rem .85rem;border-radius:14px;background:var(--hm-soft);border:1.5px solid var(--hm-line);color:var(--hm-text)}
.hm-search:hover{border-color:var(--hm-accent-2)}
.hm-search:focus-within{border-color:var(--hm-accent);box-shadow:0 0 0 .22rem var(--hm-accent-soft);background:var(--hm-surface-solid)}
.hm-search input{flex:1;min-width:0;border:0;outline:0;background:transparent;color:var(--hm-text);font-size:.92rem;font-weight:600}
.hm-search input::placeholder{color:var(--hm-muted);opacity:.85;font-weight:500}
.hm-search input::-webkit-search-cancel-button{display:none}
.hm-search i{color:var(--hm-muted)}
.hm-search button{border:0;background:var(--hm-soft-2);color:var(--hm-text);width:28px;height:28px;border-radius:8px;display:grid;place-items:center;padding:0}
.hm-search button:hover{background:var(--hm-accent-soft);color:var(--hm-accent)}

/* PUROK FILTERS */
.hm-chips{display:flex;flex-wrap:wrap;gap:.45rem}
.hm-chip{display:inline-flex;align-items:center;gap:.45rem;padding:.3rem .75rem;border-radius:999px;border:1.5px solid var(--hm-line);background:transparent;color:var(--hm-text);font-size:.8rem;font-weight:700;cursor:pointer}
.hm-chip i{width:11px;height:11px;border-radius:50%;background:var(--c);box-shadow:0 0 0 1.5px rgba(127,127,127,.5)}
.hm-chip em{font-style:normal;color:var(--hm-muted);font-weight:700}
.hm-chip:hover{background:var(--hm-soft);border-color:var(--c)}
.hm-chip.on{border-color:var(--c);background:color-mix(in srgb,var(--c) 24%,transparent);font-weight:800}
.hm-chip.on em{color:var(--hm-text)}

/* HOUSEHOLD LIST */
.hm-list{flex:1;min-height:0;overflow:auto;display:flex;flex-direction:column;gap:.35rem;margin:0 -6px;padding:0 6px;scrollbar-width:thin}
.hm-item{display:flex;align-items:center;gap:.75rem;padding:.6rem .7rem;border-radius:14px;border:1.5px solid transparent;cursor:pointer;text-align:left;background:transparent;color:var(--hm-text);width:100%}
.hm-item>i{color:var(--hm-muted)}
.hm-item:hover{background:var(--hm-soft)}
.hm-item.active{background:color-mix(in srgb,var(--c) 22%,transparent);border-color:var(--c)}
.hm-av{flex:none;width:38px;height:38px;border-radius:12px;display:grid;place-items:center;background:var(--c);color:var(--ct,#fff);font-size:1rem;box-shadow:0 0 0 1px rgba(127,127,127,.3)}
.hm-item .min-w-0{min-width:0;flex:1}
.hm-title{color:var(--hm-text);font-weight:800}
.hm-sub2{color:var(--hm-muted);font-weight:500}
.hm-empty{margin:auto;text-align:center;padding:2rem 1rem;color:var(--hm-muted)}
.hm-empty .fw-semibold{color:var(--hm-text);font-weight:800!important}
.hm-note{font-size:.8rem;font-weight:600;padding:.6rem .8rem;border-radius:12px;background:var(--hm-warn-bg);border:1.5px solid var(--hm-warn-line);color:var(--hm-warn-fg)}
.hm-note a{color:var(--hm-warn-fg);font-weight:800;text-decoration:underline}
.hm-item.is-off{cursor:default;opacity:.92}
.hm-item.is-off:hover{background:transparent}
.hm-item.is-off .hm-av{background:var(--hm-soft-2);color:var(--hm-text)}
.hm-badge{flex:none;padding:.25rem .6rem;border-radius:999px;font-size:.7rem;font-weight:800;background:var(--hm-warn-bg);color:var(--hm-warn-fg);border:1px solid var(--hm-warn-line);white-space:nowrap}
.hm-fail{display:grid;place-items:center;height:100%;padding:2rem;text-align:center;color:var(--hm-muted)}
.hm-fail .fw-semibold{color:var(--hm-text)}

/* MAP TOOLS */
.hm-tools{
    position:absolute;z-index:520;top:14px;right:14px;
    display:flex;flex-direction:column;gap:.45rem;pointer-events:auto
}
.hm-tools button{
    position:relative;z-index:521;pointer-events:auto;
    width:42px;height:42px;display:grid;place-items:center;border-radius:13px;
    border:1.5px solid var(--hm-line);background:var(--hm-surface-solid);color:var(--hm-text);
    box-shadow:0 8px 22px -10px rgba(0,0,0,.5);cursor:pointer;
    transition:background .15s,border-color .15s,color .15s,transform .15s
}
.hm-tools button:hover{background:var(--hm-soft);border-color:var(--hm-accent-2);color:var(--hm-accent);transform:translateY(-1px)}
.hm-tools button.on{background:var(--hm-accent);color:var(--hm-on-accent);border-color:var(--hm-accent)}
.hm-tools button.on:hover{filter:brightness(1.08);color:var(--hm-on-accent)}
.hm-tools button:disabled{opacity:.45;cursor:not-allowed;transform:none}
.hm-status{position:absolute;z-index:500;left:50%;bottom:14px;transform:translateX(-50%);max-width:calc(100% - 28px);padding:.4rem 1rem;border-radius:999px;font-size:.8rem;font-weight:600;background:rgba(20,10,14,.86);color:#fff;border:1px solid rgba(255,255,255,.2);backdrop-filter:blur(8px);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;pointer-events:none;transition:left .3s ease,max-width .3s ease}
.hm-shell.dock-open .hm-status{left:calc(50% + 193px);max-width:calc(100% - 372px - 150px)}

/* LEAFLET CONTROLS */
.hm-map .leaflet-bar{border:1.5px solid var(--hm-line);box-shadow:0 8px 22px -10px rgba(0,0,0,.5);border-radius:12px;overflow:hidden}
.hm-map .leaflet-bar a,.hm-map .leaflet-bar a:hover{background-color:var(--hm-surface-solid);color:var(--hm-text);border-bottom-color:var(--hm-line)}
.hm-map .leaflet-bar a:hover{background-color:var(--hm-soft);color:var(--hm-accent)}
.hm-map .leaflet-bar a.leaflet-disabled{color:var(--hm-muted);opacity:.6}
.hm-map .leaflet-control-scale-line{background:var(--hm-surface);color:var(--hm-text);border-color:var(--hm-text);text-shadow:none;font-weight:700}
.hm-map .leaflet-control-attribution{background:var(--hm-surface);color:var(--hm-muted);border-radius:8px 0 0 0}
.hm-map .leaflet-control-attribution a{color:var(--hm-link)}

/* BARANGAY BOUNDARY */
.hm-bd-wrap{background:none;border:0;pointer-events:none}
.hm-bd-label{display:inline-block;transform:translate(-50%,-50%);white-space:nowrap;padding:3px 10px;border-radius:8px;background:rgba(255,255,255,.92);border:1.5px solid #d63e4c;color:#b4232f;font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;box-shadow:0 4px 10px rgba(0,0,0,.25)}

/* HOUSEHOLD MARKERS */
.hm-pin-wrap,.hm-cluster{background:none;border:0}
.hm-pin{position:relative;display:block;width:26px;height:26px;background:var(--c);border:2px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 0 0 1px rgba(0,0,0,.35),0 3px 8px rgba(0,0,0,.5)}
.hm-pin::after{content:"";position:absolute;inset:6px;border-radius:50%;background:#fff}
.hm-cluster span{display:grid;place-items:center;width:42px;height:42px;border-radius:50%;background:var(--bg,#6c757d);color:var(--ct,#fff);font-weight:800;font-size:.9rem;border:3px solid rgba(255,255,255,.95);box-shadow:0 0 0 1px rgba(0,0,0,.3),0 6px 16px rgba(0,0,0,.4)}
.hm-cluster span.mixed{background:var(--ring);color:#2b2023}
.hm-cluster span.mixed b{display:grid;place-items:center;width:24px;height:24px;border-radius:50%;background:#fff;font-size:.8rem;font-weight:800}

/* POPUPS */
.hm-map .leaflet-popup-content-wrapper,.hm-map .leaflet-popup-tip{background:var(--hm-surface-solid);color:var(--hm-text)}
.hm-map .leaflet-popup-content-wrapper{border-radius:18px;padding:0;overflow:hidden;border:1.5px solid var(--hm-line)}
.hm-map .leaflet-popup-content{margin:0;width:auto!important;color:var(--hm-text)}
.hm-popup{min-width:250px;font-size:13px;line-height:1.5;color:var(--hm-text)}
.hm-popup-head{padding:.85rem 1rem;color:var(--ct,#fff);background:var(--c)}
.hm-popup-head .t{font-weight:800;font-size:15px}
.hm-popup-body{padding:.8rem 1rem;color:var(--hm-text);font-weight:500}
.hm-popup-body div{margin-bottom:.35rem}
.hm-popup-body i{width:18px;display:inline-block;color:var(--hm-muted)}
.hm-map a.hm-popup-link{display:block;text-align:center;padding:.5rem;border-radius:10px;background:var(--hm-soft);color:var(--hm-text);border:1.5px solid var(--hm-line);font-weight:800;text-decoration:none;margin-top:.55rem}
.hm-map a.hm-popup-link:hover{border-color:var(--hm-accent);color:var(--hm-accent)}
.hm-popup-body .hm-coords{margin-top:.55rem;padding:.55rem .7rem .6rem;border-radius:12px;background:var(--hm-soft);border:1.5px solid var(--hm-line)}
.hm-popup-body .hm-coord{display:flex;justify-content:space-between;align-items:baseline;gap:.75rem;margin-bottom:.2rem}
.hm-popup-body .hm-coord span{color:var(--hm-muted);font-weight:700;font-size:12px}
.hm-popup-body .hm-coord b{color:var(--hm-text);font-weight:800;font-variant-numeric:tabular-nums;text-align:right;word-break:break-all;user-select:all}
.hm-popup-body .hm-copy{display:flex;align-items:center;justify-content:center;gap:.45rem;width:100%;min-height:38px;margin-top:.45rem;padding:.4rem .75rem;border:0;border-radius:10px;background:var(--hm-accent);color:var(--hm-on-accent);font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;transition:filter .15s}
.hm-popup-body .hm-copy:hover{filter:brightness(1.08)}
.hm-popup-body .hm-copy.is-done{background:var(--hm-green)}
.hm-popup-body .hm-copy-msg{min-height:1.1em;margin:.35rem 0 0;font-size:12px;font-weight:700;color:var(--hm-green)}
.hm-popup-body .hm-copy-msg.is-err{color:var(--hm-link)}
.hm-popup-body .hm-copy-manual{margin:.2rem 0 0}
.hm-popup-body .hm-copy-manual input{display:block;width:100%;margin-top:.2rem;padding:.35rem .5rem;border:1.5px solid var(--hm-line);border-radius:8px;background:var(--hm-surface-solid);color:var(--hm-text);font-family:inherit;font-size:12px;font-weight:700}
.hm-popup-body .hm-coord-warn{margin:.5rem 0 0;padding:.4rem .6rem;border-radius:10px;background:#fff1cf;border:1.5px solid #e8c26a;color:#7a4e00;font-size:12px;font-weight:700}

/* "MY POSITION" MARKER (view only) */
.hm-gps-marker{background:none;border:0}
.hm-gps-dot{display:block;width:20px;height:20px;border:4px solid #fff;border-radius:50%;background:#0d6efd;box-shadow:0 0 0 2px #0d6efd,0 2px 9px rgba(0,0,0,.45)}

/* MOBILE */
@media(max-width:991.98px){
    .hm-hero{padding:18px}
    .hm-shell{height:auto;min-height:0;display:flex;flex-direction:column;overflow:visible;border:0;box-shadow:none}
    .hm-map{position:relative;height:62vh;min-height:400px;border-radius:22px;overflow:hidden;border:1.5px solid var(--hm-line)}

    .hm-dock{position:static;width:auto;max-height:540px;margin-top:12px;backdrop-filter:none;background:var(--hm-surface-solid)}
    .hm-dock.is-hidden{display:none}
    .hm-dock.is-min{max-height:none}

    .hm-tools{flex-direction:row;flex-wrap:wrap;justify-content:flex-end;gap:.35rem;max-width:calc(100% - 28px)}
    .hm-tools button{width:38px;height:38px;border-radius:11px}

    .hm-status,
    .hm-shell.dock-open .hm-status{bottom:auto;top:auto;left:14px;transform:none;max-width:calc(100% - 100px);bottom:14px}

    .hm-strip{width:100%}
    .hm-strip>div{flex:1;min-width:0}
    .hm-hero-right{width:100%}

    /* full screen on small screens: map fills the screen, dock floats at the bottom */
    .hm-shell.is-full{display:block;overflow:hidden}
    .hm-shell.is-full .hm-map{position:absolute;inset:0;height:auto;min-height:0;border:0;border-radius:0}
    .hm-shell.is-full .hm-dock{position:absolute;left:10px;right:10px;bottom:10px;margin:0;max-height:55%}
    .hm-shell.is-full .hm-status{bottom:auto;top:14px}
}
@media(prefers-reduced-motion:reduce){.hm-dock,.hm-pin,.hm-search,.hm-chip,.hm-item,.hm-status,.hm-tools button{transition:none}}
@media print{.hm-dock,.hm-tools,.hm-status,.hm-hero-right{display:none!important}}
</style>

<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const $ = id => document.getElementById(id);
    if (!$('householdMap')) return;

    window.addEventListener('error', e => {
        if ($('mapStatus')) $('mapStatus').textContent = 'Map error: ' + e.message;
    });

    if (typeof L === 'undefined' || typeof L.markerClusterGroup !== 'function') {
        $('mapStatus').textContent = 'Map library failed to load.';
        $('householdMap').innerHTML =
            '<div class="hm-fail"><div><i class="bi bi-wifi-off fs-1"></i><div class="fw-semibold mt-2">The map could not load</div><div class="small">The map library comes from a CDN. Check your internet connection or firewall, then reload.</div></div></div>';
        return;
    }

    const raw = @json($mapData->values());
    const households = Array.isArray(raw) ? raw : [];

    const HOUSEHOLDS_URL = @json(route('households.index'));

    const BANI_CENTER = [16.187656924442535, 119.84779277322872];
    const BANI_ZOOM = 14;
    const HOUSEHOLD_ZOOM = 18;

    const AREA = L.latLngBounds([15.95, 119.6], [16.45, 120.1]);

    const POBLACION_BOUNDARY = [
        [16.19481, 119.83521],
        [16.19942, 119.84490],
        [16.19875, 119.85327],
        [16.20142, 119.85488],
        [16.19742, 119.85788],
        [16.19419, 119.86123],
        [16.19381, 119.87144],
        [16.19375, 119.87421],
        [16.19147, 119.87664],
        [16.19030, 119.87964],
        [16.18547, 119.87952],
        [16.18508, 119.87866],
        [16.18369, 119.87808],
        [16.18224, 119.87721],
        [16.18046, 119.87675],
        [16.17907, 119.87733],
        [16.17646, 119.87738],
        [16.17735, 119.86336],
        [16.18241, 119.83867],
        [16.18880, 119.83682]
    ];

    const BOUNDARY_LABEL_AT = [16.18975, 119.85315];
    const BOUNDARY_BOUNDS = L.latLngBounds(POBLACION_BOUNDARY);

    const PUROK_COLORS = {
        'Purok 1':'#198754',
        'Purok 2':'#0d6efd',
        'Purok 3':'#fd7e14',
        'Purok 4':'#6f42c1',
        'Purok 5':'#dc3545',
        'Purok 6':'#e0a800',
        'Purok 7':'#795548'
    };

    const FALLBACK = ['#20c997','#6610f2','#d63384','#6c757d','#0dcaf0','#343a40','#e83e8c','#17a2b8'];
    const generated = {};

    /* HELPERS */
    const pick = (o, keys, fb = null) => {
        for (const k of keys) {
            if (o && o[k] != null) return o[k];
        }
        return fb;
    };

    const txt = (v, fb = 'N/A') =>
        (v == null || String(v).trim() === '') ? fb : String(v).trim();

    const esc = v =>
        String(v ?? '').replace(/[&<>"']/g, c => ({
            '&':'&amp;',
            '<':'&lt;',
            '>':'&gt;',
            '"':'&quot;',
            "'":'&#39;'
        }[c]));

    const num = v => {
        if (v == null || String(v).trim() === '') return null;
        const n = Number(v);
        return Number.isFinite(n) ? n : null;
    };

    function inkFor(hex) {
        const m = /^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i.exec(String(hex));
        if (!m) return '#fff';

        const [r,g,b] = [m[1],m[2],m[3]].map(x => {
            const c = parseInt(x,16)/255;
            return c <= 0.03928 ? c/12.92 : Math.pow((c+0.055)/1.055,2.4);
        });

        const lum = 0.2126*r + 0.7152*g + 0.0722*b;
        return lum > 0.4 ? '#111' : '#fff';
    }

    function normPurok(v) {
        const t = txt(v, '');
        if (!t) return 'Purok Unspecified';

        const m = t.replace(/\s+/g, ' ').match(/^(?:purok[\s-]*)?(\d+)$/i);
        if (m) return 'Purok ' + m[1];

        return t.replace(/\S+/g, w => w[0].toUpperCase() + w.slice(1).toLowerCase());
    }

    const purokSort = (a,b) => {
        const x = String(a).match(/\d+/);
        const y = String(b).match(/\d+/);
        if (x && y) return x[0] - y[0];
        return x ? -1 : y ? 1 : String(a).localeCompare(String(b));
    };

    function colorFor(p) {
        if (PUROK_COLORS[p]) return PUROK_COLORS[p];
        if (!generated[p]) generated[p] = FALLBACK[Object.keys(generated).length % FALLBACK.length];
        return generated[p];
    }

    function headName(h) {
        const head = pick(h, ['household_head','householdHead','head','head_name']);

        if (typeof head === 'string' && head.trim()) return head;

        if (head && typeof head === 'object') {
            if (head.full_name) return head.full_name;
            if (head.name) return head.name;

            const n = [head.first_name,head.middle_name,head.last_name,head.suffix]
                .filter(Boolean).join(' ');

            if (n.trim()) return n;
        }

        return [h.first_name,h.middle_name,h.last_name].filter(Boolean).join(' ') || 'Household head';
    }

    function memberCount(h) {
        const c = pick(h, ['member_count','members_count','household_members_count']);
        if (c != null) return c;

        const m = pick(h, ['members','household_members']);
        return Array.isArray(m) ? m.length : 'N/A';
    }

    /* ============================================================
       SIDE DOCK STATE (minimize, hide)
       ============================================================ */
    const dock = $('hmDock');
    const shell = $('hmShell');
    const DOCK_KEY = 'hm-dock-state';

    let dockMin = false;
    let dockHidden = false;

    try {
        const saved = JSON.parse(localStorage.getItem(DOCK_KEY) || 'null');

        if (saved) {
            dockMin = !!saved.min;
            if (innerWidth >= 992) dockHidden = !!saved.hidden;
        }
    } catch (e) {}

    const dockCoversMap = () => innerWidth >= 992 && !dockHidden && !dockMin;

    function syncDock() {
        dock.classList.toggle('is-hidden',dockHidden);
        dock.classList.toggle('is-min',dockMin);
        shell.classList.toggle('dock-open',dockCoversMap());

        const minBtn = $('panelMin');
        minBtn.title = dockMin ? 'Expand panel' : 'Minimize panel';
        minBtn.setAttribute('aria-label',minBtn.title);
        minBtn.setAttribute('aria-expanded',String(!dockMin));
        minBtn.querySelector('i').className = dockMin ? 'bi bi-chevron-down' : 'bi bi-chevron-up';

        const toggle = $('panelToggle');
        toggle.classList.toggle('on',!dockHidden);
        toggle.setAttribute('aria-pressed',String(!dockHidden));
        toggle.title = dockHidden ? 'Show side panel' : 'Hide side panel';

        try {
            localStorage.setItem(DOCK_KEY,JSON.stringify({min:dockMin,hidden:dockHidden}));
        } catch (e) {}
    }

    $('panelMin').addEventListener('click',() => {
        dockMin = !dockMin;
        syncDock();
    });

    $('panelClose').addEventListener('click',() => {
        dockHidden = true;
        syncDock();
    });

    $('panelToggle').addEventListener('click',() => {
        dockHidden = !dockHidden;
        if (!dockHidden) dockMin = false;
        syncDock();
    });

    /* MAP INITIALIZATION */
    const map = L.map('householdMap', {
        center:BANI_CENTER,
        zoom:BANI_ZOOM,
        maxZoom:20,
        zoomControl:false,
        preferCanvas:true,
        wheelPxPerZoomLevel:90
    });

    L.control.zoom({position:'bottomright'}).addTo(map);
    L.control.scale({imperial:false,position:'bottomright'}).addTo(map);

    /* HIGH-RESOLUTION BASE MAPS
       detectRetina asks for sharper tiles on HiDPI screens;
       maxZoom is higher than the native tiles so you can keep zooming in. */
    const tileCommon = {
        detectRetina:true,
        keepBuffer:4,
        updateWhenZooming:false,
        crossOrigin:true
    };

    const street = L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        Object.assign({},tileCommon,{
            maxZoom:21,
            maxNativeZoom:19,
            minZoom:10,
            attribution:'&copy; OpenStreetMap contributors'
        })
    );

    const satellite = L.tileLayer(
        'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        Object.assign({},tileCommon,{
            maxZoom:21,
            maxNativeZoom:18,
            minZoom:10,
            attribution:'Tiles &copy; Esri &mdash; Maxar, Earthstar Geographics'
        })
    );

    /* Optional road and place names on top of the satellite imagery */
    const labelsLayer = L.layerGroup([
        L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}',
            Object.assign({},tileCommon,{maxZoom:21,maxNativeZoom:18,minZoom:10})
        ),
        L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}',
            Object.assign({},tileCommon,{maxZoom:21,maxNativeZoom:18,minZoom:10})
        )
    ]);

    satellite.addTo(map);
    $('householdMap').classList.add('is-satellite');

    let isSatellite = true;
    let labelsOn = false;

    function syncLabels() {
        const show = labelsOn && isSatellite;

        show ? labelsLayer.addTo(map) : map.removeLayer(labelsLayer);

        const btn = $('labelsToggle');
        btn.classList.toggle('on',labelsOn && isSatellite);
        btn.setAttribute('aria-pressed',String(labelsOn && isSatellite));
        btn.disabled = !isSatellite;
        btn.title = !isSatellite
            ? 'Street map already shows names'
            : (labelsOn ? 'Hide road and place names' : 'Show road and place names');
    }

    /* BOUNDARY */
    map.createPane('boundary');
    map.getPane('boundary').style.zIndex = 350;
    map.getPane('boundary').style.pointerEvents = 'none';

    const boundaryRenderer = L.svg({pane:'boundary'});

    const boundaryLayer = L.layerGroup([
        L.polygon(POBLACION_BOUNDARY,{
            renderer:boundaryRenderer,
            interactive:false,
            color:'#ffffff',
            weight:6,
            opacity:.95,
            fill:false,
            lineJoin:'round'
        }),
        L.polygon(POBLACION_BOUNDARY,{
            renderer:boundaryRenderer,
            interactive:false,
            color:'#d63e4c',
            weight:3,
            opacity:1,
            dashArray:'8 6',
            lineJoin:'round',
            fillColor:'#d63e4c',
            fillOpacity:.06
        }),
        L.marker(BOUNDARY_LABEL_AT,{
            pane:'boundary',
            interactive:false,
            keyboard:false,
            icon:L.divIcon({
                className:'hm-bd-wrap',
                html:'<span class="hm-bd-label">Poblacion</span>',
                iconSize:[0,0]
            })
        })
    ]).addTo(map);

    let boundaryOn = true;

    /* CLUSTER */
    const layer = L.markerClusterGroup({
        showCoverageOnHover:false,
        zoomToBoundsOnClick:true,
        spiderfyOnMaxZoom:true,
        disableClusteringAtZoom:18,
        maxClusterRadius:45,
        animateAddingMarkers:false,
        iconCreateFunction:clusterIcon
    }).addTo(map);

    function clusterIcon(c) {
        const tally = {};

        c.getAllChildMarkers().forEach(m => {
            const col = m.options.hmColor || '#6c757d';
            tally[col] = (tally[col] || 0) + 1;
        });

        const entries = Object.entries(tally).sort((a,b) => b[1]-a[1]);
        const n = c.getChildCount();

        if (entries.length === 1) {
            return L.divIcon({
                html:`<span style="--bg:${esc(entries[0][0])};--ct:${esc(inkFor(entries[0][0]))}">${n}</span>`,
                className:'hm-cluster',
                iconSize:[42,42]
            });
        }

        let acc = 0;

        const stops = entries.map(([col,k]) => {
            const from = (acc/n)*100;
            acc += k;
            return `${col} ${from.toFixed(1)}% ${((acc/n)*100).toFixed(1)}%`;
        }).join(',');

        return L.divIcon({
            html:`<span class="mixed" style="--ring:conic-gradient(${stops})"><b>${n}</b></span>`,
            className:'hm-cluster',
            iconSize:[42,42]
        });
    }

    /* COPY HELPERS (copies text to the clipboard only; changes no data) */
    async function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            try {
                await navigator.clipboard.writeText(text);
                return true;
            } catch (e) {}
        }

        try {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly','');
            ta.style.cssText = 'position:fixed;top:-1000px;left:-1000px;opacity:0';

            document.body.appendChild(ta);
            ta.select();
            ta.setSelectionRange(0,text.length);

            const ok = document.execCommand('copy');
            document.body.removeChild(ta);

            return !!ok;
        } catch (e) {
            return false;
        }
    }

    function wirePopup(popup,rec) {
        const el = popup && popup.getElement && popup.getElement();
        if (!el) return;

        const btn = el.querySelector('.hm-copy');
        if (!btn) return;

        const label = btn.querySelector('.hm-copy-label');
        const icon = btn.querySelector('i');
        const msg = el.querySelector('.hm-copy-msg');
        const manual = el.querySelector('.hm-copy-manual');
        const field = manual ? manual.querySelector('input') : null;

        let timer;

        btn.onclick = async function () {
            const ok = await copyText(rec.coordText);
            clearTimeout(timer);

            if (ok) {
                if (manual) manual.hidden = true;

                btn.classList.add('is-done');
                label.textContent = 'Copied!';
                icon.className = 'bi bi-check2';

                msg.classList.remove('is-err');
                msg.textContent = 'Copied: ' + rec.coordText;

                timer = setTimeout(() => {
                    btn.classList.remove('is-done');
                    label.textContent = 'Copy Coordinates';
                    icon.className = 'bi bi-clipboard';
                    msg.textContent = '';
                },2500);
            } else {
                msg.classList.add('is-err');
                msg.textContent = 'The browser blocked copying. Press Ctrl+C to copy the selected text.';

                if (manual && field) {
                    manual.hidden = false;
                    field.focus();
                    field.select();
                }
            }
        };
    }

    /* BUILD HOUSEHOLD RECORDS (saved coordinates only) */
    const records = [];
    const unmappedRecs = [];

    households.forEach(h => {
        const latRaw = pick(h,['latitude','lat']);
        const lngRaw = pick(h,['longitude','lng','lon']);

        const lat = num(latRaw);
        const lng = num(lngRaw);

        if (
            lat === null ||
            lng === null ||
            Math.abs(lat) > 90 ||
            Math.abs(lng) > 180
        ) {
            const p = normPurok(pick(h,['purok','purok_name']));
            const n = txt(pick(h,['household_number','household_no','household_code','code','name']),'Household');
            const hd = headName(h);
            const ad = txt(pick(h,['address','full_address','location']),'No address');
            const st = txt(pick(h,['status']));

            unmappedRecs.push({
                id:h.id,
                purok:p,
                number:n,
                head:hd,
                address:ad,
                status:st,
                color:colorFor(p),
                text:[n,hd,ad,p,st].join(' ').toLowerCase()
            });

            return;
        }

        const purok = normPurok(pick(h,['purok','purok_name']));
        const number = txt(pick(h,['household_number','household_no','household_code','code','name']),'Household');
        const head = headName(h);
        const address = txt(pick(h,['address','full_address','location']),'No address');
        const members = memberCount(h);
        const status = txt(pick(h,['status']));

        const color = colorFor(purok);
        const ink = inkFor(color);
        const isActive = status.toLowerCase() === 'active';

        const latTxt = String(latRaw).trim();
        const lngTxt = String(lngRaw).trim();
        const coordText = latTxt + ', ' + lngTxt;
        const inArea = AREA.contains([lat,lng]);

        const marker = L.marker([lat,lng],{
            title:number,
            hmColor:color,
            icon:L.divIcon({
                className:'hm-pin-wrap',
                html:`<span class="hm-pin" style="--c:${esc(color)}"></span>`,
                iconSize:[26,26],
                iconAnchor:[13,31],
                popupAnchor:[0,-30]
            })
        });

        marker.bindPopup(`
            <div class="hm-popup" style="--c:${esc(color)};--ct:${esc(ink)}">
                <div class="hm-popup-head">
                    <div class="t">${esc(number)}</div>
                    <div>${esc(head)}</div>
                </div>
                <div class="hm-popup-body">
                    <div><i class="bi bi-geo-alt"></i>${esc(address)}</div>
                    <div><i class="bi bi-pin-map"></i>${esc(purok)}</div>
                    <div><i class="bi bi-people"></i>${esc(members)} member(s)</div>
                    <div><i class="bi bi-circle-fill" style="color:${isActive ? '#2f8a3a' : '#8a7a7e'};font-size:.6rem"></i>${esc(status)}</div>

                    <div class="hm-coords">
                        <div class="hm-coord"><span>Latitude</span><b>${esc(latTxt)}</b></div>
                        <div class="hm-coord"><span>Longitude</span><b>${esc(lngTxt)}</b></div>
                        <button type="button" class="hm-copy">
                            <i class="bi bi-clipboard"></i>
                            <span class="hm-copy-label">Copy Coordinates</span>
                        </button>
                        <div class="hm-copy-msg" role="status" aria-live="polite"></div>
                        <div class="hm-copy-manual" hidden>
                            <input type="text" readonly value="${esc(coordText)}" aria-label="Coordinates">
                        </div>
                    </div>

                    ${inArea ? '' : '<div class="hm-coord-warn"><i class="bi bi-exclamation-triangle"></i> These coordinates are outside Bani. Please verify the saved location.</div>'}

                    <a class="hm-popup-link" target="_blank" rel="noopener" href="https://www.google.com/maps?q=${lat},${lng}">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Open in Google Maps
                    </a>
                </div>
            </div>
        `,{maxWidth:320,closeButton:false});

        const rec = {
            id:h.id,
            idx:records.length,
            marker,
            lat,
            lng,
            inArea,
            coordText,
            purok,
            number,
            head,
            address,
            members,
            status,
            isActive,
            color,
            ink,
            text:[number,head,address,purok,status].join(' ').toLowerCase()
        };

        marker.on('popupopen',e => {
            setActive(rec.idx);
            wirePopup(e.popup,rec);
        });

        marker.on('popupclose',() => setActive(null));

        records.push(rec);
    });

    const puroks = [...new Set([...records,...unmappedRecs].map(r => r.purok))].sort(purokSort);

    $('statMapped').textContent = records.length;
    $('statActive').textContent = records.filter(r => r.isActive).length;
    $('statPuroks').textContent = puroks.length;

    const unmapped = households.length - records.length;
    const outside = records.filter(r => !r.inArea).length;

    if (unmapped > 0 || outside > 0) {
        const n = $('unmappedNote');
        n.classList.remove('d-none');

        n.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>' +
            (unmapped > 0 ? `${unmapped} household(s) have no saved location. ` : '') +
            (outside > 0 ? `${outside} have coordinates outside Bani (likely a typo or swapped latitude/longitude). ` : '') +
            'Locations are set on each household\'s Edit page. ' +
            `<a href="${esc(HOUSEHOLDS_URL)}">Open household records</a>`;
    }

    /* PANEL + FILTERS */
    const search = $('householdSearch');
    const listEl = $('householdList');
    const chipsEl = $('purokChips');

    const activePuroks = new Set();

    let shown = [];
    let shownU = [];

    chipsEl.innerHTML = puroks.length
        ? puroks.map(p =>
            `<button type="button" class="hm-chip" data-p="${esc(p)}" style="--c:${esc(colorFor(p))}">
                <i></i>${esc(p)}
                <em>${[...records,...unmappedRecs].filter(r => r.purok === p).length}</em>
            </button>`
        ).join('')
        : '<span class="small hm-muted">No mapped Purok data yet.</span>';

    /* Keeps fitted areas clear of the dock */
    const leftPad = extra => dockCoversMap() ? dock.offsetWidth + extra : 40;

    function resetToBani() {
        map.fitBounds(BOUNDARY_BOUNDS,{
            paddingTopLeft:[leftPad(36),40],
            paddingBottomRight:[70,40],
            animate:true
        });
    }

    function fitTo(list) {
        const pts = list.filter(r => r.inArea);

        if (!pts.length) {
            resetToBani();
            return;
        }

        map.fitBounds(
            L.latLngBounds(pts.map(r => [r.lat,r.lng])),
            {
                paddingTopLeft:[dockCoversMap() ? dock.offsetWidth + 46 : 60,60],
                paddingBottomRight:[70,60],
                maxZoom:17
            }
        );
    }

    function focusRecord(rec) {
        if (!rec) return;

        if (innerWidth < 992) {
            $('householdMap').scrollIntoView({behavior:'smooth',block:'center'});
        }

        map.once('moveend',() => {
            layer.zoomToShowLayer(rec.marker,() => rec.marker.openPopup());
        });

        map.flyTo([rec.lat,rec.lng],HOUSEHOLD_ZOOM,{duration:.8});
    }

    function setActive(idx) {
        listEl.querySelectorAll('.hm-item').forEach(el => {
            const on = idx !== null && Number(el.dataset.i) === idx;
            el.classList.toggle('active',on);

            if (on) el.scrollIntoView({block:'nearest'});
        });
    }

    function renderList() {
        if (!shown.length && !shownU.length) {
            listEl.innerHTML = `
                <div class="hm-empty">
                    <i class="bi bi-search fs-2 d-block mb-2 opacity-50"></i>
                    <div class="fw-semibold">${(records.length + unmappedRecs.length) ? 'No households match' : 'No households yet'}</div>
                    <div class="small">${(records.length + unmappedRecs.length) ? 'Try a different search or clear the Purok filters.' : 'Add households to see them here.'}</div>
                </div>`;
            return;
        }

        const LIMIT = 150;

        listEl.innerHTML =
            shown.slice(0,LIMIT).map(r => `
                <button type="button" class="hm-item" data-i="${r.idx}" style="--c:${esc(r.color)};--ct:${esc(r.ink)}">
                    <span class="hm-av"><i class="bi bi-house-fill"></i></span>
                    <span class="min-w-0">
                        <span class="d-block hm-title text-truncate">${esc(r.number)} · ${esc(r.head)}</span>
                        <span class="d-block small hm-sub2 text-truncate">${esc(r.purok)} · ${esc(r.address)}</span>
                    </span>
                    <i class="bi bi-chevron-right small"></i>
                </button>
            `).join('') +

            (shown.length > LIMIT
                ? `<div class="small hm-muted text-center py-2">Showing the first ${LIMIT}. Narrow the search to see more.</div>`
                : '') +

            shownU.map(r => `
                <div class="hm-item is-off" style="--c:${esc(r.color)}">
                    <span class="hm-av"><i class="bi bi-geo-alt-fill"></i></span>
                    <span class="min-w-0">
                        <span class="d-block hm-title text-truncate">${esc(r.number)} · ${esc(r.head)}</span>
                        <span class="d-block small hm-sub2 text-truncate">${esc(r.purok)} · ${esc(r.address)}</span>
                    </span>
                    <span class="hm-badge" title="This household has no saved latitude/longitude">Location not set</span>
                </div>
            `).join('');
    }

    function apply(fit) {
        const q = search.value.trim().toLowerCase();

        const match = r =>
            (!activePuroks.size || activePuroks.has(r.purok)) &&
            (!q || r.text.includes(q));

        shown = records.filter(match);
        shownU = unmappedRecs.filter(match);

        layer.clearLayers();
        layer.addLayers(shown.map(r => r.marker));

        const total = records.length + unmappedRecs.length;
        const count = shown.length + shownU.length;

        $('visibleHouseholdBadge').textContent =
            count === total
                ? `${total} household${total === 1 ? '' : 's'}`
                : `${count} of ${total} households`;

        $('panelCount').textContent =
            count === total ? String(total) : `${count}/${total}`;

        $('clearSearch').classList.toggle('d-none',!q);
        $('clearFilters').classList.toggle('d-none',!q && !activePuroks.size);

        chipsEl.querySelectorAll('.hm-chip').forEach(c => {
            c.classList.toggle('on',activePuroks.has(c.dataset.p));
        });

        renderList();

        if (fit) {
            shown.length === 1 ? focusRecord(shown[0]) : fitTo(shown);
        }
    }

    /* SEARCH EVENTS */
    let debounce;

    search.addEventListener('input',() => {
        clearTimeout(debounce);
        debounce = setTimeout(() => apply(true),250);
    });

    $('clearSearch').addEventListener('click',() => {
        search.value = '';
        apply(true);
        search.focus();
    });

    $('clearFilters').addEventListener('click',() => {
        search.value = '';
        activePuroks.clear();
        apply(true);
    });

    chipsEl.addEventListener('click',e => {
        const chip = e.target.closest('.hm-chip');
        if (!chip) return;

        const p = chip.dataset.p;

        activePuroks.has(p)
            ? activePuroks.delete(p)
            : activePuroks.add(p);

        apply(true);
    });

    listEl.addEventListener('click',e => {
        const item = e.target.closest('.hm-item');

        if (!item || item.classList.contains('is-off')) return;

        focusRecord(records[Number(item.dataset.i)]);
    });

    /* SHOW MY POSITION (view only: a one-time browser location request that
       only centers the map. It never saves or changes any household data.) */
    let gpsMarker = null;
    let gpsAccuracyCircle = null;

    const statusEl = $('mapStatus');
    const baseStatus = () => records.length
        ? `${records.length} household location(s) across ${puroks.length} Purok(s). Dashed red line = Barangay Poblacion border.`
        : `${households.length} household(s) loaded, but none has a valid latitude/longitude yet. Showing the Poblacion border.`;

    function locateMe() {
        if (!navigator.geolocation) {
            statusEl.textContent = 'This browser does not support geolocation.';
            return;
        }

        const button = $('locateMeButton');
        button.disabled = true;

        statusEl.textContent = 'Requesting your current position. Please wait...';

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;

                if (
                    !Number.isFinite(lat) ||
                    !Number.isFinite(lng) ||
                    !Number.isFinite(accuracy) ||
                    accuracy < 0
                ) {
                    statusEl.textContent = 'The browser returned invalid location data.';
                    button.disabled = false;
                    return;
                }

                const gpsLatLng = L.latLng(lat,lng);

                if (!gpsMarker) {
                    gpsMarker = L.marker(gpsLatLng,{
                        keyboard:false,
                        zIndexOffset:1500,
                        icon:L.divIcon({
                            className:'hm-gps-marker',
                            html:'<span class="hm-gps-dot"></span>',
                            iconSize:[20,20],
                            iconAnchor:[10,10]
                        })
                    }).addTo(map);

                    gpsMarker.bindPopup('Your current position');
                } else {
                    gpsMarker.setLatLng(gpsLatLng);
                }

                if (!gpsAccuracyCircle) {
                    gpsAccuracyCircle = L.circle(gpsLatLng,{
                        radius:accuracy,
                        color:'#0d6efd',
                        weight:2,
                        fillColor:'#0d6efd',
                        fillOpacity:.12,
                        interactive:false
                    }).addTo(map);
                } else {
                    gpsAccuracyCircle.setLatLng(gpsLatLng);
                    gpsAccuracyCircle.setRadius(accuracy);
                }

                map.flyTo(gpsLatLng,Math.max(map.getZoom(),18),{duration:.8});
                gpsMarker.openPopup();

                let message = 'Your position was found. Estimated accuracy: ' + Math.round(accuracy) + ' meters.';

                if (accuracy > 100) {
                    message += ' Accuracy is weak.';
                } else if (accuracy > 50) {
                    message += ' Accuracy is moderate.';
                }

                statusEl.textContent = message;
                button.disabled = false;
            },
            function (error) {
                const messages = {
                    1:'Location permission denied. Allow location access in your browser settings.',
                    2:'Your location is unavailable. Check your device location services.',
                    3:'Location request timed out. Please try again.'
                };

                statusEl.textContent =
                    messages[error.code] || 'Unable to detect your current position.';

                button.disabled = false;
            },
            {
                enableHighAccuracy:true,
                timeout:20000,
                maximumAge:0
            }
        );
    }

    $('locateMeButton').addEventListener('click',locateMe);

    /* MAP TOOLS */
    $('resetMapButton').addEventListener('click',resetToBani);

    $('fitHouseholdsButton').addEventListener('click',() => {
        fitTo(shown.length ? shown : records);
    });

    $('boundaryToggle').addEventListener('click',function () {
        boundaryOn = !boundaryOn;

        boundaryOn
            ? boundaryLayer.addTo(map)
            : map.removeLayer(boundaryLayer);

        this.classList.toggle('on',boundaryOn);
        this.setAttribute('aria-pressed',String(boundaryOn));
        this.title = boundaryOn ? 'Hide Poblacion border' : 'Show Poblacion border';
    });

    $('layerToggle').addEventListener('click',function () {
        isSatellite = !isSatellite;

        isSatellite
            ? (map.removeLayer(street),satellite.addTo(map))
            : (map.removeLayer(satellite),street.addTo(map));

        $('householdMap').classList.toggle('is-satellite',isSatellite);
        $('householdMap').classList.toggle('is-street',!isSatellite);

        this.classList.toggle('on',isSatellite);
        this.title = isSatellite ? 'Switch to street map' : 'Switch to satellite';

        syncLabels();
    });

    $('labelsToggle').addEventListener('click',function () {
        labelsOn = !labelsOn;
        syncLabels();
    });

    /* FULL SCREEN MAP */
    function setFullscreen(on) {
        shell.classList.toggle('is-full',on);
        document.body.style.overflow = on ? 'hidden' : '';

        const btn = $('fullscreenButton');
        btn.classList.toggle('on',on);
        btn.setAttribute('aria-pressed',String(on));
        btn.title = on ? 'Exit full screen (Esc)' : 'Full screen map';
        btn.querySelector('i').className = on ? 'bi bi-fullscreen-exit' : 'bi bi-fullscreen';

        setTimeout(() => map.invalidateSize(),150);
    }

    $('fullscreenButton').addEventListener('click',() => {
        setFullscreen(!shell.classList.contains('is-full'));
    });

    document.addEventListener('keydown',e => {
        if (e.key === 'Escape' && shell.classList.contains('is-full')) {
            setFullscreen(false);
        }
    });

    /* INITIAL DISPLAY */
    syncDock();
    syncLabels();
    apply(false);

    statusEl.textContent = baseStatus();

    setTimeout(() => {
        map.invalidateSize();
        records.length ? fitTo(records) : resetToBani();
    },250);

    if (window.ResizeObserver) {
        new ResizeObserver(() => map.invalidateSize()).observe(shell);
    } else {
        addEventListener('resize',() => {
            setTimeout(() => map.invalidateSize(),150);
        });
    }

    addEventListener('resize',syncDock);
});
</script>

@endsection
