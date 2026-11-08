<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Required Policy Consent | e-Poblacion</title>

    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&family=Public+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    <script>
        (function () {
            try {
                var saved = localStorage.getItem('theme')
                    || localStorage.getItem('epoblacion-theme')
                    || localStorage.getItem('e-poblacion-theme');

                var theme = (saved === 'dark' || saved === 'light')
                    ? saved
                    : (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

                if (!document.documentElement.getAttribute('data-theme')) {
                    document.documentElement.setAttribute('data-theme', theme);
                }
            } catch (e) {
                if (!document.documentElement.getAttribute('data-theme')) {
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            }
        })();
    </script>

    <style>
        /*
         * Same green theme as the Two-Factor Verification page.
         *  - Green (brand)  = primary action / current step
         *  - Leaf green     = completed steps + selected / verified states
         *  - Crimson + icon = errors only (never used for buttons or decoration)
         */
        :root {
            color-scheme: light;

            --page: #f4f8f6;
            --card: #ffffff;
            --aside: #fbfdfc;
            --text: #12201a;
            --muted: #566a60;
            --faint: #8a9d92;
            --border: #dce5df;
            --input: #ffffff;

            --brand: #0a911a;
            --brand-dark: #087514;
            --brand-soft: #e1f5e3;

            --leaf: #2f9e5b;
            --leaf-dark: #1f7a43;
            --leaf-soft: #e3f4ea;

            --sun: #ffc933;

            --error: #b42318;
            --error-soft: #fce7e6;

            --ring: rgba(10, 145, 26, .20);

            --font-head: 'Space Grotesk', 'Public Sans', system-ui, sans-serif;
            --font-body: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --font-mono: 'JetBrains Mono', ui-monospace, Menlo, Consolas, monospace;
        }

        html[data-theme="dark"] {
            color-scheme: dark;

            --page: #08140f;
            --card: #10201a;
            --aside: #0c1914;
            --text: #eef6f1;
            --muted: #a9bdb3;
            --faint: #7f958a;
            --border: #274034;
            --input: #0a1812;

            --brand: #1fae30;
            --brand-dark: #7fe68a;
            --brand-soft: #12301a;
            --leaf-soft: #12301f;
            --leaf-dark: #7fe0a6;

            --error: #ffaaa3;
            --error-soft: #3a1b1a;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        html, body {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            min-height: 100svh;
            padding: 20px;

            display: grid;
            place-items: center;

            background:
                radial-gradient(55% 45% at 100% 0%, rgba(10, 145, 26, .10), transparent 70%),
                radial-gradient(50% 45% at 0% 100%, rgba(255, 201, 51, .10), transparent 70%),
                var(--page);

            color: var(--text);
            font-family: var(--font-body);
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, p, ul, ol {
            margin: 0;
        }

        h1, h2, h3, h4 {
            font-family: var(--font-head);
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        button, input {
            font: inherit;
        }

        /* ==============================
           MAIN LANDSCAPE CONTAINER
           (fits one screen; only the policies scroll)
        ============================== */

        .shell {
            position: relative;

            width: 100%;
            max-width: 1120px;
            height: min(700px, calc(100svh - 40px));

            display: grid;
            grid-template-columns: 5fr 7fr;
            grid-template-rows: minmax(0, 1fr);

            border: 1px solid var(--border);
            border-radius: 24px;
            overflow: hidden;

            background: var(--card);

            box-shadow:
                0 2px 4px rgba(6, 35, 26, .04),
                0 28px 60px rgba(6, 35, 26, .12);
        }

        .shell::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            height: 4px;
            z-index: 2;

            background: linear-gradient(90deg, var(--brand), var(--leaf) 55%, var(--sun));
        }

        /* ==============================
           LEFT PANEL: BRANDING + SEAL
        ============================== */

        .side {
            min-height: 0;

            display: flex;
            flex-direction: column;
            gap: 20px;

            padding: 32px 30px 26px;

            background:
                radial-gradient(90% 60% at 0% 0%, rgba(10, 145, 26, .08), transparent 70%),
                radial-gradient(80% 60% at 100% 100%, rgba(255, 201, 51, .07), transparent 70%),
                var(--aside);

            border-right: 1px solid var(--border);
        }

        .brand h1 {
            font-size: 19px;
            font-weight: 700;
        }

        .brand p {
            margin-top: 2px;
            color: var(--muted);
            font-size: 11px;
        }

        .side-divider {
            height: 1px;
            background: var(--border);
        }

        /* Large official seal that fills the left panel */

        .seal-stage {
            position: relative;
            flex: 1;
            min-height: 160px;
        }

        .seal-stage::before {
            content: '';
            position: absolute;
            inset: 6%;

            border-radius: 50%;
            background:
                radial-gradient(closest-side, rgba(10, 145, 26, .16), rgba(255, 201, 51, .10) 62%, transparent 100%);
        }

        .seal {
            position: absolute;
            inset: 0;

            width: 100%;
            height: 100%;
            padding: 6px;

            object-fit: contain;
            object-position: center;

            filter: drop-shadow(0 14px 22px rgba(6, 35, 26, .22));
            -webkit-user-drag: none;
            user-select: none;
        }

        .side-foot {
            display: grid;
            gap: 10px;
        }

        .security-note {
            display: flex;
            align-items: flex-start;
            gap: 8px;

            color: var(--muted);
            font-size: 11px;
            line-height: 1.5;
        }

        .security-note i {
            margin-top: 2px;
            color: var(--leaf);
        }

        .side-credit {
            color: var(--faint);
            font-family: var(--font-mono);
            font-size: 10px;
            letter-spacing: .02em;
        }

        /* ==============================
           RIGHT PANEL: CONSENT
        ============================== */

        .main {
            min-width: 0;
            min-height: 0;

            display: flex;
            flex-direction: column;
            gap: 14px;

            padding: 30px 32px 26px;
        }

        .main-top {
            flex: none;

            display: flex;
            align-items: center;
            gap: 12px;
        }

        .steps {
            flex: 1;
            min-width: 0;

            list-style: none;
            margin: 0;
            padding: 0;

            display: flex;
            align-items: center;
            gap: 8px;

            font-family: var(--font-mono);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .steps li {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--faint);
            white-space: nowrap;
        }

        .steps .dot {
            width: 20px;
            height: 20px;

            display: grid;
            place-items: center;

            border-radius: 50%;
            background: var(--border);
            color: var(--muted);
            font-size: 11px;
        }

        .steps .is-done {
            color: var(--leaf-dark);
        }

        .steps .is-done .dot {
            background: var(--leaf);
            color: #ffffff;
        }

        .steps .is-current {
            color: var(--brand-dark);
        }

        .steps .is-current .dot {
            background: var(--brand);
            color: #ffffff;
        }

        .steps .bar {
            flex: 1;
            min-width: 14px;
            height: 2px;
            border-radius: 2px;
            background: linear-gradient(90deg, var(--leaf), var(--brand));
            opacity: .5;
        }

        .theme-toggle {
            flex: none;
            width: 38px;
            height: 38px;

            display: grid;
            place-items: center;

            border: 1.5px solid var(--border);
            border-radius: 11px;
            background: var(--input);
            color: var(--muted);

            font-size: 16px;
            cursor: pointer;
            transition: border-color .15s, color .15s;
        }

        .theme-toggle:hover {
            border-color: var(--brand);
            color: var(--brand-dark);
        }

        .theme-toggle .moon { display: none; }

        html[data-theme="dark"] .theme-toggle .sun { display: none; }
        html[data-theme="dark"] .theme-toggle .moon { display: block; }

        .intro {
            flex: none;
        }

        .intro h2 {
            font-size: 26px;
            font-weight: 700;
        }

        .intro p {
            margin-top: 6px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
        }

        .intro p strong {
            color: var(--text);
            font-weight: 600;
        }

        /* Scrollable policies: the only part of the page that scrolls */

        .policy-scroll {
            flex: 1;
            min-height: 0;

            display: grid;
            align-content: start;
            gap: 12px;

            padding: 12px;

            overflow-y: auto;
            overscroll-behavior: contain;

            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--page);

            scrollbar-width: thin;
            scrollbar-color: var(--faint) transparent;
        }

        .policy-scroll::-webkit-scrollbar {
            width: 8px;
        }

        .policy-scroll::-webkit-scrollbar-thumb {
            background: var(--faint);
            border-radius: 8px;
        }

        .policy-scroll:focus-visible {
            outline: 3px solid var(--brand);
            outline-offset: 2px;
        }

        .policy-card {
            padding: 16px 18px;

            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
        }

        .policy-card h3 {
            margin-bottom: 9px;
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
        }

        .policy-card p,
        .policy-card li {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.65;
        }

        .policy-card li strong {
            color: var(--text);
        }

        .policy-card p + p {
            margin-top: 10px;
        }

        .policy-card h4 {
            margin-top: 13px;
            margin-bottom: 5px;
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
        }

        .policy-card ul {
            padding-left: 19px;
            display: grid;
            gap: 5px;
        }

        .policy-card li::marker {
            color: var(--brand);
        }

        .policy-card .lead {
            margin-bottom: 10px;
            color: var(--text);
            font-weight: 600;
        }

        .policy-card .disclaimer {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid var(--border);
            font-size: 12px;
            font-style: italic;
        }

        .policy-version {
            justify-self: start;

            display: inline-flex;
            align-items: center;

            padding: 4px 10px;
            border-radius: 999px;

            background: var(--leaf-soft);
            color: var(--leaf-dark);

            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .02em;
        }

        /* Consent form */

        .consent-form {
            flex: none;
        }

        .error {
            display: flex;
            align-items: flex-start;
            gap: 9px;

            margin-bottom: 10px;
            padding: 10px 13px;

            background: var(--error-soft);
            border-left: 4px solid var(--error);
            border-radius: 10px;
            color: var(--error);

            font-size: 12.5px;
            font-weight: 600;
        }

        .error i {
            margin-top: 2px;
        }

        .confirmation {
            display: flex;
            align-items: flex-start;
            gap: 12px;

            padding: 12px 14px;
            margin-bottom: 12px;

            border: 1.5px solid var(--border);
            border-radius: 14px;
            background: var(--input);

            cursor: pointer;
            transition: border-color .15s, background .15s;
        }

        .confirmation:hover {
            border-color: var(--brand);
        }

        .confirmation:has(input:checked) {
            border-color: var(--brand);
            background: var(--brand-soft);
        }

        .confirmation input {
            width: 20px;
            height: 20px;
            margin: 2px 0 0;
            flex: 0 0 auto;

            accent-color: var(--brand);
            cursor: pointer;
        }

        .confirmation strong {
            display: block;
            font-size: 13px;
            line-height: 1.45;
        }

        .confirmation small {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 11.5px;
            line-height: 1.5;
        }

        .continue {
            width: 100%;
            min-height: 46px;
            padding: 10px 14px;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;

            border: 0;
            border-radius: 11px;

            background: var(--brand);
            color: #ffffff;

            font-size: 13.5px;
            font-weight: 700;

            cursor: pointer;
            box-shadow: 0 8px 18px var(--ring);
            transition: filter .15s, transform .15s, opacity .15s;
        }

        .continue:hover:not(:disabled) {
            filter: brightness(1.07);
        }

        .continue:active:not(:disabled) {
            transform: scale(.99);
        }

        .continue:disabled {
            opacity: .45;
            cursor: not-allowed;
            box-shadow: none;
        }

        .hint {
            min-height: 18px;
            margin-top: 9px;

            text-align: center;
            color: var(--faint);
            font-size: 12px;
        }

        :focus-visible {
            outline: 3px solid var(--brand);
            outline-offset: 3px;
        }

        /* ==============================
           RESPONSIVE LAYOUT
        ============================== */

        @media (max-height: 700px) and (min-width: 821px) {
            body {
                padding: 12px;
            }

            .shell {
                height: calc(100svh - 24px);
            }

            .side {
                padding: 24px 26px 20px;
                gap: 14px;
            }

            .main {
                padding: 24px 28px 20px;
                gap: 11px;
            }

            .intro h2 {
                font-size: 23px;
            }

            .intro p {
                font-size: 12.5px;
            }

            .confirmation {
                padding: 10px 12px;
                margin-bottom: 9px;
            }

            .confirmation small {
                display: none;
            }

            .hint {
                margin-top: 6px;
            }
        }

        @media (max-width: 820px) {
            body {
                padding: 14px;
                place-items: start center;
            }

            .shell {
                max-width: 600px;
                height: auto;

                grid-template-columns: 1fr;
                grid-template-rows: auto;
            }

            /* On phones the consent comes first, branding below. */
            .main {
                order: -1;
                padding: 24px 20px;
            }

            .policy-scroll {
                flex: none;
                max-height: 340px;
            }

            .side {
                padding: 24px 22px 20px;
                border-right: 0;
                border-top: 1px solid var(--border);
            }

            .seal-stage {
                flex: none;
                width: min(100%, 240px);
                height: 240px;
                min-height: 0;
                margin: 0 auto;
            }

            .intro h2 {
                font-size: 23px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .shell {
                border-radius: 18px;
            }

            .main {
                padding: 21px 15px;
            }

            .side {
                padding: 22px 17px 18px;
            }

            .policy-card {
                padding: 14px;
            }

            .steps .label-text {
                display: none;
            }

            .brand h1 {
                font-size: 17px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                transition: none !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>

<body>
    <main class="shell">

        {{-- ==========================================
             LEFT PANEL: BRANDING + SEAL
        =========================================== --}}
        <aside class="side" aria-label="Sign-in progress">

            <header class="brand">
                <div>
                    <h1>e-Poblacion</h1>
                    <p>Barangay Poblacion Record Management System</p>
                </div>
            </header>

            <div class="side-divider"></div>

            {{-- Large official seal: fills the space between the header and the footer. --}}
            <div class="seal-stage">
                <img
                    class="seal"
                    src="{{ asset('icon.png') }}"
                    alt="Barangay Poblacion official seal"
                    width="640"
                    height="640"
                    decoding="async"
                    fetchpriority="high"
                    draggable="false"
                >
            </div>

            <div class="side-foot">
                <p class="security-note">
                    <i class="bi bi-shield-check" aria-hidden="true"></i>

                    <span>
                        Your acceptance is recorded for this account.
                        Never share your verification codes with anyone.
                    </span>
                </p>

                <p class="side-credit">e-Poblacion &middot; Secure Barangay Record Management</p>
            </div>
        </aside>


        {{-- ==========================================
             RIGHT PANEL: POLICY CONSENT
        =========================================== --}}
        <section class="main" aria-labelledby="pageTitle">

            <div class="main-top">
                {{-- Sign-in progress --}}
                <ol class="steps" aria-label="Sign-in progress">
                    <li class="is-done">
                        <span class="dot">
                            <i class="bi bi-check-lg" aria-hidden="true"></i>
                        </span>
                        <span class="label-text">Password</span>
                    </li>

                    <li class="bar" aria-hidden="true"></li>

                    <li class="is-done">
                        <span class="dot">
                            <i class="bi bi-check-lg" aria-hidden="true"></i>
                        </span>
                        <span class="label-text">Two-factor</span>
                    </li>

                    <li class="bar" aria-hidden="true"></li>

                    <li class="is-current" aria-current="step">
                        <span class="dot">3</span>
                        <span class="label-text">Policy consent</span>
                    </li>
                </ol>

                <button
                    type="button"
                    class="theme-toggle"
                    id="themeToggle"
                    aria-label="Toggle light or dark theme"
                >
                    <i class="bi bi-sun sun" aria-hidden="true"></i>
                    <i class="bi bi-moon-stars moon" aria-hidden="true"></i>
                </button>
            </div>

            <div class="intro">
                <h2 id="pageTitle">Required Policy Consent</h2>

                <p>
                    Your authenticator code was verified. Scroll through the
                    Terms &amp; Conditions, Privacy Policy, and Data Privacy
                    Notice below. <strong>Acceptance is required</strong> before
                    you can continue to the records system.
                </p>
            </div>

            {{-- Only this box scrolls. --}}
            <div
                class="policy-scroll"
                tabindex="0"
                role="region"
                aria-label="Terms and Conditions, Privacy Policy, and Data Privacy Notice (scrollable)"
            >
                {{-- TERMS AND CONDITIONS --}}
                <section class="policy-card" aria-labelledby="termsTitle">
                    <h3 id="termsTitle">1. Terms &amp; Conditions</h3>

                    <p class="lead">
                        By signing in to e-Poblacion, you agree to use the system only for official
                        Barangay Poblacion record-management work and to follow these terms.
                    </p>

                    <ul>
                        <li>
                            <strong>Authorized use only.</strong>
                            Access is limited to Captain and Secretary accounts issued by the barangay.
                            Do not use another person's account.
                        </li>
                        <li>
                            <strong>Protect your account.</strong>
                            Keep your username, password, and authenticator access confidential,
                            and never share them with anyone.
                        </li>
                        <li>
                            <strong>Keep records accurate.</strong>
                            Enter and update resident, household, document, complaint,and relief
                            records accurately and only for legitimate barangay purposes.
                        </li>
                        <li>
                            <strong>Respect access limits.</strong>
                            Do not try to view or change records beyond your assigned permissions,
                            and do not copy or disclose records without authority.
                        </li>
                        <li>
                            <strong>Keep the system safe.</strong>
                            Do not disrupt the system, bypass its security features, or use it
                            for personal or unlawful purposes.
                        </li>
                        <li>
                            <strong>Activity may be recorded.</strong>
                            System activity may be logged to support accountability and the
                            protection of barangay records.
                        </li>
                        <li>
                            <strong>Changes to these terms.</strong>
                            The barangay may update these terms when needed. Continued use of
                            the system means you accept the updated terms.
                        </li>
                    </ul>

                    <p class="disclaimer">
                        These terms are provided for general information and do not constitute
                        a certified legal document. Questions may be raised with the Barangay
                        Poblacion office.
                    </p>
                </section>

                {{-- PRIVACY POLICY --}}
                <section class="policy-card" aria-labelledby="privacyTitle">
                    <h3 id="privacyTitle">2. Privacy Policy</h3>

                    <p class="lead">
                        e-Poblacion is an internal Barangay Record Management System. It is used
                        by authorized barangay personnel to maintain official records, and it
                        is not an online portal for residents.
                    </p>

                    <h4>What is handled</h4>
                    <p>
                        Personal information needed for barangay record management, such as
                        resident and household details, document requests, reports and complaints,
                        and relief records. The full list of information categories is in the
                        Data Privacy Notice.
                    </p>

                    <h4>Why it is handled</h4>
                    <p>
                        Information is used only for legitimate barangay purposes: keeping
                        accurate records, processing documents, handling reports and complaints,
                        and supporting disaster response and relief programs.
                    </p>

                    <h4>How it is protected</h4>
                    <ul>
                        <li>
                            Access is restricted to authorized barangay personnel based on their
                            assigned system permissions.
                        </li>
                        <li>
                            Accounts are protected by credentials and authenticator-app verification.
                        </li>
                        <li>
                            Authorized personnel must keep their credentials private and must
                            not share their passwords.
                        </li>
                    </ul>

                    <h4>Use and disclosure</h4>
                    <p>
                        Records are used for official barangay functions and are disclosed only
                        to authorized personnel or where required or permitted by applicable law.
                    </p>

                    <h4>Your rights</h4>
                    <p>
                        You may ask the Barangay Poblacion office about the personal data held
                        about you, including requests for access or correction. See the Data
                        Privacy Notice and Contact Barangay.
                    </p>

                    <p class="disclaimer">
                        This policy is provided for general information and does not constitute
                        a certified legal compliance statement. Formal compliance is verified
                        separately by the barangay.
                    </p>
                </section>

                {{-- DATA PRIVACY NOTICE --}}
                <section class="policy-card" aria-labelledby="noticeTitle">
                    <h3 id="noticeTitle">3. Data Privacy Notice</h3>

                    <p class="lead">
                        e-Poblacion collects and processes personal information for legitimate
                        barangay record-management purposes, including resident records, household
                        records, document processing, reports and complaints, and relief-related records.
                    </p>

                    <p>
                        The information that may be maintained in the system may include:
                    </p>

                    <ul>
                        <li>Full name</li>
                        <li>Address and household details</li>
                        <li>Contact information</li>
                        <li>Records related to barangay services</li>
                        <li>Documents and other supporting information</li>
                        <li>Disaster assessment and relief-related records</li>
                    </ul>

                    <p>
                        Personal information is collected only for legitimate barangay purposes
                        and is handled in accordance with applicable Philippine data privacy
                        requirements, including the Data Privacy Act of 2012. Access to records
                        is restricted to authorized barangay personnel according to their
                        assigned system permissions.
                    </p>

                    <p>
                        Authorized personnel should protect their account credentials and must
                        not share their passwords with other individuals.
                    </p>

                    <p>
                        You have the right to be informed, to access, and to request the
                        correction of your personal data. For privacy concerns, you may contact
                        the Barangay Poblacion office directly.
                    </p>

                    <p class="disclaimer">
                        This notice is provided for general information and does not constitute
                        a certified legal compliance statement. Formal compliance is verified
                        separately by the barangay.
                    </p>
                </section>

                <div class="policy-version">
                    Policy version: {{ $policyVersion }}
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('policy-consent.accept') }}"
                id="consentForm"
                class="consent-form"
            >
                @csrf

                @if ($errors->any())
                    <div class="error" role="alert">
                        <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <label class="confirmation" for="acceptPolicies">
                    <input
                        type="checkbox"
                        name="accept_policies"
                        value="1"
                        id="acceptPolicies"
                        required
                        @checked(old('accept_policies'))
                    >

                    <span>
                        <strong>
                            I have read and agree to the Terms &amp; Conditions,
                            Privacy Policy, and Data Privacy Notice.
                        </strong>

                        <small>
                            I understand that these policies apply to my use of
                            the e-Poblacion Barangay Record Management System.
                            I acknowledge that acceptance is required before
                            I can continue.
                        </small>
                    </span>
                </label>

                <button
                    type="submit"
                    class="continue"
                    id="continueBtn"
                    disabled
                >
                    Accept and Continue to e-Poblacion &rarr;
                </button>

                <p class="hint" id="hintLine" aria-live="polite">
                    Review the policies and tick the checkbox to continue.
                </p>
            </form>
        </section>

    </main>

    <script>
        (function () {
            'use strict';

            const themeToggle = document.getElementById('themeToggle');
            const checkbox = document.getElementById('acceptPolicies');
            const form = document.getElementById('consentForm');
            const continueBtn = document.getElementById('continueBtn');
            const hint = document.getElementById('hintLine');

            const BUTTON_LABEL = 'Accept and Continue to e-Poblacion →';
            const IDLE_HINT = 'Review the policies and tick the checkbox to continue.';

            let sending = false;

            /*
             * Theme toggle.
             * Uses the same localStorage key as the existing page.
             */
            themeToggle.addEventListener('click', function () {
                const root = document.documentElement;

                const next = root.getAttribute('data-theme') === 'dark'
                    ? 'light'
                    : 'dark';

                root.setAttribute('data-theme', next);

                try {
                    localStorage.setItem('theme', next);
                } catch (e) {
                    // Theme remains active for the current page.
                }
            });

            /*
             * Enable submission only after explicit acceptance.
             */
            checkbox.addEventListener('change', function () {
                continueBtn.disabled = !checkbox.checked || sending;

                hint.textContent = checkbox.checked
                    ? 'You may now accept the policies and continue.'
                    : IDLE_HINT;
            });

            /*
             * Prevent duplicate submissions.
             * Server-side validation remains authoritative.
             */
            form.addEventListener('submit', function (event) {
                if (!checkbox.checked || sending) {
                    event.preventDefault();
                    return;
                }

                sending = true;
                continueBtn.disabled = true;
                continueBtn.textContent = 'Recording acceptance…';
                hint.textContent = 'Please wait while your acceptance is recorded.';
            });

            /*
             * Restore button state if the browser restores this page
             * from its back-forward cache.
             */
            window.addEventListener('pageshow', function () {
                sending = false;
                continueBtn.textContent = BUTTON_LABEL;
                continueBtn.disabled = !checkbox.checked;
            });

            /*
             * Initialize state, including validation redirects.
             */
            continueBtn.disabled = !checkbox.checked;

        })();
    </script>
</body>
</html>