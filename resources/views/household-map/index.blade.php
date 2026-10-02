@extends('layouts.app')

@section('title', 'Household Map')

@section('content')

@php
    $mapData = collect($mapHouseholds ?? $households ?? []);
@endphp

<div class="container-fluid px-3 px-md-4 pb-4">

    {{-- HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('households.index') }}">Households</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Map</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-1"><i class="bi bi-map-fill me-2 text-primary"></i>Household Map</h2>
            <p class="mb-0 hm-sub">Where every household in Barangay Poblacion, Bani, Pangasinan lives, colored by Purok.</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="hm-strip">
                <div><b>{{ $mapData->count() }}</b><span>Households</span></div>
                <div><b id="statMapped">0</b><span>On the map</span></div>
                <div><b id="statActive">0</b><span>Active</span></div>
                <div><b id="statPuroks">0</b><span>Puroks</span></div>
            </div>
            <a href="{{ route('households.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-people-fill me-1"></i>Household records
            </a>
        </div>
    </div>

    {{-- MAP WORKSPACE --}}
    <div class="hm-shell" id="hmShell">

        <div id="householdMap" class="hm-map" aria-label="Household location map"></div>

        {{-- Floating panel: search + Purok filter/legend + results --}}
        <aside class="hm-panel" id="hmPanel">
            <div class="hm-search">
                <i class="bi bi-search"></i>
                <input type="search" id="householdSearch" placeholder="Search name, address, household no." autocomplete="off" aria-label="Search households">
                <button type="button" id="clearSearch" class="d-none" aria-label="Clear search"><i class="bi bi-x-lg"></i></button>
            </div>

            <div>
                <div class="small mb-2 hm-muted">Filter by Purok <span>(select one or more)</span></div>
                <div class="hm-chips" id="purokChips"><span class="small hm-muted">Loading Puroks…</span></div>
            </div>

            <div class="d-flex justify-content-between align-items-center small">
                <span class="fw-semibold" id="visibleHouseholdBadge">0 households</span>
                <button type="button" id="clearFilters" class="btn btn-link btn-sm p-0 d-none">Clear filters</button>
            </div>

            <div class="hm-list" id="householdList"></div>

            <div class="hm-note d-none" id="unmappedNote"></div>
        </aside>

        {{-- Map tools --}}
        <div class="hm-tools">
            <button type="button" id="panelToggle" class="d-none d-lg-grid" title="Show or hide the list" aria-label="Toggle household list"><i class="bi bi-layout-sidebar-inset"></i></button>
            <button type="button" id="fitHouseholdsButton" title="Fit all shown households" aria-label="Fit households"><i class="bi bi-arrows-angle-contract"></i></button>
            <button type="button" id="resetMapButton" title="Center on Poblacion, Bani" aria-label="Center on Poblacion"><i class="bi bi-crosshair"></i></button>
            <button type="button" id="layerToggle" title="Switch to satellite" aria-label="Switch base map"><i class="bi bi-layers"></i></button>
        </div>

        <div class="hm-status" id="mapStatus">Loading map…</div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
<style>
/* =====================================================================
   THEME TOKENS — every color below follows Bootstrap's light/dark theme
   (data-bs-theme), so text and numbers stay readable in BOTH modes.
   ===================================================================== */
.hm-shell,.hm-strip,.hm-sub{
    --hm-text:var(--bs-body-color);
    --hm-muted:var(--bs-secondary-color);
    --hm-surface:rgba(var(--bs-body-bg-rgb),.94);
    --hm-surface-solid:var(--bs-body-bg);
    --hm-soft:var(--bs-tertiary-bg);
    --hm-line:var(--bs-border-color);
}
.hm-sub,.hm-muted{color:var(--hm-muted,var(--bs-secondary-color))}

/* ---------- Header strip ---------- */
.hm-strip{display:flex;border:1px solid var(--hm-line);border-radius:16px;background:var(--hm-soft);overflow:hidden;color:var(--hm-text)}
.hm-strip>div{padding:.45rem 1rem;text-align:center;min-width:88px}
.hm-strip>div+div{border-left:1px solid var(--hm-line)}
.hm-strip b{display:block;font-size:1.25rem;line-height:1.2;color:var(--hm-text)}
.hm-strip span{font-size:.75rem;color:var(--hm-muted)}

