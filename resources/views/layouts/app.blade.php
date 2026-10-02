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

    /* Sidebar links live here: add, remove or reorder in one place */
    $navGroups = [
        [
            'label' => 'Main',
            'show'  => true,
            'items' => [
                ['route' => 'dashboard', 'icon' => 'bi-grid-fill', 'text' => 'Dashboard', 'active' => ['dashboard']],
            ],
        ],
        [
            'label' => 'Management',
            'show'  => $canManageRecords,
            'items' => [
                ['route' => 'residents.index', 'icon' => 'bi-people-fill', 'text' => 'Residents', 'active' => ['residents.*']],
                ['route' => 'households.index', 'icon' => 'bi-house-fill', 'text' => 'Households', 'active' => ['households.index', 'households.create', 'households.show', 'households.edit']],
                ['route' => 'households.map', 'icon' => 'bi-geo-alt-fill', 'text' => 'Household Map', 'active' => ['households.map']],
                ['route' => 'documents.index', 'icon' => 'bi-file-earmark-text-fill', 'text' => 'Barangay Documents', 'active' => ['documents.*']],
                ['route' => 'reports-complaints.index', 'icon' => 'bi-shield-exclamation', 'text' => 'Reports & Complaints', 'active' => ['reports-complaints.*']],
                ['route' => 'emergency-hotlines.index', 'icon' => 'bi-telephone-fill', 'text' => 'Emergency Hotline', 'active' => ['emergency-hotlines.*']],
            ],
        ],
        [
            'label' => 'Services',
            'show'  => $canAccessReliefVouchers || $canManageRecords,
            'items' => array_values(array_filter([
                $canAccessReliefVouchers ? ['route' => 'relief-vouchers.index', 'icon' => 'bi-qr-code', 'text' => 'Relief Vouchers', 'active' => ['relief-vouchers.*', 'relief-assistance.*']] : null,
                $canManageRecords ? ['route' => 'reports-analytics.index', 'icon' => 'bi-bar-chart-fill', 'text' => 'Reports & Analytics', 'active' => ['reports-analytics.*']] : null,
            ])),
        ],
        [
            'label' => 'System',
            'show'  => $canAccessUserManagement,
            'items' => [
                ['route' => 'users.index', 'icon' => 'bi-person-gear', 'text' => 'User Management', 'active' => ['users.*']],
            ],
        ],
    ];

    /* ================================================================
       MANUAL REMINDER INTEGRATION
       Keeps the existing operational notifications untouched while
       adding scheduled manual reminders to the notification bell.
       ================================================================ */
    $notifications = $notifications ?? [];

    $reminderToday = collect();
    $reminderTomorrow = collect();

    if ($user) {
        $today = \Carbon\Carbon::today();
        $tomorrow = \Carbon\Carbon::tomorrow();

        $reminderToday = \App\Models\Reminder::query()
            ->where('status', 'scheduled')
            ->whereBetween('scheduled_at', [
                $today->copy()->startOfDay(),
                $today->copy()->endOfDay(),
            ])
            ->orderBy('scheduled_at')
            ->get();

        $reminderTomorrow = \App\Models\Reminder::query()
            ->where('status', 'scheduled')
            ->whereBetween('scheduled_at', [
                $tomorrow->copy()->startOfDay(),
                $tomorrow->copy()->endOfDay(),
            ])
            ->orderBy('scheduled_at')
            ->get();
    }

    $existingNotificationCount = (int) ($notifications['count'] ?? 0);
    $manualReminderCount = $reminderToday->count() + $reminderTomorrow->count();
    $bellNotificationCount = $existingNotificationCount + $manualReminderCount;

    $hasBellNotifications =
        ($notifications['has_notifications'] ?? false)
        || $reminderToday->isNotEmpty()
        || $reminderTomorrow->isNotEmpty();
@endphp

