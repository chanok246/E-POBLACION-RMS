@php
    $user = auth()->user();
    $displayName = $user?->nickname ?: ($user?->name ?? 'User');
    $avatarUrl = $user?->avatar ? asset('storage/' . $user->avatar) : null;
    $savedTheme = $user?->theme ?? 'light';
    $initialTheme = in_array($savedTheme, ['light', 'dark'], true) ? $savedTheme : 'light';

    $isCaptain = $user?->isCaptain() ?? false;
    $isSecretary = $user?->isSecretary() ?? false;

    $canManageRecords = $isCaptain || $isSecretary;
    $canAccessReliefVouchers = $isCaptain || $isSecretary;
    $canAccessUserManagement = $isCaptain;
    $canAccessSettings = $isCaptain || $isSecretary;

    /* Logo: uses icon@2x.png automatically if you add one (sharper on retina screens) */
    $logo = asset('icon.png');
    $logo2x = file_exists(public_path('icon@2x.png')) ? asset('icon@2x.png') : null;

    /* Sidebar links live here. 'icon' = name of an inline SVG in the sprite below (works offline) */
    $navGroups = [
        ['label' => 'Main', 'show' => true, 'items' => [
            ['route' => 'dashboard', 'icon' => 'grid', 'text' => 'Dashboard', 'active' => ['dashboard']],
        ]],
        ['label' => 'Management', 'show' => $canManageRecords, 'items' => [
            ['route' => 'residents.index', 'icon' => 'people', 'text' => 'Residents', 'active' => ['residents.*']],
            ['route' => 'households.index', 'icon' => 'house', 'text' => 'Households', 'active' => ['households.index', 'households.create', 'households.show', 'households.edit']],
            ['route' => 'households.map', 'icon' => 'pin', 'text' => 'Household Map', 'active' => ['households.map']],
            ['route' => 'documents.index', 'icon' => 'file', 'text' => 'Barangay Documents', 'active' => ['documents.*']],
            ['route' => 'reports-complaints.index', 'icon' => 'shield', 'text' => 'Reports & Complaints', 'active' => ['reports-complaints.*']],
            ['route' => 'emergency-hotlines.index', 'icon' => 'phone', 'text' => 'Emergency Hotline', 'active' => ['emergency-hotlines.*']],
        ]],
        ['label' => 'Services', 'show' => $canAccessReliefVouchers || $canManageRecords, 'items' => array_values(array_filter([
            $canAccessReliefVouchers ? ['route' => 'relief-vouchers.index', 'icon' => 'qr', 'text' => 'Relief Vouchers', 'active' => ['relief-vouchers.*', 'relief-assistance.*']] : null,
            $canManageRecords ? ['route' => 'reports-analytics.index', 'icon' => 'chart', 'text' => 'Reports & Analytics', 'active' => ['reports-analytics.*']] : null,
        ]))],
        ['label' => 'System', 'show' => $canAccessUserManagement || $canManageRecords, 'items' => array_values(array_filter([
            $canAccessUserManagement ? ['route' => 'users.index', 'icon' => 'usergear', 'text' => 'User Management', 'active' => ['users.*']] : null,
            $canManageRecords ? ['route' => 'attendance.index', 'icon' => 'clock', 'text' => 'Attendance Monitoring', 'active' => ['attendance.*']] : null,
        ]))],
    ];

    /* ================================================================
       TOP BAR SEARCH DATA
       Pages + quick actions are searched instantly in the browser (works offline).
       Records (residents, households, documents...) come from the global-search route.
       ================================================================ */
    $routeHas = fn ($name) => \Illuminate\Support\Facades\Route::has($name);

    $searchKeywords = [
        'dashboard' => 'home overview main summary',
        'residents.index' => 'resident people population member citizen',
        'households.index' => 'household family home house',
        'households.map' => 'map location gps geo',
        'attendance.index' => 'attendance monitoring time in time out personnel records daily weekly monthly report qr kiosk',
        'documents.index' => 'document certificate clearance indigency residency permit request barangay id',
        'reports-complaints.index' => 'report complaint blotter case incident hearing',
        'emergency-hotlines.index' => 'emergency hotline contact number phone police fire ambulance',
        'relief-vouchers.index' => 'relief voucher assistance ayuda qr aid',
        'reports-analytics.index' => 'analytics statistics chart data insights',
        'users.index' => 'user account secretary captain staff access',
    ];

    $searchPages = [];
    foreach ($navGroups as $group) {
        if (!$group['show']) { continue; }
        foreach ($group['items'] as $item) {
            if (!$routeHas($item['route'])) { continue; }
            $searchPages[] = [
                'group' => 'Pages', 'icon' => $item['icon'], 'title' => $item['text'], 'sub' => $group['label'],
                'url' => route($item['route']), 'kw' => $searchKeywords[$item['route']] ?? '',
            ];
        }
    }
    if ($canAccessSettings && $routeHas('settings.index')) {
        $searchPages[] = ['group' => 'Pages', 'icon' => 'usergear', 'title' => 'Personal Settings', 'sub' => 'Account', 'url' => route('settings.index'), 'kw' => 'profile avatar nickname password account preferences 2fa two factor'];
    }
    if ($canManageRecords && $routeHas('reminders.index')) {
        $searchPages[] = ['group' => 'Pages', 'icon' => 'cal', 'title' => 'Schedule & Reminders', 'sub' => 'Services', 'url' => route('reminders.index'), 'kw' => 'schedule reminder calendar appointment event'];
    }

    $searchActions = [];
    if ($canManageRecords) {
        $actionDefs = [
            ['residents.create', 'people', 'Add resident', 'new register resident'],
            ['households.create', 'house', 'Add household', 'new register household family'],
            ['documents.create', 'file', 'New document request', 'create issue certificate clearance indigency'],
            ['reports-complaints.create', 'shield', 'File a report or complaint', 'new blotter incident case'],
            ['emergency-hotlines.create', 'phone', 'Add emergency hotline', 'new contact number'],
        ];
        foreach ($actionDefs as [$r, $ic, $t, $kw]) {
            if ($routeHas($r)) {
                $searchActions[] = ['group' => 'Quick actions', 'icon' => $ic, 'title' => $t, 'sub' => 'Create', 'url' => route($r), 'kw' => $kw];
            }
        }
    }
    if ($canAccessUserManagement && $routeHas('users.create')) {
        $searchActions[] = ['group' => 'Quick actions', 'icon' => 'usergear', 'title' => 'Add a Secretary account', 'sub' => 'Create', 'url' => route('users.create'), 'kw' => 'new user account staff'];
    }

    $searchUrl = ($canManageRecords && $routeHas('global-search')) ? route('global-search') : null;

    /* ================================================================
       NOTIFICATIONS + MANUAL REMINDERS (same data as before)
       ================================================================ */
    $notifications = $notifications ?? [];
    $reminderToday = collect();
    $reminderTomorrow = collect();

    if ($user) {
        $reminderToday = \App\Models\Reminder::query()->where('status', 'scheduled')
            ->whereBetween('scheduled_at', [\Carbon\Carbon::today()->startOfDay(), \Carbon\Carbon::today()->endOfDay()])
            ->orderBy('scheduled_at')->get();
        $reminderTomorrow = \App\Models\Reminder::query()->where('status', 'scheduled')
            ->whereBetween('scheduled_at', [\Carbon\Carbon::tomorrow()->startOfDay(), \Carbon\Carbon::tomorrow()->endOfDay()])
            ->orderBy('scheduled_at')->get();
    }

    // Stable keys let each user clear a reminder without changing its source record.
    $reminderToday = $reminderToday->map(fn ($r) => [
        'record' => $r,
        'key' => 'bell:reminder:' . $r->id . ':' . $r->scheduled_at->timestamp,
    ]);
    $reminderTomorrow = $reminderTomorrow->map(fn ($r) => [
        'record' => $r,
        'key' => 'bell:reminder:' . $r->id . ':' . $r->scheduled_at->timestamp,
    ]);

    $allBellKeys = collect($notifications['today'] ?? [])
        ->concat($notifications['tomorrow'] ?? [])
        ->pluck('key')
        ->concat($reminderToday->pluck('key'))
        ->concat($reminderTomorrow->pluck('key'))
        ->filter()
        ->unique()
        ->values();

    $clearedBellKeys = $user && $allBellKeys->isNotEmpty()
        ? \App\Models\NotificationClear::query()
            ->where('user_id', $user->id)
            ->whereIn('notification_key', $allBellKeys)
            ->pluck('notification_key')
            ->flip()
        : collect();

    $reminderToday = $reminderToday
        ->reject(fn ($entry) => $clearedBellKeys->has($entry['key']))
        ->values();
    $reminderTomorrow = $reminderTomorrow
        ->reject(fn ($entry) => $clearedBellKeys->has($entry['key']))
        ->values();

    $existingNotificationCount = (int) ($notifications['count'] ?? 0);
    $bellNotificationCount = $existingNotificationCount + $reminderToday->count() + $reminderTomorrow->count();
    $hasBellNotifications = $bellNotificationCount > 0;

    /* Old Bootstrap-icon names from the controller -> offline SVG names */
    $iconMap = [
        'bi-bell' => 'bell', 'bi-calendar-event' => 'cal', 'bi-calendar3' => 'cal', 'bi-clock' => 'clock',
        'bi-geo-alt' => 'pin', 'bi-geo-alt-fill' => 'pin', 'bi-people' => 'people', 'bi-people-fill' => 'people',
        'bi-house' => 'house', 'bi-house-fill' => 'house', 'bi-shield-exclamation' => 'shield',
        'bi-telephone' => 'phone', 'bi-telephone-fill' => 'phone', 'bi-qr-code' => 'qr', 'bi-box-seam' => 'box',
        'bi-file-earmark-text' => 'file', 'bi-file-earmark-text-fill' => 'file', 'bi-exclamation-triangle' => 'alert',
        'bi-exclamation-triangle-fill' => 'alert', 'bi-gift' => 'box', 'bi-megaphone' => 'bell',
    ];
    $ico = fn ($bi) => $iconMap[$bi] ?? 'bell';

    $notifUrl = function ($n) {
        $type = $n['type'] ?? null;
        if (empty($n['record_id'])) return null;
        if ($type === 'relief_operation' && \Illuminate\Support\Facades\Route::has('relief-assistance.show')) return route('relief-assistance.show', $n['record_id']);
        if ($type === 'person_complaint_hearing' && \Illuminate\Support\Facades\Route::has('reports-complaints.show')) return route('reports-complaints.show', $n['record_id']);
        return null;
    };

    $fromOps = fn ($list) => collect($list)->map(fn ($n) => [
        'key' => $n['key'] ?? null, 'url' => $notifUrl($n), 'prio' => $n['priority'] ?? 'normal', 'icon' => $ico($n['icon'] ?? 'bi-bell'),
        'title' => $n['title'] ?? 'Reminder', 'msg' => $n['message'] ?? '', 'desc' => $n['description'] ?? null,
        'time' => $n['time'] ?? null, 'loc' => $n['location'] ?? null,
    ]);
    $fromReminders = fn ($list) => $list->map(function ($entry) {
        $r = $entry['record'];

        return [
            'key' => $entry['key'],
            'url' => \Illuminate\Support\Facades\Route::has('reminders.show') ? route('reminders.show', $r) : null,
            'prio' => match ($r->priority) { 'urgent' => 'high', 'important' => 'medium', default => 'normal' },
            'icon' => 'cal', 'title' => $r->title, 'msg' => $r->type, 'desc' => $r->notes,
            'time' => $r->scheduled_at->format('g:i A'), 'loc' => $r->location,
        ];
    });

    $bellSections = [
        ['label' => 'Today', 'cls' => 'today', 'items' => $fromOps($notifications['today'] ?? [])->concat($fromReminders($reminderToday))],
        ['label' => 'Tomorrow', 'cls' => 'tomorrow', 'items' => $fromOps($notifications['tomorrow'] ?? [])->concat($fromReminders($reminderTomorrow))],
    ];

    /* Bell redesign helpers: priority tag text + per-section counts for the tabs */
    $nbPrioLabel = ['high' => 'Urgent', 'medium' => 'Important'];
    $nbCounts = [];
    foreach ($bellSections as $s) { $nbCounts[$s['cls']] = $s['items']->count(); }
@endphp