/* ---------- Workspace ---------- */
.hm-shell{position:relative;height:calc(100vh - 250px);min-height:580px;border-radius:26px;overflow:hidden;border:1px solid var(--hm-line);color:var(--hm-text);box-shadow:0 18px 50px -30px rgba(15,23,42,.55)}
.hm-map{position:absolute;inset:0;z-index:0;background:#dfe5ea}
[data-bs-theme="dark"] .hm-map{background:#14181d}

/* Blend the base map into the theme: in dark mode the street tiles are
   softly inverted so the map doesn't glare next to the dark panels. */
[data-bs-theme="dark"] .hm-map.is-street .leaflet-tile-pane{filter:invert(1) hue-rotate(180deg) brightness(.92) contrast(.9) saturate(.8)}
[data-bs-theme="dark"] .hm-map.is-satellite .leaflet-tile-pane{filter:brightness(.88) contrast(1.05)}

.hm-panel{position:absolute;z-index:500;top:14px;left:14px;bottom:14px;width:360px;display:flex;flex-direction:column;gap:.9rem;padding:14px;border-radius:20px;
    color:var(--hm-text);background:var(--hm-surface);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border:1px solid var(--hm-line);
    box-shadow:0 14px 36px -16px rgba(0,0,0,.45);transition:transform .3s ease,opacity .3s ease}
.hm-panel.is-hidden{transform:translateX(-115%);opacity:0;pointer-events:none}
.hm-panel .btn-link{color:var(--bs-link-color)}

.hm-search{display:flex;align-items:center;gap:.5rem;padding:.55rem .8rem;border-radius:14px;background:var(--hm-soft);border:1px solid var(--hm-line);color:var(--hm-text)}
.hm-search:focus-within{border-color:var(--bs-primary);box-shadow:0 0 0 3px rgba(var(--bs-primary-rgb),.22)}
.hm-search input{flex:1;min-width:0;border:0;outline:0;background:transparent;color:var(--hm-text);font-size:.9rem}
.hm-search input::placeholder{color:var(--hm-muted);opacity:1}
.hm-search input::-webkit-search-cancel-button{display:none}
.hm-search i{color:var(--hm-muted)}
.hm-search button{border:0;background:transparent;color:var(--hm-muted);padding:0}
.hm-search button:hover{color:var(--hm-text)}

.hm-chips{display:flex;flex-wrap:wrap;gap:.4rem}
.hm-chip{display:inline-flex;align-items:center;gap:.4rem;padding:.25rem .65rem;border-radius:999px;border:1px solid var(--hm-line);background:transparent;color:var(--hm-text);font-size:.78rem;transition:background .15s,border-color .15s}
.hm-chip i{width:10px;height:10px;border-radius:50%;background:var(--c);box-shadow:0 0 0 1.5px rgba(127,127,127,.45)}
.hm-chip em{font-style:normal;color:var(--hm-muted)}
.hm-chip:hover{background:var(--hm-soft)}
.hm-chip.on{border-color:var(--c);background:color-mix(in srgb,var(--c) 22%,transparent);font-weight:600}
.hm-chip.on em{color:var(--hm-text)}

.hm-list{flex:1;min-height:0;overflow:auto;display:flex;flex-direction:column;gap:.35rem;margin:0 -6px;padding:0 6px;scrollbar-width:thin;scrollbar-color:rgba(127,127,127,.5) transparent}
.hm-item{display:flex;align-items:center;gap:.75rem;padding:.6rem .7rem;border-radius:14px;border:1px solid transparent;cursor:pointer;text-align:left;background:transparent;color:var(--hm-text);width:100%}
.hm-item .text-muted,.hm-item .small.text-muted{color:var(--hm-muted)!important}
.hm-item>i{color:var(--hm-muted)}
.hm-item:hover{background:var(--hm-soft)}
.hm-item.active{background:color-mix(in srgb,var(--c) 20%,transparent);border-color:var(--c)}
.hm-av{flex:none;width:36px;height:36px;border-radius:12px;display:grid;place-items:center;background:var(--c);color:var(--ct,#fff);font-size:.95rem;box-shadow:0 0 0 1px rgba(127,127,127,.3)}
.hm-item .min-w-0{min-width:0;flex:1}
.hm-empty{margin:auto;text-align:center;padding:2rem 1rem;color:var(--hm-muted)}
.hm-note{font-size:.78rem;padding:.55rem .75rem;border-radius:12px;background:rgba(var(--bs-warning-rgb),.18);border:1px solid rgba(var(--bs-warning-rgb),.4);color:var(--hm-text)}
.hm-note a{color:var(--bs-link-color);font-weight:600}

.hm-item.is-off{cursor:default;opacity:.85}
.hm-item.is-off:hover{background:transparent}
.hm-item.is-off .hm-av{background:var(--bs-secondary-bg);color:var(--hm-text)}
.hm-fail{display:grid;place-items:center;height:100%;padding:2rem;text-align:center;color:var(--hm-muted)}

/* ---------- Tools ---------- */
.hm-tools{position:absolute;z-index:500;top:14px;right:14px;display:flex;flex-direction:column;gap:.5rem}
.hm-tools button{width:42px;height:42px;display:grid;place-items:center;border-radius:14px;border:1px solid var(--hm-line);background:var(--hm-surface);color:var(--hm-text);box-shadow:0 8px 22px -10px rgba(0,0,0,.5)}
.hm-tools button:hover{background:var(--hm-soft)}
.hm-tools button.on{background:var(--bs-primary);color:#fff;border-color:var(--bs-primary)}
.hm-status{position:absolute;z-index:500;left:50%;bottom:14px;transform:translateX(-50%);max-width:calc(100% - 28px);padding:.35rem .9rem;border-radius:999px;font-size:.78rem;
    background:rgba(12,18,32,.82);color:#fff;border:1px solid rgba(255,255,255,.18);backdrop-filter:blur(8px);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;pointer-events:none}

/* ---------- Leaflet controls follow the theme ---------- */
.hm-map .leaflet-bar{border:1px solid var(--hm-line);box-shadow:0 8px 22px -10px rgba(0,0,0,.5);border-radius:12px;overflow:hidden}
.hm-map .leaflet-bar a,.hm-map .leaflet-bar a:hover{background-color:var(--hm-surface-solid);color:var(--hm-text);border-bottom-color:var(--hm-line)}
.hm-map .leaflet-bar a:hover{background-color:var(--hm-soft)}
.hm-map .leaflet-bar a.leaflet-disabled{color:var(--hm-muted);opacity:.6}
.hm-map .leaflet-control-scale-line{background:var(--hm-surface);color:var(--hm-text);border-color:var(--hm-text);text-shadow:none}
.hm-map .leaflet-control-attribution{background:var(--hm-surface);color:var(--hm-muted);border-radius:8px 0 0 0}
.hm-map .leaflet-control-attribution a{color:var(--bs-link-color)}

/* ---------- Markers ---------- */
.hm-pin-wrap,.hm-cluster{background:none;border:0}
.hm-pin{position:relative;display:block;width:26px;height:26px;background:var(--c);border:2px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 0 0 1px rgba(0,0,0,.35),0 3px 8px rgba(0,0,0,.5);transition:transform .15s}
.hm-pin::after{content:"";position:absolute;inset:6px;border-radius:50%;background:#fff}
.hm-pin-wrap:hover .hm-pin{transform:rotate(-45deg) scale(1.15)}
.hm-cluster span{display:grid;place-items:center;width:42px;height:42px;border-radius:50%;background:#0f2f45;color:#fff;font-weight:700;font-size:.9rem;border:3px solid rgba(255,255,255,.92);box-shadow:0 0 0 1px rgba(0,0,0,.3),0 6px 16px rgba(0,0,0,.4)}

/* ---------- Popup (theme aware) ---------- */
.hm-map .leaflet-popup-content-wrapper,.hm-map .leaflet-popup-tip{background:var(--hm-surface-solid);color:var(--hm-text)}
.hm-map .leaflet-popup-content-wrapper{border-radius:18px;padding:0;overflow:hidden;border:1px solid var(--hm-line)}
.hm-map .leaflet-popup-content{margin:0;width:auto!important;color:var(--hm-text)}
.hm-popup{min-width:250px;font-size:13px;line-height:1.5;color:var(--hm-text)}
.hm-popup-head{padding:.8rem 1rem;color:var(--ct,#fff);background:var(--c)}
.hm-popup-head .t{font-weight:700;font-size:15px}
.hm-popup-body{padding:.75rem 1rem;color:var(--hm-text)}
.hm-popup-body div{margin-bottom:.35rem}
.hm-popup-body i{width:18px;display:inline-block;color:var(--hm-muted)}
.hm-map .leaflet-container a.hm-popup-link,.hm-map a.hm-popup-link{display:block;text-align:center;padding:.45rem;border-radius:10px;background:var(--hm-soft);color:var(--hm-text);border:1px solid var(--hm-line);font-weight:600;text-decoration:none;margin-top:.5rem}
.hm-map a.hm-popup-link:hover{border-color:var(--bs-primary);color:var(--bs-primary)}
.hm-map a.hm-popup-link i{color:inherit}

/* ---------- Mobile: map on top, list underneath ---------- */
@media (max-width:991.98px){
    .hm-shell{height:auto;min-height:0;display:flex;flex-direction:column;overflow:visible;border:0;box-shadow:none}
    .hm-map{position:relative;height:56vh;min-height:380px;border-radius:22px;overflow:hidden;border:1px solid var(--hm-line)}
    .hm-panel{position:static;width:auto;max-height:440px;margin-top:12px;backdrop-filter:none;background:var(--hm-surface-solid)}
    .hm-tools{top:14px}
    .hm-status{bottom:auto;top:14px;left:14px;transform:none;max-width:calc(100% - 80px)}
    .hm-strip{width:100%}.hm-strip>div{flex:1;min-width:0}
}
@media (prefers-reduced-motion:reduce){.hm-panel,.hm-pin{transition:none}}
</style>

<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    const $ = id => document.getElementById(id);
    if (!$('householdMap')) return;

    window.addEventListener('error', e => { $('mapStatus').textContent = 'Map error: ' + e.message; });
    if (typeof L === 'undefined' || typeof L.markerClusterGroup !== 'function') {
        $('mapStatus').textContent = 'Map library failed to load.';
        $('householdMap').innerHTML = '<div class="hm-fail"><div><i class="bi bi-wifi-off fs-1"></i><div class="fw-semibold mt-2">The map could not load</div><div class="small">The map library comes from a CDN. Check your internet connection or firewall, then reload.</div></div></div>';
        return;
    }

    const raw = @json($mapData->values());
    const households = Array.isArray(raw) ? raw : [];
    console.info('Household map: received', households.length, 'households. First record:', households[0]);
    const HOUSEHOLDS_URL = @json(route('households.index'));

    const BANI_CENTER = [16.187656924442535, 119.84779277322872];
    const BANI_ZOOM = 14, HOUSEHOLD_ZOOM = 18;
    // Loose box around Bani. Coordinates outside it (e.g. 0,0 or swapped lat/lng) never drive the map view.
    const AREA = L.latLngBounds([15.95, 119.6], [16.45, 120.1]);

    const PUROK_COLORS = {'Purok 1':'#198754','Purok 2':'#0d6efd','Purok 3':'#fd7e14','Purok 4':'#6f42c1','Purok 5':'#dc3545','Purok 6':'#e0a800','Purok 7':'#795548'};
    const FALLBACK = ['#20c997','#6610f2','#d63384','#6c757d','#0dcaf0','#343a40','#e83e8c','#17a2b8'];
    const generated = {};

    /* ---------- Helpers ---------- */
    const pick = (o, keys, fb = null) => { for (const k of keys) if (o && o[k] != null) return o[k]; return fb; };
    const txt = (v, fb = 'N/A') => (v == null || String(v).trim() === '') ? fb : String(v).trim();
    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const num = v => { if (v == null || String(v).trim() === '') return null; const n = Number(v); return Number.isFinite(n) ? n : null; };

    // Readable text color (white or near-black) for any Purok color, so labels on yellow/cyan etc. never wash out.
    function inkFor(hex) {
        const m = /^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i.exec(String(hex));
        if (!m) return '#fff';
        const [r, g, b] = [m[1], m[2], m[3]].map(x => { const c = parseInt(x, 16) / 255; return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4); });
        const lum = 0.2126 * r + 0.7152 * g + 0.0722 * b;
        return lum > 0.4 ? '#111' : '#fff';
    }

    function normPurok(v) {
        const t = txt(v, '');
        if (!t) return 'Purok Unspecified';
        const m = t.replace(/\s+/g, ' ').match(/^(?:purok[\s-]*)?(\d+)$/i);
        if (m) return 'Purok ' + m[1];
        return t.replace(/\S+/g, w => w[0].toUpperCase() + w.slice(1).toLowerCase());
    }
    const purokSort = (a, b) => {
        const x = String(a).match(/\d+/), y = String(b).match(/\d+/);
        if (x && y) return x[0] - y[0];
        return x ? -1 : y ? 1 : String(a).localeCompare(String(b));
    };
    function colorFor(p) {
        if (PUROK_COLORS[p]) return PUROK_COLORS[p];
        if (!generated[p]) generated[p] = FALLBACK[Object.keys(generated).length % FALLBACK.length];
        return generated[p];
    }
    function headName(h) {
        const head = pick(h, ['household_head', 'householdHead', 'head', 'head_name']);
        if (typeof head === 'string' && head.trim()) return head;
        if (head && typeof head === 'object') {
            if (head.full_name) return head.full_name;
            if (head.name) return head.name;
            const n = [head.first_name, head.middle_name, head.last_name, head.suffix].filter(Boolean).join(' ');
            if (n.trim()) return n;
        }
        return [h.first_name, h.middle_name, h.last_name].filter(Boolean).join(' ') || 'Household head';
    }
    function memberCount(h) {
        const c = pick(h, ['member_count', 'members_count', 'household_members_count']);
        if (c != null) return c;
        const m = pick(h, ['members', 'household_members']);
        return Array.isArray(m) ? m.length : 'N/A';
    }

    /* ---------- Map + base layers ---------- */
    const map = L.map('householdMap', { center: BANI_CENTER, zoom: BANI_ZOOM, zoomControl: false, preferCanvas: true });
    L.control.zoom({ position: 'bottomright' }).addTo(map);
    L.control.scale({ imperial: false, position: 'bottomright' }).addTo(map);

    const street = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, minZoom: 10, attribution: '&copy; OpenStreetMap contributors' });
    const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19, maxNativeZoom: 18, minZoom: 10, attribution: 'Tiles &copy; Esri' });
    street.addTo(map);
    $('householdMap').classList.add('is-street');
    let isSatellite = false;

    const layer = L.markerClusterGroup({
        showCoverageOnHover: false, zoomToBoundsOnClick: true, spiderfyOnMaxZoom: true,
        disableClusteringAtZoom: 18, maxClusterRadius: 45, animateAddingMarkers: false,
        iconCreateFunction: c => L.divIcon({ html: `<span>${c.getChildCount()}</span>`, className: 'hm-cluster', iconSize: [42, 42] })
    }).addTo(map);

    /* ---------- Build records (valid coordinates only) ---------- */
    const records = [];
    const unmappedRecs = [];
    households.forEach(h => {
        const lat = num(pick(h, ['latitude', 'lat'])), lng = num(pick(h, ['longitude', 'lng', 'lon']));
        if (lat === null || lng === null || Math.abs(lat) > 90 || Math.abs(lng) > 180) {
            const p = normPurok(pick(h, ['purok', 'purok_name'])), n = txt(pick(h, ['household_number', 'household_no', 'household_code', 'code', 'name']), 'Household'),
                  hd = headName(h), ad = txt(pick(h, ['address', 'full_address', 'location']), 'No address'), st = txt(pick(h, ['status']));
            unmappedRecs.push({ purok: p, number: n, head: hd, address: ad, status: st, color: colorFor(p), text: [n, hd, ad, p, st].join(' ').toLowerCase() });
            return;
        }

        const purok = normPurok(pick(h, ['purok', 'purok_name']));
        const number = txt(pick(h, ['household_number', 'household_no', 'household_code', 'code', 'name']), 'Household');
        const head = headName(h), address = txt(pick(h, ['address', 'full_address', 'location']), 'No address');
        const members = memberCount(h), status = txt(pick(h, ['status']));
        const color = colorFor(purok), ink = inkFor(color);
        const isActive = status.toLowerCase() === 'active';

        const marker = L.marker([lat, lng], {
            title: number,
            icon: L.divIcon({ className: 'hm-pin-wrap', html: `<span class="hm-pin" style="--c:${esc(color)}"></span>`, iconSize: [26, 26], iconAnchor: [13, 31], popupAnchor: [0, -30] })
        });
        marker.bindPopup(`
            <div class="hm-popup" style="--c:${esc(color)};--ct:${esc(ink)}">
                <div class="hm-popup-head"><div class="t">${esc(number)}</div><div>${esc(head)}</div></div>
                <div class="hm-popup-body">
                    <div><i class="bi bi-geo-alt"></i>${esc(address)}</div>
                    <div><i class="bi bi-pin-map"></i>${esc(purok)}</div>
                    <div><i class="bi bi-people"></i>${esc(members)} member(s)</div>
                    <div><i class="bi bi-circle-fill" style="color:${isActive ? '#198754' : '#6c757d'};font-size:.6rem"></i>${esc(status)}</div>
                    <a class="hm-popup-link" target="_blank" rel="noopener" href="https://www.google.com/maps?q=${lat},${lng}"><i class="bi bi-box-arrow-up-right me-1"></i>Open in Google Maps</a>
                </div>
            </div>`, { maxWidth: 320, closeButton: false });

        const rec = { idx: records.length, marker, lat, lng, inArea: AREA.contains([lat, lng]), purok, number, head, address, members, status, isActive, color, ink,
                      text: [number, head, address, purok, status].join(' ').toLowerCase() };
        marker.on('popupopen', () => setActive(rec.idx));
        marker.on('popupclose', () => setActive(null));
        records.push(rec);
    });

    const puroks = [...new Set([...records, ...unmappedRecs].map(r => r.purok))].sort(purokSort);
    $('statMapped').textContent = records.length;
    $('statActive').textContent = records.filter(r => r.isActive).length;
    $('statPuroks').textContent = puroks.length;

    const unmapped = households.length - records.length;
    const outside = records.filter(r => !r.inArea).length;
    if (unmapped > 0 || outside > 0) {
        const n = $('unmappedNote');
        n.classList.remove('d-none');
        n.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i>`
            + (unmapped > 0 ? `${unmapped} household(s) have no coordinates. ` : '')
            + (outside > 0 ? `${outside} have coordinates outside Bani (likely a typo or swapped latitude/longitude). ` : '')
            + `<a href="${esc(HOUSEHOLDS_URL)}">Update records</a>`;
    }

    /* ---------- Filters + list ---------- */
    const search = $('householdSearch'), listEl = $('householdList'), chipsEl = $('purokChips');
    const activePuroks = new Set();
    let shown = [], shownU = [], panelHidden = false;

    chipsEl.innerHTML = puroks.length
        ? puroks.map(p => `<button type="button" class="hm-chip" data-p="${esc(p)}" style="--c:${esc(colorFor(p))}"><i></i>${esc(p)}<em>${[...records, ...unmappedRecs].filter(r => r.purok === p).length}</em></button>`).join('')
        : '<span class="small hm-muted">No mapped Purok data yet.</span>';

    function fitTo(list) {
        const pts = list.filter(r => r.inArea);
        if (!pts.length) { resetToBani(); return; }
        const left = (innerWidth >= 992 && !panelHidden) ? 400 : 60;
        map.fitBounds(L.latLngBounds(pts.map(r => [r.lat, r.lng])), { paddingTopLeft: [left, 60], paddingBottomRight: [70, 60], maxZoom: 17 });
    }

    function focusRecord(rec) {
        if (innerWidth < 992) $('householdMap').scrollIntoView({ behavior: 'smooth', block: 'center' });
        map.once('moveend', () => layer.zoomToShowLayer(rec.marker, () => rec.marker.openPopup()));
        map.flyTo([rec.lat, rec.lng], HOUSEHOLD_ZOOM, { duration: .8 });
    }

    function setActive(idx) {
        listEl.querySelectorAll('.hm-item').forEach(el => {
            const on = idx !== null && Number(el.dataset.i) === idx;
            el.classList.toggle('active', on);
            if (on) el.scrollIntoView({ block: 'nearest' });
        });
    }

    function renderList() {
        if (!shown.length && !shownU.length) {
            listEl.innerHTML = `<div class="hm-empty"><i class="bi bi-search fs-2 d-block mb-2 opacity-50"></i>
                <div class="fw-semibold" style="color:var(--bs-body-color)">${(records.length + unmappedRecs.length) ? 'No households match' : 'No households yet'}</div>
                <div class="small">${(records.length + unmappedRecs.length) ? 'Try a different search or clear the Purok filters.' : 'Add households to see them here.'}</div></div>`;
            return;
        }
        const LIMIT = 150;
        listEl.innerHTML = shown.slice(0, LIMIT).map(r => `
            <button type="button" class="hm-item" data-i="${r.idx}" style="--c:${esc(r.color)};--ct:${esc(r.ink)}">
                <span class="hm-av"><i class="bi bi-house-fill"></i></span>
                <span class="min-w-0">
                    <span class="d-block fw-semibold text-truncate">${esc(r.number)} · ${esc(r.head)}</span>
                    <span class="d-block small text-muted text-truncate">${esc(r.purok)} · ${esc(r.address)}</span>
                </span>
                <i class="bi bi-chevron-right small"></i>
            </button>`).join('')
            + (shown.length > LIMIT ? `<div class="small hm-muted text-center py-2">Showing the first ${LIMIT}. Narrow the search to see more.</div>` : '')
            + shownU.map(r => `
            <div class="hm-item is-off" style="--c:${esc(r.color)}">
                <span class="hm-av"><i class="bi bi-geo-alt-fill"></i></span>
                <span class="min-w-0">
                    <span class="d-block fw-semibold text-truncate">${esc(r.number)} · ${esc(r.head)}</span>
                    <span class="d-block small text-muted text-truncate">${esc(r.purok)} · ${esc(r.address)}</span>
                </span>
                <span class="badge text-bg-warning">No coordinates</span>
            </div>`).join('');
    }

    function apply(fit) {
        const q = search.value.trim().toLowerCase();
        const match = r => (!activePuroks.size || activePuroks.has(r.purok)) && (!q || r.text.includes(q));
        shown = records.filter(match); shownU = unmappedRecs.filter(match);

        layer.clearLayers();
        layer.addLayers(shown.map(r => r.marker));

        const total = records.length + unmappedRecs.length, count = shown.length + shownU.length;
        $('visibleHouseholdBadge').textContent = count === total ? `${total} household${total === 1 ? '' : 's'}` : `${count} of ${total} households`;
        $('clearSearch').classList.toggle('d-none', !q);
        $('clearFilters').classList.toggle('d-none', !q && !activePuroks.size);
        chipsEl.querySelectorAll('.hm-chip').forEach(c => c.classList.toggle('on', activePuroks.has(c.dataset.p)));
        renderList();

        if (fit) shown.length === 1 ? focusRecord(shown[0]) : fitTo(shown);
    }

    let debounce;
    search.addEventListener('input', () => { clearTimeout(debounce); debounce = setTimeout(() => apply(true), 250); });
    $('clearSearch').addEventListener('click', () => { search.value = ''; apply(true); search.focus(); });
    $('clearFilters').addEventListener('click', () => { search.value = ''; activePuroks.clear(); apply(true); });
    chipsEl.addEventListener('click', e => {
        const chip = e.target.closest('.hm-chip'); if (!chip) return;
        const p = chip.dataset.p;
        activePuroks.has(p) ? activePuroks.delete(p) : activePuroks.add(p);
        apply(true);
    });
    listEl.addEventListener('click', e => {
        const item = e.target.closest('.hm-item'); if (!item || item.classList.contains('is-off')) return;
        focusRecord(records[Number(item.dataset.i)]);
    });

    /* ---------- Tools ---------- */
    function resetToBani() { map.setView(BANI_CENTER, BANI_ZOOM, { animate: true }); }
    $('resetMapButton').addEventListener('click', resetToBani);
    $('fitHouseholdsButton').addEventListener('click', () => fitTo(shown.length ? shown : records));

    $('layerToggle').addEventListener('click', function () {
        isSatellite = !isSatellite;
        isSatellite ? (map.removeLayer(street), satellite.addTo(map)) : (map.removeLayer(satellite), street.addTo(map));
        $('householdMap').classList.toggle('is-satellite', isSatellite);
        $('householdMap').classList.toggle('is-street', !isSatellite);
        this.classList.toggle('on', isSatellite);
        this.title = isSatellite ? 'Switch to street map' : 'Switch to satellite';
    });

    $('panelToggle').addEventListener('click', function () {
        panelHidden = !panelHidden;
        $('hmPanel').classList.toggle('is-hidden', panelHidden);
        this.classList.toggle('on', panelHidden);
    });

    /* ---------- Go ---------- */
    apply(false);
    $('mapStatus').textContent = records.length
        ? `${records.length} household location(s) across ${puroks.length} Purok(s). Click a pin or a list item for details.`
        : `${households.length} household(s) loaded, but none has a valid latitude/longitude yet. Showing Poblacion, Bani.`;

    setTimeout(() => { map.invalidateSize(); records.length ? fitTo(records) : resetToBani(); }, 250);
    if (window.ResizeObserver) new ResizeObserver(() => map.invalidateSize()).observe($('hmShell'));
    else addEventListener('resize', () => setTimeout(() => map.invalidateSize(), 150));
});
</script>

@endsection
