<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#237a57">

    <title>
        @hasSection('title')
            @yield('title') | Field Collection | e-Poblacion RMS
        @else
            Field Collection | e-Poblacion RMS
        @endif
    </title>

    <style>
        :root {
            --fc-green: #237a57;
            --fc-green-dark: #185b40;
            --fc-green-light: #e8f4ed;
            --fc-pink: #f5a7b8;
            --fc-background: #f7faf8;
            --fc-white: #ffffff;
            --fc-text: #26352e;
            --fc-muted: #718078;
            --fc-border: #e5e9e6;
            --fc-danger: #b42335;
            --fc-shadow: 0 8px 28px rgba(39, 75, 57, .07);
            --fc-radius: 14px;
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--fc-text);
            background: var(--fc-background);
            line-height: 1.5;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        button,
        a {
            -webkit-tap-highlight-color: transparent;
        }

        a {
            color: inherit;
        }

        /* -------------------------------------------------
           APPLICATION HEADER
        ------------------------------------------------- */

        .fc-header {
            position: sticky;
            top: 0;
            z-index: 100;
            width: 100%;
            background: var(--fc-white);
            border-bottom: 1px solid var(--fc-border);
        }

        .fc-header-inner {
            width: 100%;
            max-width: 1200px;
            min-height: 70px;
            margin: 0 auto;
            padding: 10px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .fc-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 0;
            text-decoration: none;
        }

        .fc-logo {
            width: 43px;
            height: 43px;
            flex-shrink: 0;
            object-fit: contain;
        }

        .fc-brand-text {
            min-width: 0;
        }

        .fc-brand-name {
            margin: 0;
            color: var(--fc-green-dark);
            font-size: 15px;
            font-weight: 800;
            letter-spacing: .2px;
        }

        .fc-brand-subtitle {
            margin: 2px 0 0;
            color: var(--fc-muted);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .9px;
            text-transform: uppercase;
        }

        .fc-header-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
        }

        .fc-user-info {
            text-align: right;
            min-width: 0;
        }

        .fc-user-name {
            display: block;
            max-width: 180px;
            overflow: hidden;
            color: var(--fc-text);
            font-size: 13px;
            font-weight: 700;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .fc-user-position {
            display: block;
            color: var(--fc-muted);
            font-size: 11px;
        }

        .fc-logout-form {
            margin: 0;
        }

        .fc-logout-button {
            min-height: 38px;
            padding: 8px 13px;
            border: 1px solid var(--fc-border);
            border-radius: 9px;
            background: var(--fc-white);
            color: var(--fc-danger);
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            cursor: pointer;
            transition: background .2s, border-color .2s;
        }

        .fc-logout-button:hover {
            background: #fff0f1;
            border-color: #f3c8ce;
        }

        /* -------------------------------------------------
           NAVIGATION (scrolls sideways on small phones)
        ------------------------------------------------- */

        .fc-nav {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 22px 10px;
            display: flex;
            gap: 8px;
            overflow-x: auto;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        .fc-nav::-webkit-scrollbar {
            display: none;
        }

        .fc-nav-link {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 40px;
            padding: 8px 14px;
            border: 1px solid var(--fc-border);
            border-radius: 999px;
            background: var(--fc-white);
            color: var(--fc-text);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            transition: background .2s, border-color .2s, color .2s;
        }

        .fc-nav-link svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        .fc-nav-link:hover {
            background: var(--fc-green-light);
        }

        .fc-nav-link.is-active {
            background: var(--fc-green);
            border-color: var(--fc-green);
            color: var(--fc-white);
        }

        /* -------------------------------------------------
           MAIN CONTENT
        ------------------------------------------------- */

        .fc-main {
            width: 100%;
            max-width: 1200px;
            min-height: calc(100vh - 145px);
            margin: 0 auto;
            padding: 28px 22px 40px;
        }

        .fc-page-heading {
            margin-bottom: 22px;
        }

        .fc-page-title {
            margin: 0;
            color: var(--fc-green-dark);
            font-size: 25px;
            font-weight: 800;
            line-height: 1.3;
        }

        .fc-page-description {
            margin: 7px 0 0;
            color: var(--fc-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .fc-content {
            width: 100%;
        }

        /* -------------------------------------------------
           REUSABLE CARDS
        ------------------------------------------------- */

        .fc-card {
            width: 100%;
            padding: 22px;
            background: var(--fc-white);
            border: 1px solid var(--fc-border);
            border-radius: var(--fc-radius);
            box-shadow: var(--fc-shadow);
        }

        .fc-card + .fc-card {
            margin-top: 18px;
        }

        .fc-card-title {
            margin: 0 0 7px;
            color: var(--fc-green-dark);
            font-size: 17px;
            font-weight: 800;
        }

        .fc-card-description {
            margin: 0;
            color: var(--fc-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        /* -------------------------------------------------
           ALERT MESSAGES
        ------------------------------------------------- */

        .fc-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 18px;
            padding: 13px 15px;
            border: 1px solid transparent;
            border-radius: 10px;
            font-size: 13px;
            line-height: 1.6;
        }

        .fc-alert-success {
            color: #17613e;
            background: #e9f7ef;
            border-color: #c8ead5;
        }

        .fc-alert-error {
            color: #9f2332;
            background: #fff0f1;
            border-color: #f3c8ce;
        }

        .fc-alert-warning {
            color: #805400;
            background: #fff8e6;
            border-color: #f2dfaa;
        }

        .fc-alert-info {
            color: #245c83;
            background: #eef7fd;
            border-color: #c9e4f5;
        }

        .fc-alert-icon {
            flex-shrink: 0;
            font-size: 15px;
        }

        /* -------------------------------------------------
           REUSABLE BUTTONS
        ------------------------------------------------- */

        .fc-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 43px;
            padding: 10px 16px;
            border: 1px solid transparent;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: background .2s, border-color .2s, transform .2s;
        }

        .fc-button:active {
            transform: translateY(1px);
        }

        .fc-button-primary {
            background: var(--fc-green);
            color: var(--fc-white);
        }

        .fc-button-primary:hover {
            background: var(--fc-green-dark);
        }

        .fc-button-secondary {
            background: var(--fc-white);
            border-color: var(--fc-border);
            color: var(--fc-text);
        }

        .fc-button-secondary:hover {
            background: var(--fc-background);
        }

        .fc-button-danger {
            background: #fff0f1;
            border-color: #f3c8ce;
            color: var(--fc-danger);
        }

        .fc-button-danger:hover {
            background: #ffe3e6;
        }

        .fc-button:disabled {
            opacity: .55;
            cursor: not-allowed;
            transform: none;
        }

        /* -------------------------------------------------
           FORM ELEMENTS
        ------------------------------------------------- */

        .fc-form-group {
            margin-bottom: 18px;
        }

        .fc-label {
            display: block;
            margin-bottom: 7px;
            color: #34483d;
            font-size: 13px;
            font-weight: 700;
        }

        .fc-required {
            color: var(--fc-danger);
        }

        .fc-input,
        .fc-select,
        .fc-textarea {
            display: block;
            width: 100%;
            min-height: 45px;
            padding: 11px 13px;
            border: 1px solid var(--fc-border);
            border-radius: 9px;
            outline: none;
            background: var(--fc-white);
            color: var(--fc-text);
            font-size: 14px;
            transition: border-color .2s, box-shadow .2s;
        }

        .fc-textarea {
            min-height: 105px;
            resize: vertical;
        }

        .fc-input:focus,
        .fc-select:focus,
        .fc-textarea:focus {
            border-color: var(--fc-green);
            box-shadow: 0 0 0 3px rgba(35, 122, 87, .11);
        }

        .fc-input::placeholder,
        .fc-textarea::placeholder {
            color: #a1aaa5;
        }

        .fc-hint {
            display: block;
            margin-top: 6px;
            color: var(--fc-muted);
            font-size: 11px;
            line-height: 1.5;
        }

        .fc-error {
            display: block;
            margin-top: 6px;
            color: var(--fc-danger);
            font-size: 12px;
            line-height: 1.5;
        }

        /* -------------------------------------------------
           FOOTER
        ------------------------------------------------- */

        .fc-footer {
            width: 100%;
            padding: 18px 20px;
            border-top: 1px solid var(--fc-border);
            background: var(--fc-white);
            color: var(--fc-muted);
            font-size: 11px;
            line-height: 1.7;
            text-align: center;
        }

        .fc-footer strong {
            color: var(--fc-green-dark);
        }

        /* -------------------------------------------------
           RESPONSIVE
        ------------------------------------------------- */

        @media (max-width: 640px) {
            .fc-header-inner {
                min-height: 64px;
                padding: 9px 14px;
                gap: 8px;
            }

            .fc-logo {
                width: 37px;
                height: 37px;
            }

            .fc-brand {
                gap: 8px;
            }

            .fc-brand-name {
                font-size: 12px;
            }

            .fc-brand-subtitle {
                font-size: 9px;
                letter-spacing: .5px;
            }

            .fc-header-actions {
                gap: 7px;
            }

            .fc-user-name {
                max-width: 105px;
                font-size: 11px;
            }

            .fc-user-position {
                font-size: 10px;
            }

            .fc-logout-button {
                min-height: 35px;
                padding: 7px 9px;
                font-size: 11px;
            }

            .fc-nav {
                padding: 0 14px 9px;
            }

            .fc-main {
                min-height: calc(100vh - 185px);
                padding: 18px 14px 30px;
            }

            .fc-page-heading {
                margin-bottom: 18px;
            }

            .fc-page-title {
                font-size: 22px;
            }

            .fc-page-description {
                font-size: 12px;
            }

            .fc-card {
                padding: 17px;
                border-radius: 12px;
            }

            .fc-card-title {
                font-size: 16px;
            }

            .fc-button {
                min-height: 44px;
            }
        }

        @media (max-width: 360px) {
            .fc-user-info {
                display: none;
            }

            .fc-brand-name {
                font-size: 11px;
            }

            .fc-logout-button {
                padding: 7px 8px;
            }
        }

        /* -------------------------------------------------
           ACCESSIBILITY
        ------------------------------------------------- */

        :focus-visible {
            outline: 3px solid rgba(35, 122, 87, .35);
            outline-offset: 2px;
        }

        [hidden] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body>

    <header class="fc-header">
        <div class="fc-header-inner">

            <a href="{{ url('/field-personnel') }}" class="fc-brand">
                <img
                    src="{{ asset('icon.png') }}"
                    alt="e-Poblacion RMS Logo"
                    class="fc-logo"
                >

                <div class="fc-brand-text">
                    <p class="fc-brand-name">
                        BARANGAY POBLACION
                    </p>

                    <p class="fc-brand-subtitle">
                        Field Collection
                    </p>
                </div>
            </a>

            @auth('field_personnel')
                @php
                    $fieldPersonnel = auth('field_personnel')->user();
                @endphp

                <div class="fc-header-actions">

                    <div class="fc-user-info">
                        <span class="fc-user-name">
                            {{ $fieldPersonnel->full_name }}
                        </span>

                        <span class="fc-user-position">
                            {{ $fieldPersonnel->position }}
                        </span>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('field-personnel.logout') }}"
                        class="fc-logout-form"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="fc-logout-button"
                        >
                            Sign Out
                        </button>
                    </form>

                </div>
            @endauth

        </div>

        @auth('field_personnel')
            <nav class="fc-nav" aria-label="Field Collection navigation">
                <a href="{{ url('/field-personnel') }}"
                   class="fc-nav-link {{ request()->is('field-personnel') ? 'is-active' : '' }}"
                   @if (request()->is('field-personnel')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
                    Home
                </a>

                <a href="{{ route('field-personnel.households.create') }}"
                   class="fc-nav-link {{ request()->routeIs('field-personnel.households.*') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    New Household
                </a>

                <a href="{{ route('field-personnel.submissions.index') }}"
                   class="fc-nav-link {{ request()->routeIs('field-personnel.submissions.*') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3 8-8"/><path d="M20 12v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9"/></svg>
                    Submissions
                </a>

                <a href="{{ route('field-personnel.vouchers.verify') }}"
                   class="fc-nav-link {{ request()->routeIs('field-personnel.vouchers.*') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h3M20 14v6M14 20h3"/></svg>
                    Vouchers
                </a>

                <a href="{{ route('field-personnel.tasks.index') }}"
                   class="fc-nav-link {{ request()->routeIs('field-personnel.tasks.*') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                    Tasks
                </a>
            </nav>
        @endauth
    </header>

    <main class="fc-main">

        {{-- Session success message --}}
        @if (session('success'))
            <div class="fc-alert fc-alert-success" role="status">
                <span class="fc-alert-icon" aria-hidden="true">✓</span>

                <span>
                    {{ session('success') }}
                </span>
            </div>
        @endif

        {{-- Session error message --}}
        @if (session('error'))
            <div class="fc-alert fc-alert-error" role="alert">
                <span class="fc-alert-icon" aria-hidden="true">!</span>

                <span>
                    {{ session('error') }}
                </span>
            </div>
        @endif

        {{-- General validation errors --}}
        @if ($errors->any() && ! session('hide_validation_summary'))
            <div class="fc-alert fc-alert-error" role="alert">
                <span class="fc-alert-icon" aria-hidden="true">!</span>

                <div>
                    <strong>Please review the following:</strong>

                    <ul style="margin: 6px 0 0; padding-left: 18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @hasSection('page-heading')
            <section class="fc-page-heading">
                @yield('page-heading')
            </section>
        @endif

        <section class="fc-content">
            @yield('content')
        </section>

    </main>

    <footer class="fc-footer">
        <strong>e-Poblacion Record Management System</strong>
        <br>
        Barangay Poblacion, Bani, Pangasinan
        <br>
        All Rights Reserved.
    </footer>

    @stack('scripts')

</body>
</html>