<!DOCTYPE html>
<html lang="en" data-theme="{{ $initialTheme }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('icon.png') }}">

    <title>@yield('title', 'Barangay Poblacion Record Management System')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&family=Unbounded:wght@500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* Tokens match the dashboard (.dash) so every page shares one look */
        :root {
            --sidebar-w: 244px;
            --primary: #5b3df0;
            --accent-soft: rgba(91, 61, 240, .09);
            --on-accent: #ffffff;
            --page-bg: #f5f4fb;
            --text-dark: #1f2140;
            --muted-text: #74778f;
            --surface: #ffffffed;
            --surface-secondary: #f8f7fd;
            --surface-tertiary: #f2f0fa;
            --border-color: #e9e6f3;
            --border-soft: #dedaee;
            --input-bg: #ffffff;
            --input-text: #1f2140;
            --table-bg: #ffffff;
            --table-hover: #f8f7fd;
            --shadow-color: rgba(31, 33, 64, .08);
            --shadow: 0 1px 2px rgba(31, 33, 64, .04), 0 6px 18px rgba(31, 33, 64, .05);
            --seal-sky: #6fb3f2;
            --seal-green: #4fae4e;
            --seal-gold: #f5b031;
            --seal-clay: #d4501f;
            --bs-body-font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif;
            --bs-link-color-rgb: 91, 61, 240;
            --bs-link-hover-color-rgb: 70, 44, 210;
        }

        html[data-theme="dark"] {
            --primary: #9c8cff;
            --accent-soft: rgba(156, 140, 255, .14);
            --on-accent: #17132a;
            --page-bg: #12131c;
            --text-dark: #f1f3f8;
            --muted-text: #aeb4c5;
            --surface: #1b1d2a;
            --surface-secondary: #20232f;
            --surface-tertiary: #262a38;
            --border-color: rgba(255, 255, 255, .09);
            --border-soft: rgba(255, 255, 255, .14);
            --input-bg: #20232f;
            --input-text: #f1f3f8;
            --table-bg: #1b1d2a;
            --table-hover: #222532;
            --shadow-color: rgba(0, 0, 0, .4);
            --shadow: 0 1px 2px rgba(0, 0, 0, .25), 0 6px 18px rgba(0, 0, 0, .2);
            --bs-link-color-rgb: 170, 177, 255;
            --bs-link-hover-color-rgb: 204, 208, 255;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            background: var(--page-bg);
        }

        body {
            margin: 0;
            color: var(--text-dark);
            font-family: var(--bs-body-font-family);
            transition: background-color .2s ease, color .2s ease;
        }

        .min-w-0 {
            min-width: 0;
        }

        :focus-visible {
            outline: 3px solid #ffb627;
            outline-offset: 2px;
        }

        /* ===================== SIDEBAR ===================== */

        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: var(--sidebar-w);
            z-index: 1040;
            display: flex;
            flex-direction: column;
            padding: 18px 12px 12px;
            overflow-y: auto;
            overflow-x: hidden;
            background: radial-gradient(circle at 0% 0%, var(--accent-soft), transparent 46%), var(--surface);
            border-right: 1px solid var(--border-color);
            transition: transform .25s ease;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 2px 6px 16px;
            margin-bottom: 4px;
            border-bottom: 1px solid var(--border-color);
        }

        .seal {
            flex: 0 0 88px;
            width: 88px;
            height: 88px;
            padding: 3px;
            border-radius: 50%;
            background: conic-gradient(
                from 210deg,
                var(--seal-sky),
                var(--seal-green),
                var(--seal-gold),
                var(--seal-clay),
                var(--seal-sky)
            );
            box-shadow: var(--shadow);
        }

        .seal img {
            display: block;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: #fff;
            object-fit: cover;
        }

        .brand-name {
            font-family: 'Unbounded', 'Figtree', sans-serif;
            font-size: 13.5px;
            font-weight: 500;
            letter-spacing: -.01em;
            line-height: 1.25;
            color: var(--text-dark);
        }

        .brand-name span {
            display: block;
        }

        .brand-system {
            margin-top: 5px;
            font-size: 11.5px;
            font-weight: 600;
            line-height: 1.3;
            color: var(--muted-text);
        }

        .sidebar-nav {
            flex: 1;
        }

        .sidebar-label {
            display: block;
            margin: 16px 10px 6px;
            font-size: 12px;
            font-weight: 700;
            color: var(--muted-text);
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 11px;
            min-height: 40px;
            padding: 0 12px;
            margin-bottom: 2px;
            border-radius: 12px;
            color: var(--text-dark);
            font-size: 13.5px;
            font-weight: 600;
            line-height: 1.25;
            text-decoration: none;
            transition: background-color .15s, color .15s, box-shadow .15s;
        }

        .sidebar-link i {
            width: 20px;
            flex-shrink: 0;
            font-size: 16px;
            text-align: center;
            color: var(--muted-text);
            transition: color .15s;
        }

        .sidebar-link:hover {
            background: var(--surface-tertiary);
            color: var(--text-dark);
        }

        .sidebar-link:hover i {
            color: var(--primary);
        }

        .sidebar-link.active,
        .sidebar-link.active:hover {
            background: var(--primary);
            color: var(--on-accent);
            box-shadow: 0 6px 14px color-mix(in srgb, var(--primary) 32%, transparent);
        }

        .sidebar-link.active i {
            color: var(--on-accent);
        }

        .sidebar-foot {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 14px;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            background: var(--surface-secondary);
            font-size: 12px;
            line-height: 1.35;
            color: var(--muted-text);
        }

        .sidebar-foot i {
            font-size: 18px;
            color: var(--seal-gold);
        }

        .sidebar-foot strong {
            display: block;
            color: var(--text-dark);
            font-size: 12.5px;
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--border-soft);
            border-radius: 10px;
        }

        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1030;
            background: rgba(15, 12, 40, .5);
        }

        .main-content {
            min-height: 100vh;
            margin-left: var(--sidebar-w);
            width: calc(100% - var(--sidebar-w));
        }

        /* ===================== TOPBAR ===================== */

        .topbar {
            position: sticky;
            top: 0;
            z-index: 900;
            min-height: 64px;
            padding: 0 28px;
            background: color-mix(in srgb, var(--surface) 90%, transparent);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
        }

        .topbar-toggle,
        .topbar-brand {
            display: none;
        }

        .topbar-toggle {
            width: 38px;
            height: 38px;
            align-items: center;
            justify-content: center;
            border: 1.5px solid var(--border-color);
            border-radius: 50%;
            background: var(--surface);
            color: var(--text-dark);
            font-size: 19px;
        }

        .topbar-brand {
            align-items: center;
            gap: 9px;
            font-family: 'Unbounded', 'Figtree', sans-serif;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-dark);
        }

        .topbar-brand img {
            width: 38px;
            height: 38px;
            border-radius: 50%;
        }

        .topbar-search {
            position: relative;
            width: min(100%, 320px);
            margin: 0;
        }

        .topbar-search i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted-text);
            font-size: 14px;
            pointer-events: none;
        }

        .topbar-search input {
            width: 100%;
            min-height: 38px;
            padding: 0 14px 0 38px;
            border: 1.5px solid var(--border-color);
            border-radius: 999px;
            background: var(--surface-secondary);
            color: var(--input-text);
            font: 500 14px/1 'Figtree', sans-serif;
        }

        .topbar-search input::placeholder {
            color: var(--muted-text);
            opacity: .85;
        }

        .topbar-search input:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--surface);
            box-shadow: 0 0 0 4px var(--accent-soft);
        }

        .notification-button {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 50%;
            background: transparent;
            color: var(--muted-text);
            transition: background-color .15s, color .15s;
        }

        .notification-button:hover {
            background: var(--accent-soft);
            color: var(--primary);
        }

        /* ===================== NOTIFICATIONS ===================== */

        .notification-dropdown {
            position: relative;
            flex-shrink: 0;
        }

        .notification-button {
            position: relative;
        }

        .notification-badge {
            position: absolute;
            top: -2px;
            right: -3px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--surface);
            border-radius: 999px;
            background: #dc3545;
            color: #ffffff;
            font-size: 9px;
            font-weight: 800;
            line-height: 1;
            box-shadow: 0 2px 6px rgba(220, 53, 69, .25);
        }

        .notification-menu {
            width: 390px;
            max-width: calc(100vw - 24px);
            padding: 8px;
            margin-top: 10px !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 18px;
            background: var(--surface);
            color: var(--text-dark);
            box-shadow: 0 16px 40px rgba(31, 33, 64, .16) !important;
            overflow: hidden;
        }

        .notification-menu-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 12px 14px;
            border-bottom: 1px solid var(--border-color);
        }

        .notification-menu-title {
            color: var(--text-dark);
            font-size: 16px;
            font-weight: 800;
            line-height: 1.2;
        }

        .notification-menu-subtitle {
            margin-top: 3px;
            color: var(--muted-text);
            font-size: 11.5px;
            font-weight: 500;
        }

        .notification-count-label {
            flex-shrink: 0;
            padding: 4px 9px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--primary);
            font-size: 10.5px;
            font-weight: 700;
        }

        .notification-section-label {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 10px 6px;
            color: var(--muted-text);
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .notification-section-line {
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }

        .notification-section-today {
            color: #dc3545;
        }

        html[data-theme="dark"] .notification-section-today {
            color: #ff7b87;
        }

        .notification-section-tomorrow {
            color: #b07800;
        }

        html[data-theme="dark"] .notification-section-tomorrow {
            color: #f2c45f;
        }

        .notification-item {
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            width: 100%;
            padding: 11px 10px;
            margin-bottom: 3px;
            border-radius: 13px;
            color: var(--text-dark);
            text-decoration: none;
            transition: background-color .15s ease, transform .15s ease;
        }

        .notification-item:hover {
            background: var(--surface-tertiary);
            color: var(--text-dark);
            transform: translateX(1px);
        }

        .notification-item:last-child {
            margin-bottom: 0;
        }

        .notification-item-icon {
            width: 36px;
            height: 36px;
            min-width: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 11px;
            background: var(--accent-soft);
            color: var(--primary);
            font-size: 15px;
        }

        .notification-item-high .notification-item-icon {
            background: rgba(220, 53, 69, .12);
            color: #dc3545;
        }

        html[data-theme="dark"] .notification-item-high .notification-item-icon {
            background: rgba(255, 123, 135, .14);
            color: #ff7b87;
        }

        .notification-item-medium .notification-item-icon {
            background: rgba(245, 176, 49, .14);
            color: #b07800;
        }

        html[data-theme="dark"] .notification-item-medium .notification-item-icon {
            background: rgba(242, 196, 95, .14);
            color: #f2c45f;
        }

        .notification-item-content {
            min-width: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .notification-item-title {
            color: var(--text-dark);
            font-size: 13px;
            font-weight: 800;
            line-height: 1.3;
        }

        .notification-item-message {
            margin-top: 2px;
            color: var(--text-dark);
            font-size: 12px;
            font-weight: 600;
            line-height: 1.35;
        }

        .notification-item-description {
            margin-top: 2px;
            color: var(--muted-text);
            font-size: 11px;
            line-height: 1.35;
        }

        .notification-item-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 7px 12px;
            margin-top: 5px;
            color: var(--muted-text);
            font-size: 10.5px;
            line-height: 1.3;
        }

        .notification-item-meta span {
            display: inline-flex;
            align-items: center;
        }

        .notification-item-arrow {
            flex-shrink: 0;
            align-self: center;
            margin-left: 3px;
            color: var(--muted-text);
            font-size: 11px;
        }

        .notification-empty {
            padding: 30px 20px 28px;
            text-align: center;
        }

        .notification-empty-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(31, 169, 113, .12);
            color: #12805a;
            font-size: 21px;
        }

        html[data-theme="dark"] .notification-empty-icon {
            background: rgba(88, 217, 162, .13);
            color: #58d9a2;
        }

        .notification-empty-title {
            color: var(--text-dark);
            font-size: 13px;
            font-weight: 800;
        }

        .notification-empty-text {
            max-width: 290px;
            margin: 5px auto 0;
            color: var(--muted-text);
            font-size: 11px;
            line-height: 1.5;
        }

        /* Notification dropdown on smaller screens */

        @media (max-width: 576px) {
            .notification-menu {
                position: fixed !important;
                top: 58px !important;
                right: 12px !important;
                left: 12px !important;
                width: auto;
                max-width: none;
                margin-top: 0 !important;
            }
        }

        /* ===================== SCHEDULE & REMINDERS LINK ===================== */

        .notification-quick-action {
            margin-top: 8px;
            padding-top: 10px;
            border-top: 1px solid var(--border-color);
        }

        .notification-manage-link {
            display: flex;
            align-items: center;
            width: 100%;
            margin-top: 0;
            padding: 10px 11px;
            border-radius: 11px;
            color: var(--primary);
            font-size: 10.5px;
            font-weight: 700;
            text-decoration: none;
        }

        .notification-manage-link:hover {
            background: var(--accent-soft);
            color: var(--primary);
        }

        .notification-item-manual .notification-item-icon {
            background: var(--accent-soft);
            color: var(--primary);
        }

        /* ================================================================
           GLOBAL REMINDER POPUP
           Appears anywhere in the authenticated system when a reminder
           reaches one of its configured notification times.
           ================================================================ */

        .global-reminder-alert {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 1090;
            width: min(410px, calc(100vw - 30px));
            display: none;
            overflow: hidden;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            background: var(--surface);
            color: var(--text-dark);
            box-shadow:
                0 20px 55px rgba(31, 33, 64, .22),
                0 4px 18px rgba(31, 33, 64, .10);
        }

        .global-reminder-alert.show {
            display: block;
            animation: reminderSlideIn .28s ease-out;
        }

        .global-reminder-alert.priority-urgent {
            border-color: rgba(220, 53, 69, .35);
        }

        .global-reminder-alert.priority-important {
            border-color: rgba(245, 176, 49, .40);
        }

        .global-reminder-alert.priority-normal {
            border-color: var(--border-color);
        }

        .global-reminder-top {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px 16px 12px;
        }

        .global-reminder-icon {
            width: 42px;
            height: 42px;
            min-width: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            background: var(--accent-soft);
            color: var(--primary);
            font-size: 18px;
        }

        .priority-urgent .global-reminder-icon {
            background: rgba(220, 53, 69, .12);
            color: #dc3545;
        }

        .priority-important .global-reminder-icon {
            background: rgba(245, 176, 49, .14);
            color: #b07800;
        }

        html[data-theme="dark"] .priority-urgent .global-reminder-icon {
            background: rgba(255, 123, 135, .14);
            color: #ff7b87;
        }

        html[data-theme="dark"] .priority-important .global-reminder-icon {
            background: rgba(242, 196, 95, .14);
            color: #f2c45f;
        }

        .global-reminder-content {
            min-width: 0;
            flex: 1;
        }

        .global-reminder-label {
            margin-bottom: 3px;
            color: var(--primary);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .08em;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .priority-urgent .global-reminder-label {
            color: #dc3545;
        }

        html[data-theme="dark"] .priority-urgent .global-reminder-label {
            color: #ff7b87;
        }

        .global-reminder-title {
            color: var(--text-dark);
            font-size: 16px;
            font-weight: 800;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }

        .global-reminder-type {
            margin-top: 3px;
            color: var(--muted-text);
            font-size: 11px;
            font-weight: 600;
        }

        .global-reminder-close {
            width: 30px;
            height: 30px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 50%;
            background: transparent;
            color: var(--muted-text);
        }

        .global-reminder-close:hover {
            background: var(--surface-tertiary);
            color: var(--text-dark);
        }

        .global-reminder-details {
            display: flex;
            flex-direction: column;
            gap: 7px;
            margin: 0 16px;
            padding: 11px 12px;
            border: 1px solid var(--border-color);
            border-radius: 13px;
            background: var(--surface-secondary);
        }

        .global-reminder-detail {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            color: var(--text-dark);
            font-size: 11.5px;
            line-height: 1.35;
        }

        .global-reminder-detail i {
            width: 16px;
            flex-shrink: 0;
            margin-top: 1px;
            color: var(--muted-text);
            text-align: center;
        }

        .global-reminder-notes {
            margin: 11px 16px 0;
            color: var(--muted-text);
            font-size: 11px;
            line-height: 1.45;
        }

        .global-reminder-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            padding: 14px 16px 16px;
        }

        .global-reminder-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 34px;
            padding: 7px 12px;
            border-radius: 10px;
            font-size: 11.5px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .global-reminder-btn-dismiss {
            border: 1px solid var(--border-soft);
            background: var(--surface-secondary);
            color: var(--text-dark);
        }

        .global-reminder-btn-dismiss:hover {
            background: var(--surface-tertiary);
            color: var(--text-dark);
        }

        .global-reminder-btn-view {
            border: 1px solid var(--primary);
            background: var(--primary);
            color: var(--on-accent);
        }

        .global-reminder-btn-view:hover {
            filter: brightness(1.08);
            color: var(--on-accent);
        }

        .global-reminder-queue {
            position: absolute;
            top: 9px;
            right: 47px;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            display: none;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #dc3545;
            color: #fff;
            font-size: 9px;
            font-weight: 800;
        }

        .global-reminder-queue.show {
            display: inline-flex;
        }

        @keyframes reminderSlideIn {
            from {
                opacity: 0;
                transform: translateY(18px) translateX(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0) translateX(0);
            }
        }

        @media (max-width: 576px) {
            .global-reminder-alert {
                right: 15px;
                bottom: 15px;
                width: calc(100vw - 30px);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .global-reminder-alert.show {
                animation: none;
            }
        }

        /* ===================== PROFILE ===================== */

        .user-profile-dropdown {
            position: relative;
            flex-shrink: 0;
        }

        .user-profile-trigger {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-width: 170px;
            padding: 4px 12px 4px 4px;
            border: 1.5px solid var(--border-color);
            border-radius: 999px;
            background: var(--surface);
            color: var(--text-dark);
            transition: border-color .15s, background-color .15s, box-shadow .15s;
        }

        .user-profile-trigger:hover,
        .user-profile-trigger[aria-expanded="true"] {
            border-color: var(--primary);
            background: var(--surface-secondary);
            box-shadow: 0 0 0 4px var(--accent-soft);
        }

        .user-profile-avatar,
        .user-dropdown-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: linear-gradient(135deg, #5b3df0, #3a2bb8);
            color: #fff;
            font-weight: 800;
            overflow: hidden;
        }

        .user-profile-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            font-size: 15px;
        }

        .user-dropdown-avatar {
            width: 48px;
            height: 48px;
            min-width: 48px;
            border-radius: 50%;
            font-size: 18px;
        }

        .user-profile-avatar img,
        .user-dropdown-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .user-profile-text {
            min-width: 0;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            line-height: 1.15;
        }

        .user-profile-name {
            max-width: 130px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 13px;
            font-weight: 700;
        }

        .user-profile-role {
            margin-top: 2px;
            font-size: 11.5px;
            color: var(--muted-text);
            text-transform: capitalize;
        }

        .user-profile-chevron {
            margin-left: auto;
            font-size: 11px;
            color: var(--muted-text);
            transition: transform .2s ease;
        }

        .user-profile-trigger[aria-expanded="true"] .user-profile-chevron {
            transform: rotate(180deg);
        }

        .user-dropdown-menu {
            width: 310px;
            padding: 10px;
            margin-top: 10px !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 20px;
            background: var(--surface);
            color: var(--text-dark);
            box-shadow: 0 14px 34px rgba(31, 33, 64, .16) !important;
        }

        .user-dropdown-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: -8px -8px 6px;
            padding: 16px;
            border-radius: 19px 19px 0 0;
            background: radial-gradient(circle at 100% 0%, var(--accent-soft), transparent 60%), var(--surface-secondary);
            border-bottom: 1px solid var(--border-color);
        }

        .user-dropdown-eyebrow {
            margin-bottom: 2px;
            color: var(--muted-text);
            font-size: 12px;
            font-weight: 600;
        }

        .user-dropdown-name {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 800;
            font-size: 16px;
        }

        .user-role-badge,
        .user-status-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .user-role-badge {
            margin-top: 5px;
            padding: 3px 9px;
            background: var(--accent-soft);
            color: var(--primary);
        }

        .user-status-badge {
            gap: 5px;
            margin-top: 5px;
            margin-left: 4px;
            padding: 3px 9px;
            background: rgba(31, 169, 113, .14);
            color: #12805a;
        }

        html[data-theme="dark"] .user-status-badge {
            color: #58d9a2;
        }

        .user-status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .user-dropdown-section-title {
            padding: 8px 10px 4px;
            color: var(--muted-text);
            font-size: 12px;
            font-weight: 700;
        }

        .user-dropdown-item,
        .theme-switch-row {
            display: flex;
            align-items: center;
            gap: 11px;
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 2px;
            border: 0;
            border-radius: 12px;
            background: transparent;
            color: var(--text-dark);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            text-align: left;
            transition: background-color .15s;
        }

        .user-dropdown-item {
            min-height: 46px;
        }

        .theme-switch-row {
            min-height: 54px;
            cursor: pointer;
        }

        .user-dropdown-item:hover,
        .user-dropdown-item:focus,
        .theme-switch-row:hover {
            background: var(--surface-tertiary);
            color: var(--text-dark);
            outline: none;
        }

        .user-dropdown-item-icon,
        .theme-switch-icon {
            width: 34px;
            height: 34px;
            min-width: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 11px;
            background: var(--accent-soft);
            color: var(--primary);
            font-size: 15px;
        }

        .user-dropdown-item.text-danger .user-dropdown-item-icon {
            color: #dc3545;
            background: rgba(220, 53, 69, .10);
        }

        .user-dropdown-item small,
        .theme-switch-row small {
            display: block;
            margin-top: 2px;
            color: var(--muted-text);
            font-size: 11px;
            font-weight: 400;
        }

        .theme-switch-indicator {
            width: 36px;
            height: 20px;
            margin-left: auto;
            border-radius: 999px;
            background: #c5c8d6;
            position: relative;
            transition: background-color .2s;
        }

        .theme-switch-indicator::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 2px;
            left: 2px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
            transition: transform .2s ease;
        }

        html[data-theme="dark"] .theme-switch-indicator {
            background: var(--primary);
        }

        html[data-theme="dark"] .theme-switch-indicator::after {
            transform: translateX(16px);
        }

        .user-dropdown-divider {
            margin: 6px 2px;
            border-top: 1px solid var(--border-color);
        }

        /* ===================== CONTENT & BOOTSTRAP THEMING ===================== */

        .page-content {
            padding: 28px;
        }

        .card {
            background-color: var(--surface);
            color: var(--text-dark);
            border-color: var(--border-color);
            border-radius: 20px;
            box-shadow: var(--shadow);
        }

        .card-header,
        .card-footer {
            background-color: var(--surface);
            color: var(--text-dark);
            border-color: var(--border-color);
        }

        .card-body {
            color: var(--text-dark);
        }

        html[data-theme="dark"] .bg-white {
            background-color: var(--surface) !important;
            color: var(--text-dark) !important;
        }

        html[data-theme="dark"] .bg-light {
            background-color: var(--surface-tertiary) !important;
            color: var(--text-dark) !important;
        }

        html[data-theme="dark"] .bg-body {
            background-color: var(--page-bg) !important;
            color: var(--text-dark) !important;
        }

        .btn-primary {
            --bs-btn-bg: var(--primary);
            --bs-btn-border-color: var(--primary);
            --bs-btn-color: var(--on-accent);
            --bs-btn-hover-bg: var(--primary);
            --bs-btn-hover-border-color: var(--primary);
            --bs-btn-hover-color: var(--on-accent);
            --bs-btn-active-bg: var(--primary);
            --bs-btn-active-border-color: var(--primary);
            --bs-btn-active-color: var(--on-accent);
            --bs-btn-disabled-bg: var(--primary);
            --bs-btn-disabled-border-color: var(--primary);
            font-weight: 600;
        }

        .btn-primary:hover {
            filter: brightness(1.08);
        }

        .btn-outline-primary {
            --bs-btn-color: var(--primary);
            --bs-btn-border-color: var(--primary);
            --bs-btn-hover-bg: var(--primary);
            --bs-btn-hover-border-color: var(--primary);
            --bs-btn-hover-color: var(--on-accent);
            --bs-btn-active-bg: var(--primary);
            --bs-btn-active-border-color: var(--primary);
            --bs-btn-active-color: var(--on-accent);
            font-weight: 600;
        }

        .text-primary {
            color: var(--primary) !important;
        }

        .form-control,
        .form-select {
            background-color: var(--input-bg);
            color: var(--input-text);
            border-color: var(--border-soft);
            border-radius: 12px;
        }

        .form-control::placeholder {
            color: var(--muted-text);
        }

        .form-control:focus,
        .form-select:focus {
            background-color: var(--input-bg);
            color: var(--input-text);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--accent-soft);
        }

        html[data-theme="dark"] .form-control:disabled,
        html[data-theme="dark"] .form-control[readonly],
        html[data-theme="dark"] .form-select:disabled {
            background-color: #14161f;
            color: #858c95;
        }

        html[data-theme="dark"] .form-select option {
            background-color: #20232f;
            color: #f1f3f8;
        }

        .table {
            --bs-table-bg: var(--table-bg);
            --bs-table-color: var(--text-dark);
            --bs-table-border-color: var(--border-color);
            color: var(--text-dark);
            background-color: var(--table-bg);
        }

        .table > :not(caption) > * > * {
            background-color: var(--table-bg);
            color: var(--text-dark);
            border-bottom-color: var(--border-color);
        }

        .table-hover > tbody > tr:hover > * {
            background-color: var(--table-hover);
            color: var(--text-dark);
        }

        .table-light {
            --bs-table-bg: var(--surface-secondary);
            --bs-table-color: var(--muted-text);
        }

        html[data-theme="dark"] .table-light > * {
            background-color: var(--surface-secondary) !important;
            color: var(--muted-text) !important;
        }

        .text-muted {
            color: var(--muted-text) !important;
        }

        html[data-theme="dark"] .border,
        html[data-theme="dark"] .border-top,
        html[data-theme="dark"] .border-end,
        html[data-theme="dark"] .border-bottom,
        html[data-theme="dark"] .border-start {
            border-color: var(--border-color) !important;
        }

        .dropdown-menu {
            background-color: var(--surface);
            border-color: var(--border-color);
            box-shadow: 0 .5rem 1rem var(--shadow-color);
        }

        .dropdown-item {
            color: var(--text-dark);
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: var(--surface-tertiary);
            color: var(--text-dark);
        }

        .modal-content {
            background-color: var(--surface);
            color: var(--text-dark);
            border-color: var(--border-color);
            border-radius: 20px;
        }

        .modal-header,
        .modal-footer {
            border-color: var(--border-color);
        }

        .list-group-item {
            background-color: var(--surface);
            color: var(--text-dark);
            border-color: var(--border-color);
        }

        .page-link {
            background-color: var(--surface);
            color: var(--text-dark);
            border-color: var(--border-color);
        }

        .page-link:hover {
            background-color: var(--surface-tertiary);
            color: var(--text-dark);
            border-color: var(--border-color);
        }

        .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
            color: var(--on-accent);
        }

        .input-group-text {
            background-color: var(--surface-tertiary);
            color: var(--text-dark);
            border-color: var(--border-soft);
        }

        .alert {
            border-radius: 14px;
        }

        html[data-theme="dark"] .alert-light {
            background-color: #222532;
            color: #f1f3f8;
            border-color: rgba(255, 255, 255, .12);
        }

        html[data-theme="dark"] .btn-outline-secondary {
            color: #c7cbd8;
            border-color: rgba(255, 255, 255, .22);
        }

        html[data-theme="dark"] .btn-outline-secondary:hover {
            background-color: rgba(255, 255, 255, .18);
            color: #fff;
        }

        /* ===================== RESPONSIVE ===================== */

        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }

            body.sidebar-open .sidebar {
                transform: none;
                box-shadow: 0 0 40px rgba(15, 12, 40, .35);
            }

            body.sidebar-open .sidebar-backdrop {
                display: block;
            }

            body.sidebar-open {
                overflow: hidden;
            }

            .main-content {
                margin-left: 0;
            }

            .topbar {
                padding: 0 18px;
            }

            .topbar-toggle {
                display: inline-flex;
            }

            .topbar-brand {
                display: flex;
            }

            .topbar-search {
                display: none;
            }

            .page-content {
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .user-profile-trigger {
                min-width: auto;
                padding-right: 8px;
            }

            .user-profile-text {
                display: none;
            }

            .user-dropdown-menu {
                width: min(310px, calc(100vw - 24px));
            }

            .topbar-brand span {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .page-content {
                padding: 15px;
            }

            .topbar {
                padding: 0 14px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

<!-- ============ SIDEBAR ============ -->

<aside class="sidebar" id="appSidebar">

    <div class="sidebar-brand">
        <div class="seal">
            <img
                src="{{ asset('icon.png') }}"
                width="88"
                height="88"
                alt="Barangay Poblacion Record Management System"
                decoding="async"
            >
        </div>

        <div class="min-w-0">
            <div class="brand-name">
                <span>Barangay</span>
                <span>Poblacion</span>
            </div>

            <div class="brand-system">
                Record Management System
            </div>
        </div>
    </div>

    <nav class="sidebar-nav" aria-label="Main navigation">

        @foreach ($navGroups as $group)

            @if ($group['show'] && count($group['items']))

                <small class="sidebar-label">
                    {{ $group['label'] }}
                </small>

                @foreach ($group['items'] as $item)

                    @php
                        $isActive = request()->routeIs(...$item['active']);
                    @endphp

                    <a
                        href="{{ route($item['route']) }}"
                        class="sidebar-link {{ $isActive ? 'active' : '' }}"
                        @if ($isActive) aria-current="page" @endif
                    >
                        <i class="bi {{ $item['icon'] }}"></i>
                        <span>{{ $item['text'] }}</span>
                    </a>

                @endforeach

            @endif

        @endforeach

    </nav>

    <div class="sidebar-foot">
        <i class="bi bi-patch-check-fill"></i>

        <div>
            <strong>Bani, Pangasinan</strong>
            Serving since 1769
        </div>
    </div>

</aside>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>


<!-- ============ MAIN CONTENT ============ -->

<div class="main-content">

    <nav class="topbar d-flex align-items-center justify-content-between">

        <div class="d-flex align-items-center gap-3 flex-grow-1">

            <button
                type="button"
                class="topbar-toggle"
                id="sidebarToggle"
                aria-label="Open menu"
                aria-controls="appSidebar"
                aria-expanded="false"
            >
                <i class="bi bi-list"></i>
            </button>

            <div class="topbar-brand">
                <img
                    src="{{ asset('icon.png') }}"
                    alt="Barangay Poblacion Record Management System"
                >

                <span>Poblacion</span>
            </div>

            <label class="topbar-search">
                <i class="bi bi-search" aria-hidden="true"></i>

                <input
                    type="search"
                    placeholder="Search residents, documents, records..."
                    aria-label="Search"
                    autocomplete="off"
                >
            </label>

        </div>


        <div class="ms-auto d-flex align-items-center gap-2">

            {{-- ============================================================
                 NOTIFICATIONS + MANUAL REMINDERS
                 ============================================================ --}}

            <div class="dropdown notification-dropdown">

                <button
                    type="button"
                    class="notification-button position-relative"
                    id="notificationDropdown"
                    data-bs-toggle="dropdown"
                    data-bs-auto-close="outside"
                    aria-expanded="false"
                    aria-label="Notifications"
                    title="Notifications"
                >
                    <i class="bi bi-bell fs-5"></i>

                    @if ($bellNotificationCount > 0)

                        <span class="notification-badge">
                            {{ $bellNotificationCount > 99 ? '99+' : $bellNotificationCount }}
                        </span>

                    @endif
                </button>


                <div
                    class="dropdown-menu dropdown-menu-end notification-menu"
                    aria-labelledby="notificationDropdown"
                >

                    <div class="notification-menu-header">

                        <div>

                            <div class="notification-menu-title">
                                Notifications
                            </div>

                            <div class="notification-menu-subtitle">
                                Notifications &amp; scheduled reminders
                            </div>

                        </div>

                        @if ($bellNotificationCount > 0)

                            <span class="notification-count-label">
                                {{ $bellNotificationCount }}
                                {{ $bellNotificationCount === 1 ? 'item' : 'items' }}
                            </span>

                        @endif

                    </div>


                    @if (!$hasBellNotifications)

                        <div class="notification-empty">

                            <div class="notification-empty-icon">
                                <i class="bi bi-check2-circle"></i>
                            </div>

                            <div class="notification-empty-title">
                                All caught up
                            </div>

                            <div class="notification-empty-text">
                                There are no relief operations, complaint hearings, or
                                scheduled reminders for today or tomorrow.
                            </div>

                        </div>

                    @else

                        {{-- ==================== TODAY ==================== --}}

                        @if (($notifications['today'] ?? collect())->isNotEmpty() || $reminderToday->isNotEmpty())

                            <div class="notification-section-label notification-section-today">
                                <span>Today</span>
                                <span class="notification-section-line"></span>
                            </div>


                            @foreach (($notifications['today'] ?? collect()) as $notification)

                                @php
                                    $notificationUrl = null;

                                    if (
                                        ($notification['type'] ?? null) === 'relief'
                                        && \Illuminate\Support\Facades\Route::has('relief-assistance.show')
                                        && !empty($notification['record_id'])
                                    ) {
                                        $notificationUrl = route(
                                            'relief-assistance.show',
                                            $notification['record_id']
                                        );
                                    }

                                    if (
                                        ($notification['type'] ?? null) === 'complaint'
                                        && \Illuminate\Support\Facades\Route::has('reports-complaints.show')
                                        && !empty($notification['record_id'])
                                    ) {
                                        $notificationUrl = route(
                                            'reports-complaints.show',
                                            $notification['record_id']
                                        );
                                    }
                                @endphp


                                @if ($notificationUrl)

                                    <a
                                        href="{{ $notificationUrl }}"
                                        class="notification-item notification-item-{{ $notification['priority'] ?? 'normal' }}"
                                    >

                                @else

                                    <div
                                        class="notification-item notification-item-{{ $notification['priority'] ?? 'normal' }}"
                                    >

                                @endif

                                    <span class="notification-item-icon">
                                        <i class="bi {{ $notification['icon'] ?? 'bi-bell' }}"></i>
                                    </span>

                                    <span class="notification-item-content">

                                        <span class="notification-item-title">
                                            {{ $notification['title'] ?? 'Reminder' }}
                                        </span>

                                        <span class="notification-item-message">
                                            {{ $notification['message'] ?? '' }}
                                        </span>

                                        @if (!empty($notification['description']))

                                            <span class="notification-item-description">
                                                {{ $notification['description'] }}
                                            </span>

                                        @endif

                                        <span class="notification-item-meta">

                                            @if (!empty($notification['time']))

                                                <span>
                                                    <i class="bi bi-clock me-1"></i>
                                                    {{ $notification['time'] }}
                                                </span>

                                            @endif

                                            @if (!empty($notification['location']))

                                                <span>
                                                    <i class="bi bi-geo-alt me-1"></i>
                                                    {{ $notification['location'] }}
                                                </span>

                                            @endif

                                        </span>

                                    </span>

                                    <i class="bi bi-chevron-right notification-item-arrow"></i>

                                @if ($notificationUrl)

                                    </a>

                                @else

                                    </div>

                                @endif

                            @endforeach


                            {{-- Manual reminders for today --}}

                            @foreach ($reminderToday as $reminder)

                                @php
                                    $manualPriority = match ($reminder->priority) {
                                        'urgent' => 'high',
                                        'important' => 'medium',
                                        default => 'normal',
                                    };
                                @endphp

                                <a
                                    href="{{ route('reminders.show', $reminder) }}"
                                    class="notification-item notification-item-manual notification-item-{{ $manualPriority }}"
                                >

                                    <span class="notification-item-icon">
                                        <i class="bi bi-calendar-event"></i>
                                    </span>

                                    <span class="notification-item-content">

                                        <span class="notification-item-title">
                                            {{ $reminder->title }}
                                        </span>

                                        <span class="notification-item-message">
                                            {{ $reminder->type }}
                                        </span>

                                        @if ($reminder->notes)

                                            <span class="notification-item-description">
                                                {{ $reminder->notes }}
                                            </span>

                                        @endif

                                        <span class="notification-item-meta">

                                            <span>
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $reminder->scheduled_at->format('g:i A') }}
                                            </span>

                                            @if ($reminder->location)

                                                <span>
                                                    <i class="bi bi-geo-alt me-1"></i>
                                                    {{ $reminder->location }}
                                                </span>

                                            @endif

                                        </span>

                                    </span>

                                    <i class="bi bi-chevron-right notification-item-arrow"></i>

                                </a>

                            @endforeach

                        @endif


                        {{-- ==================== TOMORROW ==================== --}}

                        @if (($notifications['tomorrow'] ?? collect())->isNotEmpty() || $reminderTomorrow->isNotEmpty())

                            <div class="notification-section-label notification-section-tomorrow">
                                <span>Tomorrow</span>
                                <span class="notification-section-line"></span>
                            </div>


                            @foreach (($notifications['tomorrow'] ?? collect()) as $notification)

                                @php
                                    $notificationUrl = null;

                                    if (
                                        ($notification['type'] ?? null) === 'relief'
                                        && \Illuminate\Support\Facades\Route::has('relief-assistance.show')
                                        && !empty($notification['record_id'])
                                    ) {
                                        $notificationUrl = route(
                                            'relief-assistance.show',
                                            $notification['record_id']
                                        );
                                    }

                                    if (
                                        ($notification['type'] ?? null) === 'complaint'
                                        && \Illuminate\Support\Facades\Route::has('reports-complaints.show')
                                        && !empty($notification['record_id'])
                                    ) {
                                        $notificationUrl = route(
                                            'reports-complaints.show',
                                            $notification['record_id']
                                        );
                                    }
                                @endphp


                                @if ($notificationUrl)

                                    <a
                                        href="{{ $notificationUrl }}"
                                        class="notification-item notification-item-{{ $notification['priority'] ?? 'normal' }}"
                                    >

                                @else

                                    <div
                                        class="notification-item notification-item-{{ $notification['priority'] ?? 'normal' }}"
                                    >

                                @endif

                                    <span class="notification-item-icon">
                                        <i class="bi {{ $notification['icon'] ?? 'bi-bell' }}"></i>
                                    </span>

                                    <span class="notification-item-content">

                                        <span class="notification-item-title">
                                            {{ $notification['title'] ?? 'Reminder' }}
                                        </span>

                                        <span class="notification-item-message">
                                            {{ $notification['message'] ?? '' }}
                                        </span>

                                        @if (!empty($notification['description']))

                                            <span class="notification-item-description">
                                                {{ $notification['description'] }}
                                            </span>

                                        @endif

                                        <span class="notification-item-meta">

                                            @if (!empty($notification['time']))

                                                <span>
                                                    <i class="bi bi-clock me-1"></i>
                                                    {{ $notification['time'] }}
                                                </span>

                                            @endif

                                            @if (!empty($notification['location']))

                                                <span>
                                                    <i class="bi bi-geo-alt me-1"></i>
                                                    {{ $notification['location'] }}
                                                </span>

                                            @endif

                                        </span>

                                    </span>

                                    <i class="bi bi-chevron-right notification-item-arrow"></i>

                                @if ($notificationUrl)

                                    </a>

                                @else

                                    </div>

                                @endif

                            @endforeach


                            {{-- Manual reminders for tomorrow --}}

                            @foreach ($reminderTomorrow as $reminder)

                                @php
                                    $manualPriority = match ($reminder->priority) {
                                        'urgent' => 'high',
                                        'important' => 'medium',
                                        default => 'normal',
                                    };
                                @endphp

                                <a
                                    href="{{ route('reminders.show', $reminder) }}"
                                    class="notification-item notification-item-manual notification-item-{{ $manualPriority }}"
                                >

                                    <span class="notification-item-icon">
                                        <i class="bi bi-calendar-event"></i>
                                    </span>

                                    <span class="notification-item-content">

                                        <span class="notification-item-title">
                                            {{ $reminder->title }}
                                        </span>

                                        <span class="notification-item-message">
                                            {{ $reminder->type }}
                                        </span>

                                        @if ($reminder->notes)

                                            <span class="notification-item-description">
                                                {{ $reminder->notes }}
                                            </span>

                                        @endif

                                        <span class="notification-item-meta">

                                            <span>
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $reminder->scheduled_at->format('g:i A') }}
                                            </span>

                                            @if ($reminder->location)

                                                <span>
                                                    <i class="bi bi-geo-alt me-1"></i>
                                                    {{ $reminder->location }}
                                                </span>

                                            @endif

                                        </span>

                                    </span>

                                    <i class="bi bi-chevron-right notification-item-arrow"></i>

                                </a>

                            @endforeach

                        @endif

                    @endif


                    {{-- ==================== SCHEDULE & REMINDERS ==================== --}}

                    @if ($canManageRecords && \Illuminate\Support\Facades\Route::has('reminders.index'))

                        <div class="notification-quick-action">

                            <a
                                href="{{ route('reminders.index') }}"
                                class="notification-manage-link"
                            >
                                <i class="bi bi-calendar3 me-1"></i>

                                Open Schedule &amp; Reminders

                                <i class="bi bi-chevron-right ms-auto"></i>
                            </a>

                        </div>

                    @endif

                </div>

            </div>


            {{-- ==================== USER PROFILE ==================== --}}

            <div class="dropdown user-profile-dropdown">

                <button
                    type="button"
                    class="user-profile-trigger"
                    id="userProfileDropdown"
                    data-bs-toggle="dropdown"
                    data-bs-auto-close="outside"
                    aria-expanded="false"
                >

                    <span class="user-profile-avatar">

                        @if ($avatarUrl)

                            <img
                                src="{{ $avatarUrl }}"
                                alt="{{ $displayName }}"
                            >

                        @else

                            <i class="bi bi-person-fill"></i>

                        @endif

                    </span>


                    <span class="user-profile-text">

                        <span class="user-profile-name">
                            {{ $displayName }}
                        </span>

                        <span class="user-profile-role">
                            {{ ucfirst($user?->role ?? 'User') }}
                        </span>

                    </span>


                    <i class="bi bi-chevron-down user-profile-chevron"></i>

                </button>


                <div
                    class="dropdown-menu user-dropdown-menu dropdown-menu-end"
                    aria-labelledby="userProfileDropdown"
                >

                    <div class="user-dropdown-header">

                        <div class="user-dropdown-avatar">

                            @if ($avatarUrl)

                                <img
                                    src="{{ $avatarUrl }}"
                                    alt="{{ $displayName }}"
                                >

                            @else

                                <i class="bi bi-person-fill"></i>

                            @endif

                        </div>


                        <div class="min-w-0">

                            <div class="user-dropdown-eyebrow">
                                Signed in as
                            </div>

                            <div class="user-dropdown-name">
                                {{ $displayName }}
                            </div>


                            <span class="user-role-badge">
                                <i class="bi bi-shield-check me-1"></i>
                                {{ ucfirst($user?->role ?? 'User') }}
                            </span>


                            @if ($user?->isActive())

                                <span class="user-status-badge">
                                    <span class="user-status-dot"></span>
                                    Active account
                                </span>

                            @endif

                        </div>

                    </div>


                    <div class="user-dropdown-section-title">
                        Account
                    </div>


                    @if ($canAccessSettings)

                        <a
                            href="{{ route('settings.index') }}"
                            class="user-dropdown-item"
                        >

                            <span class="user-dropdown-item-icon">
                                <i class="bi bi-person-gear"></i>
                            </span>

                            <span>
                                <strong>Personal Settings</strong>

                                <small>
                                    Avatar, nickname &amp; account preferences
                                </small>
                            </span>

                        </a>

                    @endif


                    <div class="user-dropdown-section-title mt-2">
                        Preferences
                    </div>


                    <button
                        type="button"
                        class="theme-switch-row"
                        id="topbarThemeToggle"
                        aria-pressed="{{ $initialTheme === 'dark' ? 'true' : 'false' }}"
                    >

                        <span class="theme-switch-icon">
                            <i
                                class="bi bi-moon-stars-fill"
                                id="topbarThemeIcon"
                            ></i>
                        </span>

                        <span class="theme-switch-copy">

                            <strong id="topbarThemeLabel">
                                Dark Mode
                            </strong>

                            <small>
                                Change the interface appearance
                            </small>

                        </span>

                        <span class="theme-switch-indicator"></span>

                    </button>


                    <div class="user-dropdown-divider"></div>


                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="user-dropdown-item text-danger"
                        >

                            <span class="user-dropdown-item-icon">
                                <i class="bi bi-box-arrow-right"></i>
                            </span>

                            <span>
                                <strong>Sign out</strong>

                                <small>
                                    End your current session
                                </small>
                            </span>

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </nav>


    <main class="page-content">

        @if (session('success'))

            <div
                class="alert alert-success alert-dismissible fade show"
                role="alert"
            >
                {{ session('success') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"
                ></button>
            </div>

        @endif


        @if (session('error'))

            <div
                class="alert alert-danger alert-dismissible fade show"
                role="alert"
            >
                {{ session('error') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"
                ></button>
            </div>

        @endif


        @if ($errors->any())

            <div
                class="alert alert-danger alert-dismissible fade show"
                role="alert"
            >

                <div class="fw-bold mb-2">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Please correct the following:
                </div>

                <ul class="mb-0">

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"
                ></button>

            </div>

        @endif


        @yield('content')

    </main>

</div>


{{-- ================================================================
     GLOBAL REMINDER POPUP
     This is intentionally outside the page content so it can appear
     regardless of which module/page the user is currently viewing.
     ================================================================ --}}

<div
    id="globalReminderAlert"
    class="global-reminder-alert"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
>

    <div class="global-reminder-top">

        <div class="global-reminder-icon">
            <i class="bi bi-alarm-fill"></i>
        </div>


        <div class="global-reminder-content">

            <div class="global-reminder-label">
                Reminder
            </div>

            <div
                class="global-reminder-title"
                id="globalReminderTitle"
            >
                Scheduled Reminder
            </div>

            <div
                class="global-reminder-type"
                id="globalReminderType"
            >
                Schedule &amp; Reminders
            </div>

        </div>


        <span
            class="global-reminder-queue"
            id="globalReminderQueue"
        >
            1
        </span>


        <button
            type="button"
            class="global-reminder-close"
            id="globalReminderClose"
            aria-label="Dismiss reminder"
            title="Dismiss"
        >
            <i class="bi bi-x-lg"></i>
        </button>

    </div>


    <div class="global-reminder-details">

        <div class="global-reminder-detail">

            <i class="bi bi-clock"></i>

            <span id="globalReminderTime">
                Scheduled time
            </span>

        </div>


        <div
            class="global-reminder-detail"
            id="globalReminderLocationRow"
        >

            <i class="bi bi-geo-alt"></i>

            <span id="globalReminderLocation">
                Location
            </span>

        </div>

    </div>


    <div
        class="global-reminder-notes"
        id="globalReminderNotes"
        style="display: none;"
    ></div>


    <div class="global-reminder-actions">

        <button
            type="button"
            class="global-reminder-btn global-reminder-btn-dismiss"
            id="globalReminderDismiss"
        >
            <i class="bi bi-check2"></i>
            Dismiss
        </button>


        <a
            href="#"
            class="global-reminder-btn global-reminder-btn-view"
            id="globalReminderView"
        >
            <i class="bi bi-calendar-event"></i>
            View Reminder
        </a>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Page scripts (@push('scripts')) print here, right after Bootstrap --}}
@stack('scripts')


<!-- ================================================================
     SIDEBAR + THEME
     ================================================================ -->

<script>
(function () {

    /* ------------------------------------------------------------
       Mobile sidebar
       ------------------------------------------------------------ */

    const body = document.body;
    const toggle = document.getElementById('sidebarToggle');
    const backdrop = document.getElementById('sidebarBackdrop');

    const setSidebar = (open) => {

        body.classList.toggle('sidebar-open', open);

        if (toggle) {
            toggle.setAttribute(
                'aria-expanded',
                open ? 'true' : 'false'
            );
        }
    };

    if (toggle) {
        toggle.addEventListener(
            'click',
            () => setSidebar(
                !body.classList.contains('sidebar-open')
            )
        );
    }

    if (backdrop) {
        backdrop.addEventListener(
            'click',
            () => setSidebar(false)
        );
    }

    document.addEventListener(
        'keydown',
        e => {
            if (e.key === 'Escape') {
                setSidebar(false);
            }
        }
    );


    /* ------------------------------------------------------------
       Theme
       Account is the source of truth.
       ------------------------------------------------------------ */

    localStorage.removeItem('barangay-theme');

    const root = document.documentElement;
    const btn = document.getElementById('topbarThemeToggle');
    const label = document.getElementById('topbarThemeLabel');
    const icon = document.getElementById('topbarThemeIcon');

    const paint = (theme) => {

        const dark = theme === 'dark';

        root.setAttribute(
            'data-theme',
            theme
        );

        if (btn) {
            btn.setAttribute(
                'aria-pressed',
                dark ? 'true' : 'false'
            );
        }

        if (label) {
            label.textContent = dark
                ? 'Light Mode'
                : 'Dark Mode';
        }

        if (icon) {
            icon.className = dark
                ? 'bi bi-sun-fill'
                : 'bi bi-moon-stars-fill';
        }

        document.dispatchEvent(
            new CustomEvent(
                'themechange',
                {
                    detail: {
                        theme
                    }
                }
            )
        );
    };


    window.EPTheme = {

        current: () =>
            root.getAttribute('data-theme') === 'dark'
                ? 'dark'
                : 'light',

        async set(theme) {

            const previous = this.current();

            if (theme === previous) {
                return true;
            }

            paint(theme);

            try {

                const res = await fetch(
                    @json(route('settings.theme.update')),
                    {
                        method: 'PUT',

                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN':
                                document.querySelector(
                                    'meta[name="csrf-token"]'
                                )?.content || ''
                        },

                        body: JSON.stringify({
                            theme
                        })
                    }
                );

                if (!res.ok) {
                    throw new Error('save failed');
                }

                return true;

            } catch (e) {

                paint(previous);

                return false;
            }
        }
    };


    paint(window.EPTheme.current());


    if (btn) {

        btn.addEventListener(
            'click',
            () =>
                window.EPTheme.set(
                    window.EPTheme.current() === 'dark'
                        ? 'light'
                        : 'dark'
                )
        );

    }

})();
</script>


<!-- ================================================================
     GLOBAL REMINDER ENGINE
     ================================================================ -->

<script>
(function () {

    'use strict';


    /* ------------------------------------------------------------
       Only initialize when an authenticated user is present.
       app.blade.php is already inside the authenticated application.
       ------------------------------------------------------------ */

    const alertBox = document.getElementById('globalReminderAlert');

    if (!alertBox) {
        return;
    }


    const titleElement =
        document.getElementById('globalReminderTitle');

    const typeElement =
        document.getElementById('globalReminderType');

    const timeElement =
        document.getElementById('globalReminderTime');

    const locationRow =
        document.getElementById('globalReminderLocationRow');

    const locationElement =
        document.getElementById('globalReminderLocation');

    const notesElement =
        document.getElementById('globalReminderNotes');

    const viewButton =
        document.getElementById('globalReminderView');

    const dismissButton =
        document.getElementById('globalReminderDismiss');

    const closeButton =
        document.getElementById('globalReminderClose');

    const queueBadge =
        document.getElementById('globalReminderQueue');


    /* ------------------------------------------------------------
       Configuration
       ------------------------------------------------------------ */

    const DUE_URL = @json(route('reminders.due'));

    const STORAGE_KEY =
        'epoblacion.reminders.dismissed.v1';

    const POLL_INTERVAL = 30000;


    /* ------------------------------------------------------------
       Runtime state
       ------------------------------------------------------------ */

    let reminderQueue = [];

    let currentReminder = null;

    let pollTimer = null;

    let audioContext = null;


    /* ------------------------------------------------------------
       Local storage helpers
       ------------------------------------------------------------ */

    const readDismissed = () => {

        try {

            const raw =
                localStorage.getItem(STORAGE_KEY);

            if (!raw) {
                return {};
            }

            const parsed =
                JSON.parse(raw);

            if (
                parsed &&
                typeof parsed === 'object'
            ) {
                return parsed;
            }

            return {};

        } catch (error) {

            return {};
        }
    };


    const writeDismissed = (data) => {

        try {

            localStorage.setItem(
                STORAGE_KEY,
                JSON.stringify(data)
            );

        } catch (error) {
            /* Storage may be unavailable. */
        }
    };


    const isDismissed = (key) => {

        if (!key) {
            return false;
        }

        const dismissed =
            readDismissed();

        return Boolean(
            dismissed[key]
        );
    };


    const markDismissed = (key) => {

        if (!key) {
            return;
        }

        const dismissed =
            readDismissed();

        dismissed[key] =
            Date.now();

        /*
         * Keep storage small.
         * Remove entries older than seven days.
         */
        const cutoff =
            Date.now() -
            (7 * 24 * 60 * 60 * 1000);

        Object.keys(dismissed)
            .forEach(existingKey => {

                if (
                    Number(dismissed[existingKey]) <
                    cutoff
                ) {
                    delete dismissed[existingKey];
                }

            });

        writeDismissed(dismissed);
    };


    /* ------------------------------------------------------------
       Simple reminder sound
       Uses Web Audio API, so no audio file is required.
       ------------------------------------------------------------ */

    const prepareAudio = () => {

        try {

            if (!audioContext) {

                const AudioContext =
                    window.AudioContext ||
                    window.webkitAudioContext;

                if (!AudioContext) {
                    return;
                }

                audioContext =
                    new AudioContext();
            }

            if (
                audioContext.state === 'suspended'
            ) {
                audioContext.resume().catch(() => {});
            }

        } catch (error) {
            /* Browser may block audio. */
        }
    };


    const playReminderSound = () => {

        try {

            if (!audioContext) {
                return;
            }

            if (
                audioContext.state === 'suspended'
            ) {
                audioContext.resume().catch(() => {});
            }

            const now =
                audioContext.currentTime;

            const oscillator =
                audioContext.createOscillator();

            const gain =
                audioContext.createGain();

            oscillator.type = 'sine';

            oscillator.frequency.setValueAtTime(
                880,
                now
            );

            oscillator.frequency.exponentialRampToValueAtTime(
                1320,
                now + 0.12
            );

            gain.gain.setValueAtTime(
                0.0001,
                now
            );

            gain.gain.exponentialRampToValueAtTime(
                0.12,
                now + 0.015
            );

            gain.gain.exponentialRampToValueAtTime(
                0.0001,
                now + 0.28
            );

            oscillator.connect(gain);
            gain.connect(
                audioContext.destination
            );

            oscillator.start(now);

            oscillator.stop(
                now + 0.30
            );

        } catch (error) {
            /* Audio is optional. */
        }
    };


    /*
     * Browser autoplay policies generally require a user interaction
     * before audio can play. We prepare the audio context after the
     * first interaction anywhere on the page.
     */

    const initializeAudioAfterInteraction = () => {
        prepareAudio();
    };

    document.addEventListener(
        'pointerdown',
        initializeAudioAfterInteraction,
        {
            once: true,
            passive: true
        }
    );

    document.addEventListener(
        'keydown',
        initializeAudioAfterInteraction,
        {
            once: true
        }
    );


    /* ------------------------------------------------------------
       Popup rendering
       ------------------------------------------------------------ */

    const hidePopup = () => {

        alertBox.classList.remove('show');

        currentReminder = null;

        updateQueueBadge();
    };


    const showPopup = (reminder) => {

        if (!reminder) {
            return;
        }

        currentReminder =
            reminder;

        const priority =
            String(
                reminder.priority || 'normal'
            ).toLowerCase();


        alertBox.classList.remove(
            'priority-normal',
            'priority-important',
            'priority-urgent'
        );

        alertBox.classList.add(
            `priority-${priority}`
        );


        titleElement.textContent =
            reminder.title ||
            'Scheduled Reminder';


        typeElement.textContent =
            reminder.type ||
            'Schedule & Reminders';


        timeElement.textContent =
            reminder.scheduled_at_label ||
            reminder.notification_at_label ||
            'Scheduled time';


        if (reminder.location) {

            locationElement.textContent =
                reminder.location;

            locationRow.style.display =
                'flex';

        } else {

            locationRow.style.display =
                'none';
        }


        if (reminder.notes) {

            notesElement.textContent =
                reminder.notes;

            notesElement.style.display =
                'block';

        } else {

            notesElement.textContent =
                '';

            notesElement.style.display =
                'none';
        }


        if (reminder.url) {

            viewButton.href =
                reminder.url;

            viewButton.style.display =
                'inline-flex';

        } else {

            viewButton.removeAttribute(
                'href'
            );

            viewButton.style.display =
                'none';
        }


        alertBox.classList.add(
            'show'
        );

        updateQueueBadge();

        /*
         * Play only when a reminder actually becomes visible.
         */
        playReminderSound();
    };


    const updateQueueBadge = () => {

        if (!queueBadge) {
            return;
        }

        const count =
            reminderQueue.length;

        if (count > 0) {

            queueBadge.textContent =
                count > 99
                    ? '99+'
                    : String(count);

            queueBadge.classList.add(
                'show'
            );

        } else {

            queueBadge.classList.remove(
                'show'
            );
        }
    };


    /* ------------------------------------------------------------
       Queue handling
       ------------------------------------------------------------ */

    const displayNextReminder = () => {

        if (
            currentReminder
            || reminderQueue.length === 0
        ) {
            updateQueueBadge();
            return;
        }

        const next =
            reminderQueue.shift();

        updateQueueBadge();

        showPopup(next);
    };


    const dismissCurrentReminder = () => {

        if (!currentReminder) {
            return;
        }

        markDismissed(
            currentReminder.notification_key
        );

        currentReminder = null;

        alertBox.classList.remove(
            'show'
        );

        updateQueueBadge();

        /*
         * Give the browser one frame to complete the
         * hide animation/state update before showing
         * another queued reminder.
         */
        window.setTimeout(
            displayNextReminder,
            150
        );
    };


    const enqueueReminder = (reminder) => {

        if (!reminder) {
            return;
        }

        const key =
            reminder.notification_key;

        if (!key) {
            return;
        }

        if (isDismissed(key)) {
            return;
        }

        if (
            currentReminder &&
            currentReminder.notification_key === key
        ) {
            return;
        }

        const alreadyQueued =
            reminderQueue.some(
                queued =>
                    queued.notification_key === key
            );

        if (alreadyQueued) {
            return;
        }

        reminderQueue.push(
            reminder
        );

        /*
         * Sort reminders by notification time
         * when several become due together.
         */
        reminderQueue.sort(
            (a, b) =>
                String(
                    a.notification_at || ''
                ).localeCompare(
                    String(
                        b.notification_at || ''
                    )
                )
        );

        updateQueueBadge();

        displayNextReminder();
    };


    /* ------------------------------------------------------------
       Poll backend for due reminders
       ------------------------------------------------------------ */

    let requestInProgress = false;


    const checkDueReminders = async () => {

        if (requestInProgress) {
            return;
        }

        requestInProgress = true;

        try {

            const response =
                await fetch(
                    DUE_URL,
                    {
                        method: 'GET',
                        headers: {
                            'Accept':
                                'application/json',
                            'X-Requested-With':
                                'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        cache: 'no-store'
                    }
                );


            if (!response.ok) {
                throw new Error(
                    `Reminder request failed: ${response.status}`
                );
            }


            const data =
                await response.json();


            if (
                data &&
                Array.isArray(
                    data.reminders
                )
            ) {

                data.reminders.forEach(
                    reminder => {
                        enqueueReminder(
                            reminder
                        );
                    }
                );

            }

        } catch (error) {

            /*
             * Reminder polling must never break
             * the rest of the application.
             */

            console.debug(
                'Reminder polling:',
                error
            );

        } finally {

            requestInProgress = false;
        }
    };


    /* ------------------------------------------------------------
       Popup controls
       ------------------------------------------------------------ */

    if (dismissButton) {

        dismissButton.addEventListener(
            'click',
            dismissCurrentReminder
        );

    }


    if (closeButton) {

        closeButton.addEventListener(
            'click',
            dismissCurrentReminder
        );

    }


    /*
     * If the user chooses View Reminder, mark the currently displayed
     * notification as handled so it does not immediately return after
     * navigating to the reminder page.
     */

    if (viewButton) {

        viewButton.addEventListener(
            'click',
            () => {

                if (currentReminder) {

                    markDismissed(
                        currentReminder.notification_key
                    );

                }

            }
        );

    }


    /* ------------------------------------------------------------
       Start polling
       ------------------------------------------------------------ */

    checkDueReminders();

    pollTimer =
        window.setInterval(
            checkDueReminders,
            POLL_INTERVAL
        );


    /*
     * Also check when the browser tab becomes active again.
     * This helps when the user leaves the tab in the background.
     */

    document.addEventListener(
        'visibilitychange',
        () => {

            if (
                document.visibilityState ===
                'visible'
            ) {
                checkDueReminders();
            }

        }
    );


    /*
     * Clean up if the page is being unloaded.
     */
    window.addEventListener(
        'beforeunload',
        () => {

            if (pollTimer) {
                window.clearInterval(
                    pollTimer
                );
            }

        }
    );

})();
</script>

</body>

</html>