<!DOCTYPE html>
<html lang="en" data-theme="{{ $initialTheme }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#e23b49">

    <link rel="icon" type="image/png" href="{{ $logo }}">
    <link rel="shortcut icon" type="image/png" href="{{ $logo }}">

    <title>@yield('title', 'Barangay Poblacion Record Management System')</title>

    {{-- Page components (grid, forms, modals) still use Bootstrap from the CDN.
         The sidebar, top bar, menus and popups below do NOT depend on it, so they stay intact offline. --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* =========================================================
           WATERMELON THEME — flesh-pink top bar, soft blush canvas.
           The sidebar uses a calm deep-emerald palette (see SIDEBAR section).
           System fonts + inline SVG only.
           ========================================================= */
        :root {
            --sidebar-w: 260px;
            --primary: #e5505c; --accent-soft: rgba(229,80,92,.13); --on-accent: #fff;
            --green: #4fae4e; --green-d: #3b8a40; --flesh: #ef6b78; --pale: #e4f2d6;
            --page-bg: #f4d9df; --text-dark: #3a2a2e; --muted-text: #8a6f75;
            --surface: #fdf3f5; --surface-secondary: #fae7eb; --surface-tertiary: #f6dce1;
            --border-color: #efc6ce; --border-soft: #e6b3bd;
            --input-bg: #fff; --input-text: #3a2a2e; --table-bg: #fff8f9; --table-hover: #fae7eb;
            --shadow-color: rgba(120,40,55,.12);
            --shadow: 0 2px 4px rgba(120,40,55,.06), 0 10px 24px rgba(120,40,55,.08);
            --tb1: #e23b49; --tb2: #f0616c;
            --font: ui-rounded, 'Nunito', 'Quicksand', 'Varela Round', 'Trebuchet MS', system-ui, -apple-system, 'Segoe UI', sans-serif;
            --bs-body-font-family: var(--font);
            --bs-link-color-rgb: 229,80,92; --bs-link-hover-color-rgb: 190,45,60;

            /* Sidebar palette: deep forest emerald, light text, solid mint active block */
            --sb-bg-top: #12301f;
            --sb-bg-bottom: #0c2217;
            --sb-border: rgba(255,255,255,.10);
            --sb-label: #b9e2c9;          /* section headers  (~10:1 on the sidebar) */
            --sb-text: #dcebe2;           /* inactive links   (~12:1) */
            --sb-icon: #a9cbb7;           /* inactive icons   (~8:1) */
            --sb-hover-bg: rgba(255,255,255,.08);
            --sb-active-bg: #6ee7a0;      /* solid mint fill */
            --sb-active-text: #052e1a;    /* near-black green on mint (~10:1) */
            --sb-muted: #a3c2b0;
        }
        html[data-theme="dark"] {
            --primary: #ff7f8b; --accent-soft: rgba(255,127,139,.16); --on-accent: #2b0c12;
            --green: #5fc062; --green-d: #3f9a48; --flesh: #d4596a; --pale: #223a2b;
            --page-bg: #0f1a14; --text-dark: #f6eff1; --muted-text: #a8b8ad;
            --surface: #17251d; --surface-secondary: #1c2d23; --surface-tertiary: #24382b;
            --border-color: rgba(255,255,255,.09); --border-soft: rgba(255,255,255,.15);
            --input-bg: #1c2d23; --input-text: #f6eff1; --table-bg: #17251d; --table-hover: #1f3126;
            --shadow-color: rgba(0,0,0,.4); --shadow: 0 2px 4px rgba(0,0,0,.25), 0 8px 20px rgba(0,0,0,.22);
            --tb1: #8c2f3d; --tb2: #b04757;
            --bs-link-color-rgb: 255,150,160; --bs-link-hover-color-rgb: 255,190,196;

            --sb-bg-top: #0a1c12;
            --sb-bg-bottom: #06130c;
            --sb-border: rgba(255,255,255,.09);
        }

        * { box-sizing: border-box; }
        html, body { background: var(--page-bg); }
        body { margin: 0; color: var(--text-dark); font-family: var(--font); transition: background-color .2s, color .2s; }
        a { text-decoration: none; }
        .min-w-0 { min-width: 0; }
        :focus-visible { outline: 3px solid var(--green); outline-offset: 2px; }
        .i { width: 1.1em; height: 1.1em; flex-shrink: 0; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* =========================================================
           SIDEBAR (redesigned)
           - Deep emerald background, no texture, no bright rind
           - Section headers: bold, uppercase, high contrast
           - Inactive links: light muted text + icons
           - Active link: solid mint block with dark text
           - Same 40px row height, 12px padding and 12px icon gap everywhere
           ========================================================= */
        .sidebar { position: fixed; inset: 0 auto 0 0; width: var(--sidebar-w); z-index: 1040; display: flex; flex-direction: column; padding: 18px 14px 14px; overflow: hidden; color: var(--sb-text);
            background: linear-gradient(180deg, var(--sb-bg-top), var(--sb-bg-bottom));
            border-right: 1px solid var(--sb-border);
            box-shadow: 4px 0 24px rgba(0,0,0,.18); transition: transform .25s ease; }

        /* Brand / header */
        .sidebar-brand { flex: none; display: flex; align-items: center; gap: 12px; padding: 0 6px 16px; margin-bottom: 8px; border-bottom: 1px solid var(--sb-border); }
        .seal { flex: 0 0 56px; width: 56px; height: 56px; padding: 2px; border-radius: 50%; background: rgba(255,255,255,.14); box-shadow: 0 0 0 1px rgba(255,255,255,.22); }
        .seal-in { position: relative; width: 100%; height: 100%; overflow: hidden; border-radius: 50%; background: #fff; }
        .seal-in svg, .seal-in img { position: absolute; inset: 0; width: 100%; height: 100%; }
        .seal-in img { object-fit: cover; transform: translateZ(0); backface-visibility: hidden; }
        .brand-name { font: 800 16px/1.2 var(--font); letter-spacing: -.01em; color: #fff; }
        .brand-name span { display: block; }
        .brand-system { margin-top: 5px; font-size: 12px; font-weight: 600; line-height: 1.3; color: var(--sb-muted); }

        /* Navigation (scrolls only as a safety net on very short screens) */
        .sidebar-nav { flex: 1 1 0; min-height: 0; display: flex; flex-direction: column; overflow-y: auto; overflow-x: hidden; padding-right: 2px; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.22) transparent; }
        .sidebar-nav::-webkit-scrollbar { width: 6px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.22); border-radius: 10px; }
        .sidebar-group { flex: none; display: flex; flex-direction: column; gap: 2px; }
        .sidebar-group + .sidebar-group { margin-top: 18px; }
        .sidebar-label { display: block; margin: 0 0 6px; padding: 0 12px; font-size: 11px; font-weight: 800; letter-spacing: .12em; line-height: 1.2; text-transform: uppercase; color: var(--sb-label); }

        .sidebar-link { position: relative; display: flex; align-items: center; gap: 12px; min-height: 40px; padding: 0 12px; border-radius: 10px; color: var(--sb-text); font-size: 13.5px; font-weight: 600; line-height: 1.25; transition: background-color .15s, color .15s; }
        .sidebar-link .i { width: 20px; height: 20px; color: var(--sb-icon); transition: color .15s; }
        .sidebar-link span { min-width: 0; }
        .sidebar-link:hover { background: var(--sb-hover-bg); color: #fff; }
        .sidebar-link:hover .i { color: #fff; }

        /* Active page: solid mint block, dark text and icon */
        .sidebar-link.active, .sidebar-link.active:hover { background: var(--sb-active-bg); color: var(--sb-active-text); font-weight: 800; box-shadow: none; }
        .sidebar-link.active .i, .sidebar-link.active:hover .i { color: var(--sb-active-text); }

        /* Keyboard focus must be visible on the dark background */
        .sidebar :focus-visible { outline: 2px solid #fff; outline-offset: -2px; }
        .sidebar-link.active:focus-visible { outline-color: var(--sb-active-text); }

        /* Footer / location */
        .sidebar-foot { flex: none; display: flex; align-items: center; gap: 12px; margin-top: 12px; padding: 14px 12px 2px; border-top: 1px solid var(--sb-border); color: var(--sb-muted); font-size: 11.5px; line-height: 1.3; }
        .sidebar-foot .i { width: 20px; height: 20px; color: var(--sb-label); }
        .sidebar-foot strong { display: block; color: #fff; font-size: 12.5px; font-weight: 800; }

        .sidebar-backdrop { display: none; position: fixed; inset: 0; z-index: 1030; background: rgba(40,10,20,.5); }
        .main-content { min-height: 100vh; margin-left: var(--sidebar-w); width: calc(100% - var(--sidebar-w)); }

        /* Short screens: tighten spacing so everything still fits */
        @media (max-height: 820px) {
            .sidebar { padding-top: 14px; }
            .sidebar-brand { padding-bottom: 12px; }
            .seal { flex-basis: 48px; width: 48px; height: 48px; }
            .brand-system { display: none; }
            .sidebar-group + .sidebar-group { margin-top: 12px; }
            .sidebar-link { min-height: 36px; font-size: 13px; }
        }
        @media (max-height: 700px) {
            .sidebar-link { min-height: 32px; font-size: 12.5px; }
            .sidebar-link .i { width: 18px; height: 18px; }
            .sidebar-foot { display: none; }
        }

        /* ===================== TOP BAR ===================== */
        .topbar { position: sticky; top: 0; z-index: 900; display: flex; align-items: center; justify-content: space-between; gap: 14px; min-height: 66px; padding: 0 28px;
            background: linear-gradient(90deg, var(--tb1), var(--tb2)); border-radius: 0 0 24px 24px; box-shadow: 0 8px 22px rgba(229,80,92,.24); color: #fff; }
        .tb-left { display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0; }
        .tb-right { display: flex; align-items: center; gap: 10px; margin-left: auto; }
        .topbar-toggle, .topbar-brand { display: none; }
        .topbar-toggle { width: 40px; height: 40px; align-items: center; justify-content: center; border: 0; border-radius: 50%; background: rgba(255,255,255,.24); color: #fff; font-size: 19px; }
        .topbar-brand { align-items: center; gap: 9px; font: 800 14px var(--font); color: #fff; }
        .topbar-brand .mini { position: relative; width: 38px; height: 38px; overflow: hidden; border-radius: 50%; background: #fff; }
        .topbar-brand .mini svg, .topbar-brand .mini img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }

        /* ---- global search ---- */
        .gsearch { position: relative; width: min(100%, 340px); transition: width .2s ease; }
        .gsearch:focus-within { width: min(100%, 440px); }
        .gsearch-field { position: relative; margin: 0; display: block; }
        .gsearch-field > .i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #fff; font-size: 15px; pointer-events: none; transition: color .15s; }
        .gsearch-field input { width: 100%; min-height: 40px; padding: 0 74px 0 40px; border: 0; border-radius: 999px; background: rgba(255,255,255,.26); color: #fff; font: 600 14px var(--font); -webkit-appearance: none; appearance: none; }
        .gsearch-field input::-webkit-search-cancel-button { display: none; }
        .gsearch-field input::placeholder { color: rgba(255,255,255,.95); }
        .gsearch-field input:focus { outline: none; background: #fff; color: #3a2a2e; box-shadow: 0 0 0 4px rgba(255,255,255,.35); }
        .gsearch-field input:focus::placeholder { color: #8a6f75; }
        .gsearch-field:focus-within > .i { color: var(--primary); }
        .gsearch-hint { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); padding: 2px 8px; border-radius: 7px; background: rgba(255,255,255,.28); color: #fff; font: 800 10.5px var(--font); letter-spacing: .02em; pointer-events: none; }
        .gsearch.has-value .gsearch-hint, .gsearch:focus-within .gsearch-hint { display: none; }
        .gsearch-clear { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); width: 26px; height: 26px; display: none; align-items: center; justify-content: center; border: 0; border-radius: 50%; background: var(--surface-tertiary); color: var(--muted-text); }
        .gsearch.has-value .gsearch-clear { display: inline-flex; }
        .gsearch-clear:hover { background: var(--border-soft); color: var(--text-dark); }
        .gsearch-clear .i { width: 13px; height: 13px; }
        .gsearch-spin { position: absolute; right: 40px; top: 50%; width: 16px; height: 16px; margin-top: -8px; border: 2px solid var(--border-soft); border-top-color: var(--primary); border-radius: 50%; display: none; animation: gsSpin .7s linear infinite; }
        .gsearch.loading .gsearch-spin { display: block; }
        @keyframes gsSpin { to { transform: rotate(360deg); } }

        .gsearch-panel { display: none; position: absolute; left: 0; top: calc(100% + 12px); z-index: 1060; width: min(520px, calc(100vw - 40px)); max-height: min(70vh, 520px); overflow-y: auto; padding: 8px; border: 1px solid var(--border-color); border-radius: 20px; background: var(--surface); color: var(--text-dark); box-shadow: 0 18px 44px rgba(120,40,55,.22); overscroll-behavior: contain; }
        .gsearch-panel.show { display: block; animation: ddIn .16s ease-out; }
        .gsearch-panel::-webkit-scrollbar { width: 6px; }
        .gsearch-panel::-webkit-scrollbar-thumb { background: var(--border-soft); border-radius: 10px; }
        .gs-group { display: flex; align-items: center; gap: 8px; padding: 10px 10px 5px; color: var(--muted-text); font-size: 10.5px; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; }
        .gs-group::after { content: ''; flex: 1; height: 1px; background: var(--border-color); }
        .gs-item { display: flex; align-items: center; gap: 12px; width: 100%; padding: 8px 10px; margin-bottom: 2px; border-radius: 14px; color: var(--text-dark); cursor: pointer; }
        .gs-item:hover { color: var(--text-dark); }
        .gs-item.active { background: var(--surface-tertiary); color: var(--text-dark); }
        .gs-ico { width: 34px; height: 34px; min-width: 34px; display: flex; align-items: center; justify-content: center; border-radius: 11px; background: var(--accent-soft); color: var(--primary); font-size: 16px; }
        .gs-item.is-action .gs-ico { background: rgba(79,174,78,.16); color: var(--green-d); }
        html[data-theme="dark"] .gs-item.is-action .gs-ico { color: #8fe29a; }
        .gs-text { min-width: 0; flex: 1; display: flex; flex-direction: column; }
        .gs-title { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 13.5px; font-weight: 800; line-height: 1.3; }
        .gs-sub { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 1px; color: var(--muted-text); font-size: 11.5px; font-weight: 600; }
        .gs-title mark { padding: 0 1px; border-radius: 3px; background: var(--accent-soft); color: var(--primary); }
        .gs-enter { flex-shrink: 0; display: none; align-items: center; padding: 2px 7px; border-radius: 6px; background: var(--surface); border: 1px solid var(--border-soft); color: var(--muted-text); font-size: 10px; font-weight: 800; }
        .gs-item.active .gs-enter { display: inline-flex; }
        .gs-state { padding: 26px 20px; text-align: center; }
        .gs-state-ico { width: 46px; height: 46px; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: var(--accent-soft); color: var(--primary); font-size: 20px; }
        .gs-state-title { font-size: 13.5px; font-weight: 800; }
        .gs-state-text { max-width: 300px; margin: 4px auto 0; color: var(--muted-text); font-size: 11.5px; line-height: 1.5; }
        .gs-note { display: flex; align-items: center; gap: 8px; margin: 4px 2px 2px; padding: 9px 11px; border-radius: 12px; background: var(--surface-secondary); color: var(--muted-text); font-size: 11px; font-weight: 600; }
        .gs-foot { display: flex; align-items: center; gap: 14px; margin-top: 6px; padding: 9px 10px 4px; border-top: 1px solid var(--border-color); color: var(--muted-text); font-size: 10.5px; font-weight: 700; }
        .gs-foot kbd { padding: 1px 6px; border: 1px solid var(--border-soft); border-radius: 5px; background: var(--surface); color: var(--text-dark); font: 800 10px var(--font); }

        .net-pill { display: none; align-items: center; gap: 7px; height: 34px; padding: 0 13px; border-radius: 999px; background: #fff; color: var(--primary); font-size: 12.5px; font-weight: 800; }
        .net-pill.show { display: inline-flex; }
        .net-pill::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: #e5505c; }

        /* ---- own dropdowns (no Bootstrap needed) ---- */
        .dd { position: relative; flex-shrink: 0; }
        .dd-menu { display: none; position: absolute; right: 0; top: calc(100% + 12px); z-index: 1060; color: var(--text-dark); }
        .dd-menu.show { display: block; animation: ddIn .16s ease-out; }
        @keyframes ddIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }

        /* =========================================================
           NOTIFICATION BELL (redesigned dropdown)
           Panel = header + tabs + scrollable cards + footer button
           ========================================================= */
        .notification-button { position: relative; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 0; border-radius: 50%; background: rgba(255,255,255,.26); color: #fff; font-size: 20px; transition: background-color .15s, color .15s; }
        .notification-button:hover, .notification-button[aria-expanded="true"] { background: #fff; color: var(--primary); }
        .notification-button.has-unread svg { transform-origin: 50% 0; animation: nbRing 1.4s ease .6s 1; }
        @keyframes nbRing { 0%,100% { transform: rotate(0) } 15% { transform: rotate(14deg) } 30% { transform: rotate(-12deg) } 45% { transform: rotate(8deg) } 60% { transform: rotate(-5deg) } 75% { transform: rotate(2deg) } }
        .notification-badge { position: absolute; top: -4px; right: -5px; min-width: 19px; height: 19px; padding: 0 5px; display: inline-flex; align-items: center; justify-content: center; border: 2px solid var(--tb1); border-radius: 999px; background: #fff; color: var(--primary); font-size: 9.5px; font-weight: 800; line-height: 1; box-shadow: 0 2px 6px rgba(0,0,0,.18); }
        .notification-badge[hidden] { display: none; }

        .notification-menu { width: 400px; max-width: calc(100vw - 24px); padding: 0; border: 1px solid var(--border-color); border-radius: 24px; background: var(--surface); box-shadow: 0 18px 44px rgba(120,40,55,.22); overflow: hidden; }
        .notification-menu.show { display: flex; flex-direction: column; max-height: min(78vh, 620px); }

        /* header */
        .notification-menu-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 18px 18px 14px; background: linear-gradient(135deg, var(--accent-soft), transparent 75%), var(--surface-secondary); }
        .notification-menu-title { font-size: 18px; font-weight: 800; line-height: 1.2; letter-spacing: -.01em; }
        .notification-menu-subtitle { margin-top: 3px; color: var(--muted-text); font-size: 12px; font-weight: 500; }
        .nb-head-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .notification-count-label { flex-shrink: 0; padding: 4px 10px; border-radius: 999px; background: var(--accent-soft); color: var(--primary); font-size: 11px; font-weight: 800; }
        .notification-count-label[hidden], .notification-clear-all[hidden] { display: none; }
        .notification-clear-all { display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 6px 11px; border: 0; border-radius: 10px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border-soft); color: var(--muted-text); font: 700 11px var(--font); cursor: pointer; }
        .notification-clear-all:hover { background: var(--accent-soft); color: var(--primary); box-shadow: inset 0 0 0 1px var(--primary); }
        .notification-clear-all:disabled { opacity: .55; cursor: wait; }

        /* tabs */
        .nb-tabs { display: flex; gap: 4px; margin: 0 14px; padding: 4px; border-radius: 14px; background: var(--surface-tertiary); transform: translateY(-6px); }
        .nb-tabs[hidden] { display: none; }
        .nb-tab { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 32px; border: 0; border-radius: 10px; background: transparent; color: var(--muted-text); font: 800 12px var(--font); cursor: pointer; transition: background-color .15s, color .15s, box-shadow .15s; }
        .nb-tab:hover { color: var(--text-dark); }
        .nb-tab[aria-selected="true"] { background: var(--surface); color: var(--text-dark); box-shadow: 0 1px 3px var(--shadow-color); }
        .nb-tab-n { min-width: 18px; padding: 1px 6px; border-radius: 999px; background: var(--accent-soft); color: var(--primary); font-size: 10px; line-height: 1.5; }
        .nb-tab[aria-selected="true"] .nb-tab-n { background: var(--primary); color: var(--on-accent); }

        /* list */
        .notification-list { flex: 1; min-height: 0; overflow-y: auto; padding: 0 0 8px; overscroll-behavior: contain; }
        .notification-list::-webkit-scrollbar { width: 6px; }
        .notification-list::-webkit-scrollbar-thumb { background: var(--border-soft); border-radius: 10px; }

        .notification-section-label { position: sticky; top: 0; z-index: 2; display: flex; align-items: center; gap: 8px; padding: 10px 18px 8px; background: var(--surface); font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .notification-section-label::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: currentColor; box-shadow: 0 0 0 3px color-mix(in srgb, currentColor 22%, transparent); }
        .notification-section-line { flex: 1; height: 1px; background: var(--border-color); }
        .notification-section-today { color: #dc3545; } .notification-section-tomorrow { color: #b07800; }
        html[data-theme="dark"] .notification-section-today { color: #ff7b87; } html[data-theme="dark"] .notification-section-tomorrow { color: #f2c45f; }

        /* cards */
        .notification-item-row { position: relative; display: block; margin: 0 12px 8px; }
        .notification-item { position: relative; display: flex; align-items: flex-start; gap: 12px; width: 100%; padding: 12px 40px 12px 16px; overflow: hidden; border: 1px solid var(--border-color); border-radius: 16px; background: var(--surface-secondary); color: var(--text-dark); transition: border-color .15s, box-shadow .15s, transform .15s; }
        .notification-item::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--green); }
        .notification-item-high::before { background: #dc3545; }
        .notification-item-medium::before { background: #f5b031; }
        a.notification-item:hover { transform: translateY(-1px); border-color: var(--border-soft); box-shadow: 0 6px 16px var(--shadow-color); color: var(--text-dark); }
        .notification-item-icon { width: 38px; height: 38px; min-width: 38px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--accent-soft); color: var(--primary); font-size: 16px; }
        .notification-item-high .notification-item-icon { background: rgba(220,53,69,.13); color: #dc3545; }
        .notification-item-medium .notification-item-icon { background: rgba(245,176,49,.16); color: #b07800; }
        html[data-theme="dark"] .notification-item-high .notification-item-icon { background: rgba(255,123,135,.15); color: #ff7b87; }
        html[data-theme="dark"] .notification-item-medium .notification-item-icon { background: rgba(242,196,95,.15); color: #f2c45f; }
        .notification-item-content { min-width: 0; flex: 1; display: flex; flex-direction: column; }
        .notification-item-title { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; font-size: 13.5px; font-weight: 800; line-height: 1.3; }
        .notification-item-message { margin-top: 2px; font-size: 12px; font-weight: 600; line-height: 1.35; opacity: .85; }
        .notification-item-description { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-top: 2px; color: var(--muted-text); font-size: 11px; line-height: 1.35; }
        .notification-item-meta { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .notification-item-meta span { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border: 1px solid var(--border-color); border-radius: 999px; background: var(--surface); color: var(--muted-text); font-size: 10.5px; font-weight: 700; line-height: 1.3; }
        .nb-tag { padding: 1px 8px; border-radius: 999px; font-size: 9.5px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .nb-tag-high { background: rgba(220,53,69,.14); color: #dc3545; }
        .nb-tag-medium { background: rgba(245,176,49,.2); color: #b07800; }
        html[data-theme="dark"] .nb-tag-high { color: #ff7b87; } html[data-theme="dark"] .nb-tag-medium { color: #f2c45f; }

        /* clear (x) button on each card */
        .notification-clear-button { position: absolute; top: 8px; right: 8px; width: 26px; height: 26px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 50%; background: transparent; color: var(--muted-text); font-size: 0; opacity: 0; cursor: pointer; transition: opacity .15s, background-color .15s, color .15s; }
        .notification-clear-button .i { width: 13px; height: 13px; }
        .notification-item-row:hover .notification-clear-button, .notification-item-row:focus-within .notification-clear-button { opacity: 1; }
        .notification-clear-button:hover { background: rgba(220,53,69,.14); color: #dc3545; }
        .notification-clear-button:disabled { opacity: .55; cursor: wait; }
        @media (hover: none) { .notification-clear-button { opacity: 1; } }

        /* tab filtering */
        .notification-menu[data-filter="today"] [data-section="tomorrow"],
        .notification-menu[data-filter="tomorrow"] [data-section="today"] { display: none; }
        .nb-filter-empty { display: none; padding: 30px 20px; text-align: center; color: var(--muted-text); font-size: 12px; font-weight: 600; }
        .nb-filter-empty.show { display: block; }

        /* empty + feedback */
        .notification-empty { padding: 36px 24px 32px; text-align: center; }
        .notification-empty-icon { width: 56px; height: 56px; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: rgba(79,174,78,.15); color: var(--green-d); font-size: 24px; }
        .notification-empty-title { font-size: 14px; font-weight: 800; }
        .notification-empty-text { max-width: 290px; margin: 5px auto 0; color: var(--muted-text); font-size: 11px; line-height: 1.5; }
        .notification-feedback { padding: 4px 18px; color: var(--muted-text); font-size: 11px; }
        .notification-feedback:empty { display: none; }

        /* footer */
        .notification-quick-action { padding: 8px; border-top: 1px solid var(--border-color); background: var(--surface-secondary); }
        .notification-manage-link { display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%; padding: 11px; border-radius: 14px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border-soft); color: var(--primary); font-size: 12px; font-weight: 800; transition: background-color .15s, color .15s; }
        .notification-manage-link:hover { background: var(--primary); color: var(--on-accent); }

        /* ---- profile ---- */
        .user-profile-trigger { display: inline-flex; align-items: center; gap: 10px; min-width: 176px; padding: 4px 14px 4px 4px; border: 0; border-radius: 999px; background: rgba(255,255,255,.26); color: #fff; transition: background-color .15s, box-shadow .15s; }
        .user-profile-trigger:hover, .user-profile-trigger[aria-expanded="true"] { background: rgba(255,255,255,.38); box-shadow: 0 0 0 4px rgba(255,255,255,.22); }
        .user-profile-avatar, .user-dropdown-avatar { display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; background: linear-gradient(135deg, #4fae4e, #2f7a34); color: #fff; font-weight: 800; border-radius: 50%; }
        .user-profile-avatar { width: 36px; height: 36px; font-size: 17px; border: 2px solid #fff; }
        .user-dropdown-avatar { width: 50px; height: 50px; min-width: 50px; font-size: 22px; border: 3px solid #fff; box-shadow: 0 3px 10px rgba(120,40,55,.18); }
        .user-profile-avatar img, .user-dropdown-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .user-profile-text { min-width: 0; display: flex; flex-direction: column; align-items: flex-start; line-height: 1.15; }
        .user-profile-name { max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 13px; font-weight: 800; }
        .user-profile-role { margin-top: 2px; font-size: 11.5px; color: rgba(255,255,255,.9); text-transform: capitalize; }
        .user-profile-chevron { margin-left: auto; font-size: 12px; transition: transform .2s; }
        .user-profile-trigger[aria-expanded="true"] .user-profile-chevron { transform: rotate(180deg); }
        .user-dropdown-menu { width: 312px; padding: 10px; border: 1px solid var(--border-color); border-radius: 22px; background: var(--surface); box-shadow: 0 18px 44px rgba(120,40,55,.22); }
        .user-dropdown-header { display: flex; align-items: center; gap: 12px; margin: -10px -10px 8px; padding: 16px; border-radius: 21px 21px 0 0; background: linear-gradient(135deg, var(--accent-soft), transparent 70%), var(--surface-secondary); border-bottom: 1px solid var(--border-color); }
        .user-dropdown-eyebrow { margin-bottom: 2px; color: var(--muted-text); font-size: 12px; font-weight: 600; }
        .user-dropdown-name { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 800; font-size: 16px; }
        .user-role-badge, .user-status-badge { display: inline-flex; align-items: center; gap: 5px; border-radius: 999px; font-size: 11px; font-weight: 800; }
        .user-role-badge { margin-top: 5px; padding: 3px 10px; background: var(--accent-soft); color: var(--primary); }
        .user-status-badge { margin-top: 5px; margin-left: 4px; padding: 3px 10px; background: rgba(79,174,78,.16); color: var(--green-d); }
        html[data-theme="dark"] .user-status-badge { color: #8fe29a; }
        .user-status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .user-dropdown-section-title { padding: 8px 10px 4px; color: var(--muted-text); font-size: 12px; font-weight: 800; }
        .user-dropdown-item, .theme-switch-row { display: flex; align-items: center; gap: 11px; width: 100%; padding: 8px 10px; margin-bottom: 2px; border: 0; border-radius: 14px; background: transparent; color: var(--text-dark); font: 700 13px var(--font); text-align: left; cursor: pointer; transition: background-color .15s; }
        .user-dropdown-item { min-height: 48px; } .theme-switch-row { min-height: 54px; }
        .user-dropdown-item:hover, .user-dropdown-item:focus, .theme-switch-row:hover { background: var(--surface-tertiary); color: var(--text-dark); outline: none; }
        .user-dropdown-item-icon, .theme-switch-icon { width: 34px; height: 34px; min-width: 34px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--accent-soft); color: var(--primary); font-size: 16px; }
        .user-dropdown-item.text-danger .user-dropdown-item-icon { color: #dc3545; background: rgba(220,53,69,.11); }
        .user-dropdown-item small, .theme-switch-row small { display: block; margin-top: 2px; color: var(--muted-text); font-size: 11px; font-weight: 500; }
        .theme-switch-indicator { width: 38px; height: 21px; margin-left: auto; border-radius: 999px; background: #d7b8be; position: relative; transition: background-color .2s; }
        .theme-switch-indicator::after { content: ''; position: absolute; width: 17px; height: 17px; top: 2px; left: 2px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25); transition: transform .2s; }
        html[data-theme="dark"] .theme-switch-indicator { background: var(--green); }
        html[data-theme="dark"] .theme-switch-indicator::after { transform: translateX(17px); }
        .user-dropdown-divider { margin: 6px 2px; border-top: 1px solid var(--border-color); }

        /* ===================== REMINDER POPUP ===================== */
        .global-reminder-alert { position: fixed; right: 24px; bottom: 24px; z-index: 1090; width: min(410px, calc(100vw - 30px)); display: none; overflow: hidden; border: 1px solid var(--border-color); border-radius: 22px; background: var(--surface); color: var(--text-dark); box-shadow: 0 22px 55px rgba(120,40,55,.28), 0 4px 18px rgba(120,40,55,.12); }
        .global-reminder-alert.show { display: block; animation: reminderSlideIn .28s ease-out; }
        .global-reminder-alert.priority-urgent { border-color: rgba(220,53,69,.4); } .global-reminder-alert.priority-important { border-color: rgba(245,176,49,.45); }
        .global-reminder-top { position: relative; display: flex; align-items: flex-start; gap: 12px; padding: 16px 16px 12px; }
        .global-reminder-icon { width: 44px; height: 44px; min-width: 44px; display: flex; align-items: center; justify-content: center; border-radius: 14px; background: var(--accent-soft); color: var(--primary); font-size: 20px; }
        .priority-urgent .global-reminder-icon { background: rgba(220,53,69,.13); color: #dc3545; } .priority-important .global-reminder-icon { background: rgba(245,176,49,.16); color: #b07800; }
        .global-reminder-content { min-width: 0; flex: 1; }
        .global-reminder-label { margin-bottom: 3px; color: var(--primary); font-size: 10px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .global-reminder-title { font-size: 16px; font-weight: 800; line-height: 1.3; overflow-wrap: anywhere; }
        .global-reminder-type { margin-top: 3px; color: var(--muted-text); font-size: 11px; font-weight: 600; }
        .global-reminder-close { width: 30px; height: 30px; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 50%; background: transparent; color: var(--muted-text); }
        .global-reminder-close:hover { background: var(--surface-tertiary); color: var(--text-dark); }
        .global-reminder-details { display: flex; flex-direction: column; gap: 7px; margin: 0 16px; padding: 11px 12px; border: 1px solid var(--border-color); border-radius: 14px; background: var(--surface-secondary); }
        .global-reminder-detail { display: flex; align-items: flex-start; gap: 8px; font-size: 11.5px; line-height: 1.35; }
        .global-reminder-detail .i { margin-top: 1px; color: var(--muted-text); }
        .global-reminder-notes { margin: 11px 16px 0; color: var(--muted-text); font-size: 11px; line-height: 1.45; }
        .global-reminder-actions { display: flex; align-items: center; justify-content: flex-end; gap: 8px; padding: 14px 16px 16px; }
        .global-reminder-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 36px; padding: 7px 14px; border-radius: 999px; font: 800 11.5px var(--font); cursor: pointer; }
        .global-reminder-btn-dismiss { border: 1px solid var(--border-soft); background: var(--surface-secondary); color: var(--text-dark); }
        .global-reminder-btn-dismiss:hover { background: var(--surface-tertiary); color: var(--text-dark); }
        .global-reminder-btn-view { border: 1px solid var(--green); background: var(--green); color: #fff; }
        .global-reminder-btn-view:hover { filter: brightness(1.08); color: #fff; }
        .global-reminder-queue { position: absolute; top: 9px; right: 47px; min-width: 20px; height: 20px; padding: 0 6px; display: none; align-items: center; justify-content: center; border-radius: 999px; background: #dc3545; color: #fff; font-size: 9px; font-weight: 800; }
        .global-reminder-queue.show { display: inline-flex; }
        @keyframes reminderSlideIn { from { opacity: 0; transform: translate(10px, 18px); } to { opacity: 1; transform: none; } }

        /* ---- offline toast ---- */
        .ep-toast { position: fixed; left: 50%; bottom: 22px; z-index: 1100; transform: translate(-50%, 20px); padding: 11px 20px; border-radius: 999px; background: #2a1a1d; color: #fff; font-size: 13.5px; font-weight: 700; box-shadow: 0 10px 30px rgba(0,0,0,.28); opacity: 0; pointer-events: none; transition: opacity .25s, transform .25s; }
        .ep-toast.show { opacity: 1; transform: translate(-50%, 0); }
        html[data-theme="dark"] .ep-toast { background: #f6eff1; color: #2a1a1d; }

        /* ---- flash messages (no Bootstrap needed) ---- */
        .flash { position: relative; display: flex; align-items: flex-start; gap: 10px; margin-bottom: 16px; padding: 14px 44px 14px 16px; border-radius: 16px; font-size: 14px; font-weight: 600; border: 1px solid transparent; }
        .flash-success { background: rgba(79,174,78,.14); border-color: rgba(79,174,78,.35); color: var(--green-d); }
        .flash-error { background: rgba(229,80,92,.12); border-color: rgba(229,80,92,.35); color: #b3303c; }
        html[data-theme="dark"] .flash-success { color: #8fe29a; } html[data-theme="dark"] .flash-error { color: #ff9aa4; }
        .flash ul { margin: 6px 0 0; padding-left: 18px; font-weight: 500; }
        .flash-x { position: absolute; top: 10px; right: 10px; width: 28px; height: 28px; display: grid; place-items: center; border: 0; border-radius: 50%; background: transparent; color: inherit; cursor: pointer; }
        .flash-x:hover { background: rgba(0,0,0,.08); }

        /* ===================== CONTENT & BOOTSTRAP THEMING ===================== */
        .page-content { padding: 28px; }
        .card { background-color: var(--surface); color: var(--text-dark); border-color: var(--border-color); border-radius: 22px; box-shadow: var(--shadow); }
        .card-header, .card-footer { background-color: var(--surface); color: var(--text-dark); border-color: var(--border-color); }
        .card-body { color: var(--text-dark); }
        html[data-theme="dark"] .bg-white { background-color: var(--surface) !important; color: var(--text-dark) !important; }
        html[data-theme="dark"] .bg-light { background-color: var(--surface-tertiary) !important; color: var(--text-dark) !important; }
        html[data-theme="dark"] .bg-body { background-color: var(--page-bg) !important; color: var(--text-dark) !important; }
        .btn-primary { --bs-btn-bg: var(--primary); --bs-btn-border-color: var(--primary); --bs-btn-color: var(--on-accent); --bs-btn-hover-bg: var(--primary); --bs-btn-hover-border-color: var(--primary); --bs-btn-hover-color: var(--on-accent); --bs-btn-active-bg: var(--primary); --bs-btn-active-border-color: var(--primary); --bs-btn-active-color: var(--on-accent); --bs-btn-disabled-bg: var(--primary); --bs-btn-disabled-border-color: var(--primary); font-weight: 700; }
        .btn-primary:hover { filter: brightness(1.08); }
        .btn-outline-primary { --bs-btn-color: var(--primary); --bs-btn-border-color: var(--primary); --bs-btn-hover-bg: var(--primary); --bs-btn-hover-border-color: var(--primary); --bs-btn-hover-color: var(--on-accent); --bs-btn-active-bg: var(--primary); --bs-btn-active-border-color: var(--primary); --bs-btn-active-color: var(--on-accent); font-weight: 700; }
        .btn-success { --bs-btn-bg: var(--green); --bs-btn-border-color: var(--green); --bs-btn-hover-bg: var(--green-d); --bs-btn-hover-border-color: var(--green-d); --bs-btn-active-bg: var(--green-d); --bs-btn-active-border-color: var(--green-d); font-weight: 700; }
        .text-primary { color: var(--primary) !important; }
        .text-muted { color: var(--muted-text) !important; }
        .form-control, .form-select { background-color: var(--input-bg); color: var(--input-text); border-color: var(--border-soft); border-radius: 12px; }
        .form-control::placeholder { color: var(--muted-text); }
        .form-control:focus, .form-select:focus { background-color: var(--input-bg); color: var(--input-text); border-color: var(--primary); box-shadow: 0 0 0 4px var(--accent-soft); }
        html[data-theme="dark"] .form-control:disabled, html[data-theme="dark"] .form-control[readonly], html[data-theme="dark"] .form-select:disabled { background-color: #101a14; color: #858c95; }
        html[data-theme="dark"] .form-select option { background-color: #1c2d23; color: #f6eff1; }
        .table { --bs-table-bg: var(--table-bg); --bs-table-color: var(--text-dark); --bs-table-border-color: var(--border-color); color: var(--text-dark); background-color: var(--table-bg); }
        .table > :not(caption) > * > * { background-color: var(--table-bg); color: var(--text-dark); border-bottom-color: var(--border-color); }
        .table-hover > tbody > tr:hover > * { background-color: var(--table-hover); color: var(--text-dark); }
        .table-light { --bs-table-bg: var(--surface-secondary); --bs-table-color: var(--muted-text); }
        html[data-theme="dark"] .table-light > * { background-color: var(--surface-secondary) !important; color: var(--muted-text) !important; }
        html[data-theme="dark"] .border, html[data-theme="dark"] .border-top, html[data-theme="dark"] .border-end, html[data-theme="dark"] .border-bottom, html[data-theme="dark"] .border-start { border-color: var(--border-color) !important; }
        .dropdown-menu { background-color: var(--surface); border-color: var(--border-color); box-shadow: 0 .5rem 1rem var(--shadow-color); }
        .dropdown-item { color: var(--text-dark); }
        .dropdown-item:hover, .dropdown-item:focus { background-color: var(--surface-tertiary); color: var(--text-dark); }
        .modal-content { background-color: var(--surface); color: var(--text-dark); border-color: var(--border-color); border-radius: 22px; }
        .modal-header, .modal-footer { border-color: var(--border-color); }
        .list-group-item { background-color: var(--surface); color: var(--text-dark); border-color: var(--border-color); }
        .page-link { background-color: var(--surface); color: var(--text-dark); border-color: var(--border-color); }
        .page-link:hover { background-color: var(--surface-tertiary); color: var(--text-dark); border-color: var(--border-color); }
        .page-item.active .page-link { background-color: var(--primary); border-color: var(--primary); color: var(--on-accent); }
        .input-group-text { background-color: var(--surface-tertiary); color: var(--text-dark); border-color: var(--border-soft); }
        .alert { border-radius: 16px; }
        html[data-theme="dark"] .alert-light { background-color: #1f3126; color: #f6eff1; border-color: rgba(255,255,255,.12); }
        html[data-theme="dark"] .btn-outline-secondary { color: #c9d6cd; border-color: rgba(255,255,255,.22); }
        html[data-theme="dark"] .btn-outline-secondary:hover { background-color: rgba(255,255,255,.18); color: #fff; }

        /* ===================== RESPONSIVE ===================== */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            body.sidebar-open .sidebar { transform: none; box-shadow: 0 0 40px rgba(0,0,0,.45); }
            body.sidebar-open .sidebar-backdrop { display: block; }
            body.sidebar-open { overflow: hidden; }
            .main-content { margin-left: 0; width: 100%; }
            .topbar { padding: 0 18px; }
            .topbar-toggle, .topbar-brand { display: inline-flex; }
            .gsearch { flex: 1; width: auto; max-width: 340px; }
            .gsearch:focus-within { width: auto; max-width: 340px; }
            .gsearch-hint { display: none; }
            .page-content { padding: 20px; }
        }
        @media (max-width: 768px) {
            .user-profile-trigger { min-width: auto; padding-right: 6px; }
            .user-profile-text, .user-profile-chevron { display: none; }
            .user-dropdown-menu { width: min(312px, calc(100vw - 24px)); }
            .topbar-brand span:last-child { display: none; }
        }
        @media (max-width: 576px) {
            .page-content { padding: 15px; } .topbar { padding: 0 14px; }
            .topbar-brand { display: none; }
            .notification-menu { position: fixed; top: 74px; right: 12px; left: 12px; width: auto; max-width: none; }
            .notification-menu.show { max-height: calc(100vh - 96px); }
            .gsearch { position: static; }
            .gsearch-panel { position: fixed; top: 74px; left: 12px; right: 12px; width: auto; }
            .global-reminder-alert { right: 15px; bottom: 15px; width: calc(100vw - 30px); }
            .gs-foot { display: none; }
        }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; animation: none !important; } }

        /* =====================================================================
           TOP BAR DECORATION (red flesh + glossy shine + black seeds,
           with white and green rind layers underneath).
           Keep this block near the END of this <style>.
           ===================================================================== */
        :root {
            --rind-white: #fff8f0;
            --rind-green: #3fae56;

            /* Black teardrop seeds with a small glossy highlight (8 seeds per 520px) */
            --seed-pattern: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='520' height='66' viewBox='0 0 520 66'%3E%3Cdefs%3E%3Cg id='s'%3E%3Cpath d='M0-9C2-5 6-1 6 3a6 6 0 0 1-12 0C-6-1-2-5 0-9z' fill='%23140a0c'/%3E%3Cellipse cx='-1.9' cy='2' rx='1.2' ry='2.3' fill='%23ffffff' opacity='.42'/%3E%3C/g%3E%3C/defs%3E%3Cuse href='%23s' transform='translate(40 22) rotate(-20)'/%3E%3Cuse href='%23s' transform='translate(105 48) rotate(25)'/%3E%3Cuse href='%23s' transform='translate(170 20) rotate(10)'/%3E%3Cuse href='%23s' transform='translate(238 49) rotate(-30)'/%3E%3Cuse href='%23s' transform='translate(300 19) rotate(20)'/%3E%3Cuse href='%23s' transform='translate(362 47) rotate(-10)'/%3E%3Cuse href='%23s' transform='translate(428 22) rotate(32)'/%3E%3Cuse href='%23s' transform='translate(486 49) rotate(-25)'/%3E%3C/svg%3E");
        }
        html[data-theme="dark"] {
            --rind-white: #d3e3d6;
            --rind-green: #2f7a3c;
        }

        .topbar {
            background-color: var(--tb1);
            background-image:
                var(--seed-pattern),
                linear-gradient(180deg, rgba(255,255,255,.22), rgba(255,255,255,0) 60%),
                linear-gradient(90deg, var(--tb1), var(--tb2));
            background-size: 520px 66px, auto, auto;
            background-repeat: repeat-x, no-repeat, no-repeat;
            box-shadow:
                0 4px 0 var(--rind-white),
                0 8px 0 var(--rind-green),
                0 16px 24px rgba(226,59,73,.22);
        }
        html[data-theme="dark"] .topbar {
            box-shadow:
                0 4px 0 var(--rind-white),
                0 8px 0 var(--rind-green),
                0 16px 24px rgba(0,0,0,.35);
        }

        /* Search bar: default look (white field, dark text) */
        .gsearch-field input { background: #fff; color: #3a2a2e; font: 500 14px var(--font); box-shadow: 0 2px 8px rgba(120,40,55,.18); }
        .gsearch-field input::placeholder { color: #9a8389; font-weight: 400; }
        .gsearch-field input:focus { background: #fff; color: #3a2a2e; box-shadow: 0 0 0 4px rgba(255,255,255,.4); }
        .gsearch-field > .i { color: #9a8389; }
        .gsearch-field:focus-within > .i { color: var(--primary); }
        .gsearch-hint { background: #f6dce1; color: #8a6f75; font-weight: 700; }
        html[data-theme="dark"] .gsearch-field input { background: #fff; color: #3a2a2e; }

        /* Header buttons: outlined instead of filled, so the seeds stay visible behind them. */
        .topbar-toggle,
        .user-profile-trigger {
            background: transparent;
            color: #fff;
            box-shadow: inset 0 0 0 2px rgba(255,255,255,.75);
        }
        .topbar-toggle:hover,
        .user-profile-trigger:hover,
        .user-profile-trigger[aria-expanded="true"] {
            background: rgba(255,255,255,.12);
            box-shadow: inset 0 0 0 2px #fff;
        }

        /* Notification bell: yellow */
        .notification-button {
            background: transparent;
            color: #ffd43b;
            box-shadow: inset 0 0 0 2px #ffd43b;
        }
        .notification-button:hover,
        .notification-button[aria-expanded="true"] {
            background: #ffd43b;
            color: #7a4e00;
            box-shadow: inset 0 0 0 2px #ffd43b;
        }
        .notification-button svg { filter: drop-shadow(0 1px 1px rgba(0,0,0,.25)); }
        .notification-badge { background: #ffd43b; color: #7a1f00; border-color: var(--tb1); }

        /* =====================================================================
           UI/UX COLOR SYSTEM (semantic) — keep at the very END of this <style>
           Brand decoration  : watermelon red header (decor only)
           Primary / action  : GREEN   (buttons, links, focus, selected tabs)
           Success           : green   (saved, active account, all caught up)
           Info / neutral    : BLUE    (today, normal reminders, quick actions)
           Warning           : AMBER   (important, tomorrow-level attention, offline)
           Danger / urgent   : RED     (urgent, errors, delete/clear, sign out)
           ===================================================================== */
        :root {
            --primary: #2f9e44; --primary-d: #237a35; --accent-soft: rgba(47,158,68,.13); --on-accent: #fff;
            --danger: #c92a3b; --danger-soft: rgba(201,42,59,.12);
            --warning: #e8a317; --warning-text: #9a6300; --warning-soft: rgba(232,163,23,.18);
            --info: #1c7ed6; --info-soft: rgba(28,126,214,.13);
            --bs-link-color-rgb: 35,122,53; --bs-link-hover-color-rgb: 27,97,42;
        }
        html[data-theme="dark"] {
            --primary: #5fc062; --primary-d: #8fe29a; --accent-soft: rgba(95,192,98,.16); --on-accent: #0b2314;
            --danger: #ff7b87; --danger-soft: rgba(255,123,135,.15);
            --warning: #f2c45f; --warning-text: #f2c45f; --warning-soft: rgba(242,196,95,.15);
            --info: #6cb2ff; --info-soft: rgba(108,178,255,.15);
            --bs-link-color-rgb: 143,226,154; --bs-link-hover-color-rgb: 190,242,198;
        }

        /* Quick actions in search = blue (differs from green "pages") */
        .gs-item.is-action .gs-ico { background: var(--info-soft); color: var(--info); }
        html[data-theme="dark"] .gs-item.is-action .gs-ico { color: var(--info); }

        /* Notification badge = attention (red text on white) */
        .notification-badge { color: #7a1f00; }
        .notification-count-label { background: var(--danger-soft); color: var(--danger); }

        /* Notification sections: Today = info blue, Tomorrow = neutral */
        .notification-section-today, html[data-theme="dark"] .notification-section-today { color: var(--info); }
        .notification-section-tomorrow, html[data-theme="dark"] .notification-section-tomorrow { color: var(--muted-text); }

        /* Notification cards: normal = info, important = amber, urgent = red */
        .notification-item::before { background: var(--info); }
        .notification-item-high::before { background: var(--danger); }
        .notification-item-medium::before { background: var(--warning); }
        .notification-item-icon { background: var(--info-soft); color: var(--info); }
        .notification-item-high .notification-item-icon, html[data-theme="dark"] .notification-item-high .notification-item-icon { background: var(--danger-soft); color: var(--danger); }
        .notification-item-medium .notification-item-icon, html[data-theme="dark"] .notification-item-medium .notification-item-icon { background: var(--warning-soft); color: var(--warning-text); }
        .nb-tag-high, html[data-theme="dark"] .nb-tag-high { background: var(--danger-soft); color: var(--danger); }
        .nb-tag-medium, html[data-theme="dark"] .nb-tag-medium { background: var(--warning-soft); color: var(--warning-text); }
        .notification-clear-button:hover { background: var(--danger-soft); color: var(--danger); }
        .notification-clear-all:hover { background: var(--danger-soft); color: var(--danger); box-shadow: inset 0 0 0 1px var(--danger); }
        .notification-manage-link { color: var(--primary-d); }
        .notification-manage-link:hover { background: var(--primary); color: var(--on-accent); }

        /* Reminder popup */
        .global-reminder-icon { background: var(--info-soft); color: var(--info); }
        .global-reminder-label { color: var(--info); }
        .global-reminder-alert.priority-urgent { border-color: var(--danger); }
        .global-reminder-alert.priority-important { border-color: var(--warning); }
        .priority-urgent .global-reminder-icon { background: var(--danger-soft); color: var(--danger); }
        .priority-important .global-reminder-icon { background: var(--warning-soft); color: var(--warning-text); }
        .global-reminder-queue { background: var(--danger); }
        .global-reminder-btn-view { background: var(--primary); border-color: var(--primary); color: var(--on-accent); }
        .global-reminder-btn-view:hover { color: var(--on-accent); }

        /* Offline = warning, not brand red */
        .net-pill { color: var(--warning-text); }
        .net-pill::before { background: var(--warning); }

        /* Account menu: role = info, status = success, sign out = danger */
        .user-role-badge { background: var(--info-soft); color: var(--info); }
        .user-dropdown-item.text-danger { color: var(--danger); }
        .user-dropdown-item.text-danger .user-dropdown-item-icon { background: var(--danger-soft); color: var(--danger); }

        /* Flash messages */
        .flash-error { background: var(--danger-soft); border-color: var(--danger); color: var(--danger); }
        html[data-theme="dark"] .flash-error { color: var(--danger); }

        /* Bootstrap bits that should follow the semantic colours */
        .btn-danger { --bs-btn-bg: var(--danger); --bs-btn-border-color: var(--danger); --bs-btn-hover-bg: var(--danger); --bs-btn-hover-border-color: var(--danger); --bs-btn-color: #fff; font-weight: 700; }
        .btn-outline-danger { --bs-btn-color: var(--danger); --bs-btn-border-color: var(--danger); --bs-btn-hover-bg: var(--danger); --bs-btn-hover-border-color: var(--danger); --bs-btn-hover-color: #fff; font-weight: 700; }
        .text-danger { color: var(--danger) !important; }
        .text-success { color: var(--primary-d) !important; }
        .text-warning { color: var(--warning-text) !important; }
        .text-info { color: var(--info) !important; }
    </style>

    @stack('styles')
</head>

<body>

{{-- Inline icon sprite: shows even with no internet --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false"><defs>
    <symbol id="i-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></symbol>
    <symbol id="i-people" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><circle cx="17" cy="9" r="2.5"/><path d="M17 14.5a5 5 0 0 1 4.5 5"/></symbol>
    <symbol id="i-house" viewBox="0 0 24 24"><path d="M3 11 12 3l9 8M5 9.5V21h14V9.5M10 21v-6h4v6"/></symbol>
    <symbol id="i-pin" viewBox="0 0 24 24"><path d="M12 21s7-6.2 7-11.5A7 7 0 0 0 5 9.5C5 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></symbol>
    <symbol id="i-file" viewBox="0 0 24 24"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></symbol>
    <symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 3 4 6v6c0 4.5 3.2 8 8 9 4.8-1 8-4.5 8-9V6z"/><path d="M12 8v5M12 16.5v.01"/></symbol>
    <symbol id="i-phone" viewBox="0 0 24 24"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></symbol>
    <symbol id="i-qr" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20 14v.01M14 20h3M20 17v4"/></symbol>
    <symbol id="i-chart" viewBox="0 0 24 24"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></symbol>
    <symbol id="i-usergear" viewBox="0 0 24 24"><circle cx="10" cy="8" r="4"/><path d="M3 21a7 7 0 0 1 11-5.5"/><circle cx="18" cy="17" r="2.5"/><path d="M18 12.5v1.2M18 20.3v1.2M13.5 17h1.2M21.3 17h1.2"/></symbol>
    <symbol id="i-bell" viewBox="0 0 24 24"><path d="M6 16v-5a6 6 0 0 1 12 0v5l2 2H4z"/><path d="M10 21a2 2 0 0 0 4 0"/></symbol>
    <symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></symbol>
    <symbol id="i-menu" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></symbol>
    <symbol id="i-chev-r" viewBox="0 0 24 24"><path d="m9 6 6 6-6 6"/></symbol>
    <symbol id="i-chev-d" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></symbol>
    <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol>
    <symbol id="i-cal" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></symbol>
    <symbol id="i-check2" viewBox="0 0 24 24"><path d="m5 12 5 5 9-10"/></symbol>
    <symbol id="i-moon" viewBox="0 0 24 24"><path d="M20 14.5A8.5 8.5 0 1 1 9.5 4a7 7 0 0 0 10.5 10.5z"/></symbol>
    <symbol id="i-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M5 5l1.5 1.5M17.5 17.5 19 19M5 19l1.5-1.5M17.5 6.5 19 5"/></symbol>
    <symbol id="i-logout" viewBox="0 0 24 24"><path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5M16 8l4 4-4 4M20 12H9"/></symbol>
    <symbol id="i-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></symbol>
    <symbol id="i-shieldcheck" viewBox="0 0 24 24"><path d="M12 3 4 6v6c0 4.5 3.2 8 8 9 4.8-1 8-4.5 8-9V6z"/><path d="m8.5 12 2.5 2.5 4.5-5"/></symbol>
    <symbol id="i-alarm" viewBox="0 0 24 24"><circle cx="12" cy="13" r="7.5"/><path d="M12 9v4l2.5 1.5M4 5l3-2M20 5l-3-2"/></symbol>
    <symbol id="i-x" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></symbol>
    <symbol id="i-patch" viewBox="0 0 24 24"><path d="m12 2.5 2.5 1.8 3 .1.9 2.9 2.4 1.8-1 2.8 1 2.8-2.4 1.8-.9 2.9-3 .1L12 21.5l-2.5-1.8-3-.1-.9-2.9-2.4-1.8 1-2.8-1-2.8 2.4-1.8.9-2.9 3-.1z"/><path d="m8.5 12 2.5 2.5 4.5-5"/></symbol>
    <symbol id="i-box" viewBox="0 0 24 24"><path d="m21 8-9-5-9 5v8l9 5 9-5zM3 8l9 5 9-5M12 13v8"/></symbol>
    <symbol id="i-alert" viewBox="0 0 24 24"><path d="M12 3 2 20h20z"/><path d="M12 10v5M12 17.5v.01"/></symbol>
    <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
</defs></svg>


<!-- ============ SIDEBAR ============ -->

<aside class="sidebar" id="appSidebar">

    <div class="sidebar-brand">
        <div class="seal">
            <div class="seal-in">
                {{-- Watermelon fallback: shows if the logo file is missing or cannot load offline --}}
                <svg viewBox="0 0 40 40" aria-hidden="true">
                    <rect width="40" height="40" fill="#fff"/>
                    <path d="M4 17a16 16 0 0 0 32 0z" fill="#4fae4e"/>
                    <path d="M7.4 17a12.6 12.6 0 0 0 25.2 0z" fill="#e4f2d6"/>
                    <path d="M9.6 17a10.4 10.4 0 0 0 20.8 0z" fill="#ef6b78"/>
                    <ellipse cx="15.5" cy="22" rx="1.1" ry="1.9" transform="rotate(-20 15.5 22)" fill="#2a1a1d"/>
                    <ellipse cx="20" cy="25" rx="1.1" ry="1.9" fill="#2a1a1d"/>
                    <ellipse cx="24.5" cy="22" rx="1.1" ry="1.9" transform="rotate(20 24.5 22)" fill="#2a1a1d"/>
                </svg>
                <img src="{{ $logo }}" @if ($logo2x) srcset="{{ $logo }} 1x, {{ $logo2x }} 2x" @endif
                     width="2000" height="2000" alt="Barangay Poblacion Record Management System"
                     loading="eager" decoding="sync" fetchpriority="high" onerror="this.remove()">
            </div>
        </div>

        <div class="min-w-0">
            <div class="brand-name"><span>Barangay</span><span>Poblacion</span></div>
            <div class="brand-system">Record Management System</div>
        </div>
    </div>

    <nav class="sidebar-nav" aria-label="Main navigation">
        @foreach ($navGroups as $group)
            @if ($group['show'] && count($group['items']))
                <div class="sidebar-group">
                    <small class="sidebar-label">{{ $group['label'] }}</small>

                    @foreach ($group['items'] as $item)
                        @php $isActive = request()->routeIs(...$item['active']); @endphp
                        <a href="{{ route($item['route']) }}" class="sidebar-link {{ $isActive ? 'active' : '' }}" @if ($isActive) aria-current="page" @endif>
                            <svg class="i" aria-hidden="true"><use href="#i-{{ $item['icon'] }}"/></svg>
                            <span>{{ $item['text'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        @endforeach
    </nav>

    <div class="sidebar-foot">
        <svg class="i" aria-hidden="true"><use href="#i-patch"/></svg>
        <div><strong>Bani, Pangasinan</strong>Serving since 1769</div>
    </div>

</aside>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>


<!-- ============ MAIN CONTENT ============ -->

<div class="main-content">

    <nav class="topbar">

        <div class="tb-left">
            <button type="button" class="topbar-toggle" id="sidebarToggle" aria-label="Open menu" aria-controls="appSidebar" aria-expanded="false">
                <svg class="i" style="width:20px;height:20px" aria-hidden="true"><use href="#i-menu"/></svg>
            </button>

            <div class="topbar-brand">
                <span class="mini">
                    <svg viewBox="0 0 40 40" aria-hidden="true"><rect width="40" height="40" fill="#fff"/><path d="M4 17a16 16 0 0 0 32 0z" fill="#4fae4e"/><path d="M9.6 17a10.4 10.4 0 0 0 20.8 0z" fill="#ef6b78"/></svg>
                    <img src="{{ $logo }}" alt="" width="76" height="76" onerror="this.remove()">
                </span>
                <span>Poblacion</span>
            </div>

            {{-- ============ GLOBAL SEARCH ============ --}}
            <div class="gsearch" id="gSearch">
                <label class="gsearch-field" for="gSearchInput">
                    <svg class="i" aria-hidden="true"><use href="#i-search"/></svg>
                    <input type="search" id="gSearchInput" placeholder="Search residents, documents, records..." aria-label="Search"
                           autocomplete="off" autocapitalize="off" spellcheck="false"
                           role="combobox" aria-expanded="false" aria-controls="gSearchPanel" aria-autocomplete="list">
                    <span class="gsearch-hint" aria-hidden="true">Ctrl K</span>
                    <span class="gsearch-spin" aria-hidden="true"></span>
                    <button type="button" class="gsearch-clear" id="gSearchClear" aria-label="Clear search" tabindex="-1">
                        <svg class="i" aria-hidden="true"><use href="#i-x"/></svg>
                    </button>
                </label>

                <div class="gsearch-panel" id="gSearchPanel" role="listbox" aria-label="Search results"></div>
            </div>
        </div>

        <div class="tb-right">

            <span class="net-pill" id="netPill" role="status" aria-live="polite">Offline</span>

            {{-- ============ NOTIFICATIONS + MANUAL REMINDERS (redesigned) ============ --}}
            <div class="dd">
                <button type="button"
                    class="notification-button {{ $bellNotificationCount > 0 ? 'has-unread' : '' }}"
                    id="notificationDropdown" data-dd="notificationMenu"
                    aria-expanded="false" aria-haspopup="true" aria-label="Notifications" title="Notifications">
                    <svg class="i" style="width:21px;height:21px" aria-hidden="true"><use href="#i-bell"/></svg>
                    @if ($bellNotificationCount > 0)
                        <span class="notification-badge">{{ $bellNotificationCount > 99 ? '99+' : $bellNotificationCount }}</span>
                    @endif
                </button>

                <div class="dd-menu notification-menu" id="notificationMenu" data-filter="all" aria-labelledby="notificationDropdown">

                    {{-- Header --}}
                    <div class="notification-menu-header">
                        <div>
                            <div class="notification-menu-title">Notifications</div>
                            <div class="notification-menu-subtitle">{{ now()->format('l, F j') }}</div>
                        </div>
                        <div class="nb-head-right">
                            @if ($bellNotificationCount > 0)
                                <span class="notification-count-label">{{ $bellNotificationCount }} {{ $bellNotificationCount === 1 ? 'item' : 'items' }}</span>
                            @endif
                            @if ($hasBellNotifications)
                                <button type="button" class="notification-clear-all" id="notificationClearAll"
                                    data-url="{{ route('notifications.clear-all') }}">
                                    <svg class="i" aria-hidden="true"><use href="#i-check"/></svg>
                                    Clear all
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Tabs --}}
                    @if ($hasBellNotifications)
                        <div class="nb-tabs" id="nbTabs" role="tablist" aria-label="Filter notifications">
                            <button type="button" class="nb-tab" role="tab" aria-selected="true" data-nb-tab="all">All <span class="nb-tab-n" data-nb-count="all">{{ $bellNotificationCount }}</span></button>
                            <button type="button" class="nb-tab" role="tab" aria-selected="false" data-nb-tab="today">Today <span class="nb-tab-n" data-nb-count="today">{{ $nbCounts['today'] ?? 0 }}</span></button>
                            <button type="button" class="nb-tab" role="tab" aria-selected="false" data-nb-tab="tomorrow">Tomorrow <span class="nb-tab-n" data-nb-count="tomorrow">{{ $nbCounts['tomorrow'] ?? 0 }}</span></button>
                        </div>
                    @endif

                    {{-- List --}}
                    <div class="notification-list" id="nbList">
                        @foreach ($bellSections as $section)
                            @if ($section['items']->isNotEmpty())
                                <div class="notification-section-label notification-section-{{ $section['cls'] }}" data-section="{{ $section['cls'] }}">
                                    <span>{{ $section['label'] }}</span>
                                    <span class="notification-section-line"></span>
                                </div>

                                @foreach ($section['items'] as $item)
                                    @php $tag = !empty($item['url']) ? 'a' : 'div'; @endphp
                                    <div class="notification-item-row" data-notification-row data-section="{{ $section['cls'] }}" data-key="{{ $item['key'] }}">
                                        <{{ $tag }} @if ($tag === 'a') href="{{ $item['url'] }}" @endif class="notification-item notification-item-{{ $item['prio'] }}">
                                            <span class="notification-item-icon"><svg class="i" aria-hidden="true"><use href="#i-{{ $item['icon'] }}"/></svg></span>
                                            <span class="notification-item-content">
                                                <span class="notification-item-title">
                                                    {{ $item['title'] }}
                                                    @if (isset($nbPrioLabel[$item['prio']]))
                                                        <span class="nb-tag nb-tag-{{ $item['prio'] }}">{{ $nbPrioLabel[$item['prio']] }}</span>
                                                    @endif
                                                </span>
                                                @if (!empty($item['msg']))
                                                    <span class="notification-item-message">{{ $item['msg'] }}</span>
                                                @endif
                                                @if (!empty($item['desc']))
                                                    <span class="notification-item-description">{{ $item['desc'] }}</span>
                                                @endif
                                                @if (!empty($item['time']) || !empty($item['loc']))
                                                    <span class="notification-item-meta">
                                                        @if (!empty($item['time']))
                                                            <span><svg class="i" aria-hidden="true"><use href="#i-clock"/></svg>{{ $item['time'] }}</span>
                                                        @endif
                                                        @if (!empty($item['loc']))
                                                            <span><svg class="i" aria-hidden="true"><use href="#i-pin"/></svg>{{ $item['loc'] }}</span>
                                                        @endif
                                                    </span>
                                                @endif
                                            </span>
                                        </{{ $tag }}>
                                        <button type="button" class="notification-clear-button"
                                            data-clear-notification data-key="{{ $item['key'] }}"
                                            data-url="{{ route('notifications.clear') }}"
                                            aria-label="Clear this notification" title="Clear">
                                            <svg class="i" aria-hidden="true"><use href="#i-x"/></svg>
                                            Clear
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                        @endforeach

                        <div class="nb-filter-empty" id="nbFilterEmpty">Nothing here for this day.</div>
                    </div>

                    @if (!$hasBellNotifications)
                        <div class="notification-empty">
                            <div class="notification-empty-icon"><svg class="i" aria-hidden="true"><use href="#i-check"/></svg></div>
                            <div class="notification-empty-title">All caught up</div>
                            <div class="notification-empty-text">There are no relief operations, complaint hearings, or scheduled reminders for today or tomorrow.</div>
                        </div>
                    @endif

                    <div class="notification-feedback" id="notificationFeedback" role="status" aria-live="polite"></div>

                    @if ($canManageRecords && \Illuminate\Support\Facades\Route::has('reminders.index'))
                        <div class="notification-quick-action">
                            <a href="{{ route('reminders.index') }}" class="notification-manage-link">
                                <svg class="i" aria-hidden="true"><use href="#i-cal"/></svg>
                                Open Schedule &amp; Reminders
                                <svg class="i" aria-hidden="true"><use href="#i-chev-r"/></svg>
                            </a>
                        </div>
                    @endif

                </div>
            </div>


            {{-- ============ USER PROFILE ============ --}}
            <div class="dd">
                <button type="button" class="user-profile-trigger" id="userProfileDropdown" data-dd="userMenu" aria-expanded="false" aria-haspopup="true">
                    <span class="user-profile-avatar">
                        @if ($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="{{ $displayName }}">
                        @else
                            <svg class="i" aria-hidden="true"><use href="#i-user"/></svg>
                        @endif
                    </span>

                    <span class="user-profile-text">
                        <span class="user-profile-name">{{ $displayName }}</span>
                        <span class="user-profile-role">{{ ucfirst($user?->role ?? 'User') }}</span>
                    </span>

                    <svg class="i user-profile-chevron" aria-hidden="true"><use href="#i-chev-d"/></svg>
                </button>

                <div class="dd-menu user-dropdown-menu" id="userMenu" aria-labelledby="userProfileDropdown">

                    <div class="user-dropdown-header">
                        <div class="user-dropdown-avatar">
                            @if ($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ $displayName }}">
                            @else
                                <svg class="i" aria-hidden="true"><use href="#i-user"/></svg>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <div class="user-dropdown-eyebrow">Signed in as</div>
                            <div class="user-dropdown-name">{{ $displayName }}</div>

                            <span class="user-role-badge">
                                <svg class="i" style="width:13px;height:13px" aria-hidden="true"><use href="#i-shieldcheck"/></svg>
                                {{ ucfirst($user?->role ?? 'User') }}
                            </span>

                            @if ($user?->isActive())
                                <span class="user-status-badge"><span class="user-status-dot"></span>Active account</span>
                            @endif
                        </div>
                    </div>

                    <div class="user-dropdown-section-title">Account</div>

                    @if ($canAccessSettings)
                        <a href="{{ route('settings.index') }}" class="user-dropdown-item">
                            <span class="user-dropdown-item-icon"><svg class="i" aria-hidden="true"><use href="#i-usergear"/></svg></span>
                            <span><strong>Personal Settings</strong><small>Avatar, nickname &amp; account preferences</small></span>
                        </a>
                    @endif

                    <div class="user-dropdown-section-title" style="margin-top:8px">Preferences</div>

                    <button type="button" class="theme-switch-row" id="topbarThemeToggle" aria-pressed="{{ $initialTheme === 'dark' ? 'true' : 'false' }}">
                        <span class="theme-switch-icon"><svg class="i" id="topbarThemeIcon" aria-hidden="true"><use href="#i-moon"/></svg></span>
                        <span class="theme-switch-copy">
                            <strong id="topbarThemeLabel">Dark Mode</strong>
                            <small>Change the interface appearance</small>
                        </span>
                        <span class="theme-switch-indicator"></span>
                    </button>

                    <div class="user-dropdown-divider"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="user-dropdown-item text-danger">
                            <span class="user-dropdown-item-icon"><svg class="i" aria-hidden="true"><use href="#i-logout"/></svg></span>
                            <span><strong>Sign out</strong><small>End your current session</small></span>
                        </button>
                    </form>

                </div>
            </div>

        </div>

    </nav>


    <main class="page-content">

        @if (session('success'))
            <div class="flash flash-success" role="alert">
                <svg class="i" style="margin-top:2px" aria-hidden="true"><use href="#i-check"/></svg>
                <div>{{ session('success') }}</div>
                <button type="button" class="flash-x" data-flash-close aria-label="Close"><svg class="i" aria-hidden="true"><use href="#i-x"/></svg></button>
            </div>
        @endif

        @if (session('error'))
            <div class="flash flash-error" role="alert">
                <svg class="i" style="margin-top:2px" aria-hidden="true"><use href="#i-alert"/></svg>
                <div>{{ session('error') }}</div>
                <button type="button" class="flash-x" data-flash-close aria-label="Close"><svg class="i" aria-hidden="true"><use href="#i-x"/></svg></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="flash flash-error" role="alert">
                <svg class="i" style="margin-top:2px" aria-hidden="true"><use href="#i-alert"/></svg>
                <div>
                    <div style="font-weight:800">Please correct the following:</div>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="flash-x" data-flash-close aria-label="Close"><svg class="i" aria-hidden="true"><use href="#i-x"/></svg></button>
            </div>
        @endif

        @yield('content')

    </main>

</div>


{{-- ============ GLOBAL REMINDER POPUP (outside page content so it shows on every page) ============ --}}

<div id="globalReminderAlert" class="global-reminder-alert" role="alert" aria-live="assertive" aria-atomic="true">

    <div class="global-reminder-top">
        <div class="global-reminder-icon"><svg class="i" aria-hidden="true"><use href="#i-alarm"/></svg></div>

        <div class="global-reminder-content">
            <div class="global-reminder-label">Reminder</div>
            <div class="global-reminder-title" id="globalReminderTitle">Scheduled Reminder</div>
            <div class="global-reminder-type" id="globalReminderType">Schedule &amp; Reminders</div>
        </div>

        <span class="global-reminder-queue" id="globalReminderQueue">1</span>

        <button type="button" class="global-reminder-close" id="globalReminderClose" aria-label="Dismiss reminder" title="Dismiss">
            <svg class="i" aria-hidden="true"><use href="#i-x"/></svg>
        </button>
    </div>

    <div class="global-reminder-details">
        <div class="global-reminder-detail"><svg class="i" aria-hidden="true"><use href="#i-clock"/></svg><span id="globalReminderTime">Scheduled time</span></div>
        <div class="global-reminder-detail" id="globalReminderLocationRow"><svg class="i" aria-hidden="true"><use href="#i-pin"/></svg><span id="globalReminderLocation">Location</span></div>
    </div>

    <div class="global-reminder-notes" id="globalReminderNotes" style="display:none"></div>

    <div class="global-reminder-actions">
        <button type="button" class="global-reminder-btn global-reminder-btn-dismiss" id="globalReminderDismiss">
            <svg class="i" aria-hidden="true"><use href="#i-check2"/></svg> Dismiss
        </button>
        <a href="#" class="global-reminder-btn global-reminder-btn-view" id="globalReminderView">
            <svg class="i" aria-hidden="true"><use href="#i-cal"/></svg> View Reminder
        </a>
    </div>

</div>

<div class="ep-toast" id="epToast" role="status" aria-live="polite"></div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Page scripts (@push('scripts')) print here, right after Bootstrap --}}
@stack('scripts')


<!-- ================================================================
     SIDEBAR · MENUS · THEME · OFFLINE (none of this needs Bootstrap)
     ================================================================ -->

<script>
(function () {

    const body = document.body;
    const $ = (id) => document.getElementById(id);

    /* ---------- Mobile sidebar ---------- */
    const toggle = $('sidebarToggle');
    const setSidebar = (open) => {
        body.classList.toggle('sidebar-open', open);
        if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    };
    if (toggle) toggle.addEventListener('click', () => setSidebar(!body.classList.contains('sidebar-open')));
    if ($('sidebarBackdrop')) $('sidebarBackdrop').addEventListener('click', () => setSidebar(false));

    /* ---------- Dropdown menus (notifications + profile) ---------- */
    const triggers = Array.from(document.querySelectorAll('[data-dd]'));
    const closeMenus = (except) => triggers.forEach(t => {
        if (t === except) return;
        t.setAttribute('aria-expanded', 'false');
        const m = $(t.dataset.dd); if (m) m.classList.remove('show');
    });
    triggers.forEach(t => t.addEventListener('click', (e) => {
        e.stopPropagation();
        const menu = $(t.dataset.dd);
        const open = !menu.classList.contains('show');
        closeMenus(open ? t : null);
        menu.classList.toggle('show', open);
        t.setAttribute('aria-expanded', open ? 'true' : 'false');
    }));
    document.addEventListener('click', (e) => { if (!e.target.closest('.dd-menu')) closeMenus(null); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { setSidebar(false); closeMenus(null); } });

    /* ---------- Notification clearing (does not mark items as read) ---------- */
    const notificationMenu = $('notificationMenu');
    const notificationFeedback = $('notificationFeedback');
    const clearAllButton = $('notificationClearAll');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const updateNotificationCount = () => {
        if (!notificationMenu) return;

        const rows = Array.from(notificationMenu.querySelectorAll('[data-notification-row]'));
        const count = rows.length;
        let badge = document.querySelector('.notification-badge');

        if (!badge && count > 0) {
            badge = document.createElement('span');
            badge.className = 'notification-badge';
            $('notificationDropdown')?.appendChild(badge);
        }

        if (badge) {
            badge.textContent = count > 99 ? '99+' : String(count);
            badge.hidden = count === 0;
        }

        const countLabel = notificationMenu.querySelector('.notification-count-label');
        if (countLabel) {
            countLabel.textContent = count + (count === 1 ? ' item' : ' items');
            countLabel.hidden = count === 0;
        }

        notificationMenu.querySelectorAll('.notification-section-label').forEach(label => {
            let sibling = label.nextElementSibling;
            let hasRows = false;

            while (sibling && !sibling.classList.contains('notification-section-label')) {
                if (sibling.matches('[data-notification-row]')) {
                    hasRows = true;
                    break;
                }
                sibling = sibling.nextElementSibling;
            }

            if (!hasRows) label.remove();
        });

        if (count === 0 && !notificationMenu.querySelector('.notification-empty')) {
            const empty = document.createElement('div');
            empty.className = 'notification-empty';
            empty.innerHTML = '<div class="notification-empty-icon">✓</div><div class="notification-empty-title">All caught up</div><div class="notification-empty-text">There are no uncleared notifications for today or tomorrow.</div>';

            const toolbar = notificationMenu.querySelector('.notification-toolbar');
            if (toolbar) toolbar.remove();

            notificationMenu.insertBefore(empty, notificationFeedback || notificationMenu.firstChild);
        }

        if (clearAllButton) clearAllButton.disabled = count === 0;
    };

    const postNotificationClear = async (url, payload) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            let message = 'Unable to clear notification(s). Please try again.';
            try {
                const data = await response.json();
                if (data.message) message = data.message;
            } catch (e) {}
            throw new Error(message);
        }

        return response.json();
    };

    notificationMenu?.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-clear-notification]');
        if (!button) return;

        event.preventDefault();
        event.stopPropagation();

        const row = button.closest('[data-notification-row]');
        const key = button.dataset.key;
        if (!row || !key || button.disabled) return;

        button.disabled = true;
        if (notificationFeedback) notificationFeedback.textContent = 'Clearing notification…';

        try {
            await postNotificationClear(button.dataset.url, { key });
            row.remove();
            updateNotificationCount();
            if (notificationFeedback) notificationFeedback.textContent = 'Notification cleared.';
        } catch (error) {
            button.disabled = false;
            if (notificationFeedback) notificationFeedback.textContent = error.message;
        }
    });

    clearAllButton?.addEventListener('click', async () => {
        const rows = Array.from(notificationMenu.querySelectorAll('[data-notification-row]'));
        const keys = rows.map(row => row.dataset.key).filter(Boolean);
        if (!keys.length || clearAllButton.disabled) return;

        clearAllButton.disabled = true;
        if (notificationFeedback) notificationFeedback.textContent = 'Clearing notifications…';

        try {
            // The controller accepts up to 200 keys per request.
            for (let index = 0; index < keys.length; index += 200) {
                await postNotificationClear(clearAllButton.dataset.url, {
                    keys: keys.slice(index, index + 200)
                });
            }

            rows.forEach(row => row.remove());
            updateNotificationCount();
            if (notificationFeedback) notificationFeedback.textContent = 'All displayed notifications cleared.';
        } catch (error) {
            clearAllButton.disabled = false;
            if (notificationFeedback) notificationFeedback.textContent = error.message;
        }
    });

    /* ---------- Bell tabs (All / Today / Tomorrow) + live tab counts ---------- */
    (function () {
        const menu = $('notificationMenu');
        const list = $('nbList');
        const tabs = $('nbTabs');
        if (!menu || !list) return;

        const emptyMsg = $('nbFilterEmpty');
        let filter = 'all';

        const sync = () => {
            const rows = Array.from(list.querySelectorAll('[data-notification-row]'));
            const n = { all: rows.length, today: 0, tomorrow: 0 };
            rows.forEach(r => { if (n[r.dataset.section] !== undefined) n[r.dataset.section]++; });

            document.querySelectorAll('[data-nb-count]').forEach(b => { b.textContent = n[b.dataset.nbCount] ?? 0; });

            if (tabs) tabs.hidden = n.all === 0;
            if (clearAllButton) clearAllButton.hidden = n.all === 0;
            if (n.all === 0 && filter !== 'all') setFilter('all');
            if (emptyMsg) emptyMsg.classList.toggle('show', n.all > 0 && filter !== 'all' && n[filter] === 0);
            $('notificationDropdown')?.classList.toggle('has-unread', n.all > 0);
        };

        const setFilter = (f) => {
            filter = f;
            menu.dataset.filter = f;
            tabs?.querySelectorAll('[data-nb-tab]').forEach(t => t.setAttribute('aria-selected', t.dataset.nbTab === f ? 'true' : 'false'));
            sync();
        };

        tabs?.addEventListener('click', (e) => {
            const t = e.target.closest('[data-nb-tab]');
            if (t) setFilter(t.dataset.nbTab);
        });

        new MutationObserver(sync).observe(list, { childList: true, subtree: true });
        sync();
    })();

    /* ---------- Flash messages ---------- */
    document.querySelectorAll('[data-flash-close]').forEach(b => b.addEventListener('click', () => b.closest('.flash').remove()));

    /* ---------- Offline awareness ---------- */
    const pill = $('netPill'), toast = $('epToast');
    let toastTimer;
    const say = (msg, sticky) => {
        toast.textContent = msg; toast.classList.add('show');
        clearTimeout(toastTimer);
        if (!sticky) toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
    };
    const net = (online, announce) => {
        pill.classList.toggle('show', !online);
        if (!announce) return;
        online ? say('Back online.', false) : say('You are offline. Showing the last loaded data.', true);
    };
    net(navigator.onLine, !navigator.onLine);
    window.addEventListener('online', () => net(true, true));
    window.addEventListener('offline', () => net(false, true));

    /* ---------- Theme (account is the source of truth) ---------- */
    try { localStorage.removeItem('barangay-theme'); } catch (e) {}

    const root = document.documentElement;
    const btn = $('topbarThemeToggle'), label = $('topbarThemeLabel'), icon = $('topbarThemeIcon');

    const paint = (theme) => {
        const dark = theme === 'dark';
        root.setAttribute('data-theme', theme);
        if (btn) btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
        if (label) label.textContent = dark ? 'Light Mode' : 'Dark Mode';
        if (icon) icon.firstElementChild.setAttribute('href', dark ? '#i-sun' : '#i-moon');
        document.dispatchEvent(new CustomEvent('themechange', { detail: { theme } }));
    };

    window.EPTheme = {
        current: () => root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light',
        async set(theme) {
            const previous = this.current();
            if (theme === previous) return true;
            paint(theme);
            try {
                const res = await fetch(@json(route('settings.theme.update')), {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ theme })
                });
                if (!res.ok) throw new Error('save failed');
                return true;
            } catch (e) {
                paint(previous);
                return false;
            }
        }
    };

    paint(window.EPTheme.current());
    if (btn) btn.addEventListener('click', () => window.EPTheme.set(window.EPTheme.current() === 'dark' ? 'light' : 'dark'));

})();
</script>


<!-- ================================================================
     GLOBAL SEARCH
     - Pages and quick actions: matched instantly in the browser
     - Records: fetched from the global-search route (debounced, cancels stale requests)
     - Keyboard: Ctrl/Cmd+K or "/" to focus, Up/Down to move, Enter to open, Esc to close
     ================================================================ -->

<script>
(function () {
    'use strict';

    const wrap = document.getElementById('gSearch');
    const input = document.getElementById('gSearchInput');
    const panel = document.getElementById('gSearchPanel');
    const clearBtn = document.getElementById('gSearchClear');
    if (!wrap || !input || !panel) return;

    const PAGES = @json($searchPages);
    const ACTIONS = @json($searchActions);
    const SEARCH_URL = @json($searchUrl);
    const MIN_CHARS = 2;
    const DEBOUNCE = 250;
    const SVG_NS = 'http://www.w3.org/2000/svg';

    let items = [];          // flat list of selectable rows (for keyboard)
    let active = -1;
    let remote = [];         // last record results
    let remoteState = 'idle'; // idle | loading | done | error | offline
    let remoteFor = '';      // query that remote results belong to
    let timer = null, controller = null, seq = 0;
    const cache = new Map();

    /* ---------- helpers ---------- */
    const norm = (s) => String(s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    const terms = (q) => norm(q).split(/\s+/).filter(Boolean);
    const open = () => { panel.classList.add('show'); input.setAttribute('aria-expanded', 'true'); };
    const close = () => { panel.classList.remove('show'); input.setAttribute('aria-expanded', 'false'); active = -1; };

    const icon = (name) => {
        const s = document.createElementNS(SVG_NS, 'svg');
        s.setAttribute('class', 'i'); s.setAttribute('aria-hidden', 'true');
        const u = document.createElementNS(SVG_NS, 'use');
        u.setAttribute('href', '#i-' + name);
        s.appendChild(u);
        return s;
    };
    const el = (tag, cls, text) => {
        const n = document.createElement(tag);
        if (cls) n.className = cls;
        if (text !== undefined) n.textContent = text;
        return n;
    };

    /* Highlight typed words inside a title (safe: built with text nodes, never innerHTML) */
    const highlight = (text, ts) => {
        const frag = document.createDocumentFragment();
        if (!ts.length) { frag.appendChild(document.createTextNode(text)); return frag; }
        const plain = norm(text);
        const marks = new Array(text.length).fill(false);
        if (plain.length === text.length) {
            ts.forEach(t => {
                let from = 0, at;
                while ((at = plain.indexOf(t, from)) !== -1) {
                    for (let k = at; k < at + t.length; k++) marks[k] = true;
                    from = at + t.length;
                }
            });
        }
        let buf = '', on = false;
        const flush = () => {
            if (!buf) return;
            if (on) { const m = document.createElement('mark'); m.textContent = buf; frag.appendChild(m); }
            else frag.appendChild(document.createTextNode(buf));
            buf = '';
        };
        for (let k = 0; k < text.length; k++) {
            if (marks[k] !== on) { flush(); on = marks[k]; }
            buf += text[k];
        }
        flush();
        return frag;
    };

    /* Score a local entry: every typed word must be found; earlier / title matches rank higher */
    const score = (entry, ts) => {
        const title = norm(entry.title), hay = title + ' ' + norm(entry.sub) + ' ' + norm(entry.kw);
        let total = 0;
        for (const t of ts) {
            if (!hay.includes(t)) return -1;
            const ti = title.indexOf(t);
            total += ti === 0 ? 30 : title.includes(' ' + t) ? 22 : ti > 0 ? 14 : 6;
        }
        return total;
    };

    const localMatches = (list, ts, limit) => list
        .map(e => ({ e, s: score(e, ts) })).filter(x => x.s >= 0)
        .sort((a, b) => b.s - a.s).slice(0, limit).map(x => x.e);

    /* ---------- rendering ---------- */
    const row = (entry, ts, isAction) => {
        const a = el('a', 'gs-item' + (isAction ? ' is-action' : ''));
        a.href = entry.url; a.setAttribute('role', 'option');
        const ico = el('span', 'gs-ico'); ico.appendChild(icon(isAction ? 'plus' : entry.icon));
        const txt = el('span', 'gs-text');
        const title = el('span', 'gs-title'); title.appendChild(highlight(entry.title, ts));
        txt.appendChild(title);
        if (entry.sub) txt.appendChild(el('span', 'gs-sub', entry.sub));
        a.append(ico, txt, el('span', 'gs-enter', '↵'));
        a.addEventListener('mousemove', () => setActive(items.indexOf(a), false));
        return a;
    };

    const section = (label, entries, ts, isAction) => {
        if (!entries.length) return;
        panel.appendChild(el('div', 'gs-group', label));
        entries.forEach(e => { const r = row(e, ts, isAction); items.push(r); panel.appendChild(r); });
    };

    const state = (iconName, title, text) => {
        const s = el('div', 'gs-state');
        const i = el('div', 'gs-state-ico'); i.appendChild(icon(iconName));
        s.append(i, el('div', 'gs-state-title', title), el('div', 'gs-state-text', text));
        panel.appendChild(s);
    };

    const note = (iconName, text) => {
        const n = el('div', 'gs-note'); n.appendChild(icon(iconName)); n.appendChild(el('span', '', text));
        panel.appendChild(n);
    };

    const footer = () => {
        const f = el('div', 'gs-foot');
        [['↑↓', 'navigate'], ['↵', 'open'], ['esc', 'close']].forEach(([k, t]) => {
            const s = el('span'); const kb = el('kbd', '', k); s.append(kb, document.createTextNode(' ' + t)); f.appendChild(s);
        });
        panel.appendChild(f);
    };

    const render = () => {
        const q = input.value.trim();
        const ts = terms(q);
        panel.replaceChildren();
        items = [];

        if (q.length < MIN_CHARS) {
            /* Nothing typed: show shortcuts */
            section('Quick actions', ACTIONS.slice(0, 4), [], true);
            section('Jump to', PAGES.slice(0, 6), [], false);
            if (!items.length) state('search', 'Search the system', 'Type at least ' + MIN_CHARS + ' characters to search.');
            else if (q.length === 1) note('search', 'Keep typing to search residents, households and documents.');
            footer();
            return setActive(items.length ? 0 : -1, false);
        }

        section('Pages', localMatches(PAGES, ts, 4), ts, false);
        section('Quick actions', localMatches(ACTIONS, ts, 3), ts, true);

        /* Records, grouped in the order the server sent them */
        if (remoteFor === q && remote.length) {
            const groups = {};
            remote.forEach(r => (groups[r.group] = groups[r.group] || []).push(r));
            Object.keys(groups).forEach(g => section(g, groups[g], ts, false));
        }

        if (SEARCH_URL) {
            if (remoteState === 'offline') note('alert', 'You are offline. Only pages and actions are searchable.');
            else if (remoteState === 'error') note('alert', 'Could not search records right now. Try again in a moment.');
            else if (remoteState === 'loading' && !(remoteFor === q && remote.length)) note('clock', 'Searching records…');
        }

        const searching = SEARCH_URL && remoteState === 'loading';
        if (!items.length && !searching && remoteState !== 'error' && remoteState !== 'offline') {
            state('search', 'No results for “' + q + '”', 'Check the spelling, or try a name, household number or reference number.');
        }

        footer();
        setActive(items.length ? 0 : -1, false);
    };

    const setActive = (idx, scroll) => {
        items.forEach((n, k) => { n.classList.toggle('active', k === idx); n.setAttribute('aria-selected', k === idx ? 'true' : 'false'); });
        active = idx;
        if (idx >= 0 && scroll !== false) items[idx].scrollIntoView({ block: 'nearest' });
    };

    /* ---------- remote record search ---------- */
    const setLoading = (on) => wrap.classList.toggle('loading', on);

    const fetchRecords = async (q) => {
        if (!SEARCH_URL || q.length < MIN_CHARS) return;

        if (!navigator.onLine) { remoteState = 'offline'; remote = []; remoteFor = q; setLoading(false); return render(); }

        const key = norm(q);
        if (cache.has(key)) {
            remote = cache.get(key); remoteFor = q; remoteState = 'done'; setLoading(false);
            return render();
        }

        if (controller) controller.abort();
        controller = new AbortController();
        const mine = ++seq;
        remoteState = 'loading'; setLoading(true);

        try {
            const res = await fetch(SEARCH_URL + '?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin', signal: controller.signal
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            if (mine !== seq) return;                    // a newer search replaced this one
            remote = Array.isArray(data.results) ? data.results : [];
            remoteFor = q; remoteState = 'done';
            cache.set(key, remote);
            if (cache.size > 40) cache.delete(cache.keys().next().value);
        } catch (e) {
            if (e.name === 'AbortError') return;
            if (mine !== seq) return;
            remote = []; remoteFor = q; remoteState = 'error';
        }
        setLoading(false);
        if (input.value.trim() === q) render();
    };

    const onInput = () => {
        const q = input.value.trim();
        wrap.classList.toggle('has-value', input.value.length > 0);
        clearTimeout(timer);
        if (controller) controller.abort();
        seq++;
        remote = []; remoteFor = ''; setLoading(false);

        if (q.length >= MIN_CHARS && SEARCH_URL) {
            remoteState = 'loading';
            timer = setTimeout(() => fetchRecords(q), DEBOUNCE);
        } else {
            remoteState = 'idle';
        }
        open(); render();
    };

    /* ---------- events ---------- */
    input.addEventListener('input', onInput);
    input.addEventListener('focus', () => { open(); render(); });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault(); if (!panel.classList.contains('show')) { open(); render(); }
            if (items.length) setActive((active + 1) % items.length);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (items.length) setActive((active - 1 + items.length) % items.length);
        } else if (e.key === 'Enter') {
            if (active >= 0 && items[active]) { e.preventDefault(); window.location.href = items[active].href; }
            else e.preventDefault();
        } else if (e.key === 'Escape') {
            if (input.value) { input.value = ''; onInput(); }
            else { close(); input.blur(); }
            e.stopPropagation();
        } else if (e.key === 'Tab') {
            close();
        }
    });

    clearBtn.addEventListener('mousedown', (e) => e.preventDefault());
    clearBtn.addEventListener('click', () => { input.value = ''; onInput(); input.focus(); });

    document.addEventListener('mousedown', (e) => { if (!wrap.contains(e.target)) close(); });

    /* Ctrl/Cmd+K, or "/" when not typing in a field */
    document.addEventListener('keydown', (e) => {
        const tag = (document.activeElement && document.activeElement.tagName || '').toLowerCase();
        const typing = ['input', 'textarea', 'select'].includes(tag) || (document.activeElement && document.activeElement.isContentEditable);
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); input.focus(); input.select(); }
        else if (e.key === '/' && !typing && !e.ctrlKey && !e.metaKey && !e.altKey) { e.preventDefault(); input.focus(); input.select(); }
    });

    window.addEventListener('online', () => { if (input.value.trim().length >= MIN_CHARS) { cache.clear(); onInput(); } });
    window.addEventListener('pageshow', () => { wrap.classList.toggle('has-value', input.value.length > 0); });
})();
</script>


<!-- ================================================================
     GLOBAL REMINDER ENGINE
     ================================================================ -->

<script>
(function () {
    'use strict';

    const box = document.getElementById('globalReminderAlert');
    if (!box) return;

    const $ = (id) => document.getElementById(id);
    const el = {
        title: $('globalReminderTitle'), type: $('globalReminderType'), time: $('globalReminderTime'),
        locRow: $('globalReminderLocationRow'), loc: $('globalReminderLocation'), notes: $('globalReminderNotes'),
        view: $('globalReminderView'), dismiss: $('globalReminderDismiss'), close: $('globalReminderClose'), queue: $('globalReminderQueue')
    };

    const DUE_URL = @json(route('reminders.due'));
    const KEY = 'epoblacion.reminders.dismissed.v1';
    const POLL = 30000;

    let queue = [], current = null, audio = null, busy = false;

    /* ---- dismissed keys (kept 7 days) ---- */
    const read = () => { try { const p = JSON.parse(localStorage.getItem(KEY) || '{}'); return p && typeof p === 'object' ? p : {}; } catch (e) { return {}; } };
    const write = (d) => { try { localStorage.setItem(KEY, JSON.stringify(d)); } catch (e) {} };
    const isDismissed = (k) => !!k && !!read()[k];
    const markDismissed = (k) => {
        if (!k) return;
        const d = read(); d[k] = Date.now();
        const cutoff = Date.now() - 7 * 24 * 60 * 60 * 1000;
        Object.keys(d).forEach(x => { if (Number(d[x]) < cutoff) delete d[x]; });
        write(d);
    };

    /* ---- sound (Web Audio, no file needed) ---- */
    const prepAudio = () => {
        try {
            if (!audio) { const A = window.AudioContext || window.webkitAudioContext; if (!A) return; audio = new A(); }
            if (audio.state === 'suspended') audio.resume().catch(() => {});
        } catch (e) {}
    };
    const beep = () => {
        try {
            if (!audio) return;
            prepAudio();
            const t = audio.currentTime, o = audio.createOscillator(), g = audio.createGain();
            o.type = 'sine';
            o.frequency.setValueAtTime(880, t);
            o.frequency.exponentialRampToValueAtTime(1320, t + 0.12);
            g.gain.setValueAtTime(0.0001, t);
            g.gain.exponentialRampToValueAtTime(0.12, t + 0.015);
            g.gain.exponentialRampToValueAtTime(0.0001, t + 0.28);
            o.connect(g); g.connect(audio.destination);
            o.start(t); o.stop(t + 0.3);
        } catch (e) {}
    };
    document.addEventListener('pointerdown', prepAudio, { once: true, passive: true });
    document.addEventListener('keydown', prepAudio, { once: true });

    /* ---- popup ---- */
    const badge = () => {
        if (!el.queue) return;
        const n = queue.length;
        el.queue.textContent = n > 99 ? '99+' : String(n);
        el.queue.classList.toggle('show', n > 0);
    };

    const show = (r) => {
        current = r;
        const p = String(r.priority || 'normal').toLowerCase();
        box.classList.remove('priority-normal', 'priority-important', 'priority-urgent');
        box.classList.add('priority-' + p);

        el.title.textContent = r.title || 'Scheduled Reminder';
        el.type.textContent = r.type || 'Schedule & Reminders';
        el.time.textContent = r.scheduled_at_label || r.notification_at_label || 'Scheduled time';

        if (r.location) { el.loc.textContent = r.location; el.locRow.style.display = 'flex'; } else { el.locRow.style.display = 'none'; }
        if (r.notes) { el.notes.textContent = r.notes; el.notes.style.display = 'block'; } else { el.notes.textContent = ''; el.notes.style.display = 'none'; }
        if (r.url) { el.view.href = r.url; el.view.style.display = 'inline-flex'; } else { el.view.removeAttribute('href'); el.view.style.display = 'none'; }

        box.classList.add('show');
        badge();
        beep();
    };

    const next = () => {
        if (current || queue.length === 0) { badge(); return; }
        const n = queue.shift();
        badge();
        show(n);
    };

    const dismissCurrent = () => {
        if (!current) return;
        markDismissed(current.notification_key);
        current = null;
        box.classList.remove('show');
        badge();
        setTimeout(next, 150);
    };

    const enqueue = (r) => {
        if (!r || !r.notification_key) return;
        const k = r.notification_key;
        if (isDismissed(k)) return;
        if (current && current.notification_key === k) return;
        if (queue.some(q => q.notification_key === k)) return;
        queue.push(r);
        queue.sort((a, b) => String(a.notification_at || '').localeCompare(String(b.notification_at || '')));
        badge();
        next();
    };

    /* ---- poll for due reminders ---- */
    const check = async () => {
        if (busy || !navigator.onLine) return;
        busy = true;
        try {
            const res = await fetch(DUE_URL, {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store'
            });
            if (!res.ok) throw new Error('Reminder request failed: ' + res.status);
            const data = await res.json();
            if (data && Array.isArray(data.reminders)) data.reminders.forEach(enqueue);
        } catch (e) {
            console.debug('Reminder polling:', e);   /* must never break the app */
        } finally {
            busy = false;
        }
    };

    if (el.dismiss) el.dismiss.addEventListener('click', dismissCurrent);
    if (el.close) el.close.addEventListener('click', dismissCurrent);
    if (el.view) el.view.addEventListener('click', () => { if (current) markDismissed(current.notification_key); });

    check();
    const timer = setInterval(check, POLL);
    document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') check(); });
    window.addEventListener('online', check);
    window.addEventListener('beforeunload', () => clearInterval(timer));
})();
</script>

</body>

</html>
