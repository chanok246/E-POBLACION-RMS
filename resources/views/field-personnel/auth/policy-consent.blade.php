
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#123b2b">

    <title>Policies &amp; Consent | e-Poblacion</title>

    <style>
        :root {
            --green: #17613f;
            --green-dark: #123b2b;
            --green-light: #eaf4ee;
            --text: #24332b;
            --muted: #68776e;
            --border: #dce6df;
            --surface: #ffffff;
            --background: #f3f7f4;
            --danger: #b42318;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px 16px;
            background: var(--background);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
        }

        button, input {
            font: inherit;
        }

        .page {
            width: 100%;
            max-width: 760px;
            margin: 0 auto;
        }

        .brand {
            margin-bottom: 20px;
            text-align: center;
        }

        .brand-mark {
            display: inline-flex;
            width: 54px;
            height: 54px;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: var(--green-dark);
            color: white;
            font-size: 23px;
            font-weight: 800;
        }

        .brand h1 {
            margin: 10px 0 2px;
            color: var(--green-dark);
            font-size: 25px;
        }

        .brand p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
        }

        .panel {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--surface);
            box-shadow: 0 8px 28px rgba(18, 59, 43, 0.06);
        }

        .panel-header {
            padding: 25px 25px 20px;
            border-bottom: 1px solid var(--border);
        }

        .eyebrow {
            margin: 0 0 7px;
            color: var(--green);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .panel-header h2 {
            margin: 0 0 8px;
            color: var(--green-dark);
            font-size: 22px;
        }

        .panel-header p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        .personnel {
            margin-top: 15px;
            padding: 11px 13px;
            border-radius: 9px;
            background: var(--green-light);
            color: var(--green-dark);
            font-size: 13px;
        }

        .policy-list {
            display: grid;
            gap: 12px;
            padding: 20px 25px;
        }

        .policy {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: white;
        }

        .policy summary {
            position: relative;
            padding: 16px 42px 16px 16px;
            color: var(--green-dark);
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            list-style: none;
        }

        .policy summary::-webkit-details-marker {
            display: none;
        }

        .policy summary::after {
            position: absolute;
            top: 50%;
            right: 17px;
            content: "+";
            transform: translateY(-50%);
            color: var(--green);
            font-size: 22px;
            font-weight: 400;
        }

        .policy[open] summary::after {
            content: "−";
        }

        .policy-content {
            padding: 0 16px 18px;
            border-top: 1px solid var(--border);
            color: #46564c;
            font-size: 13px;
        }

        .policy-content h3 {
            margin: 17px 0 6px;
            color: var(--green-dark);
            font-size: 14px;
        }

        .policy-content p {
            margin: 12px 0;
        }

        .policy-content ul {
            padding-left: 21px;
        }

        .policy-content li {
            margin: 8px 0;
        }

        .policy-content a {
            color: var(--green);
            font-weight: 700;
        }

        .disclaimer {
            color: var(--muted);
            font-size: 12px;
            font-style: italic;
        }

        .consent-area {
            padding: 20px 25px 25px;
            border-top: 1px solid var(--border);
            background: #fbfdfb;
        }

        .version {
            margin-bottom: 14px;
            color: var(--muted);
            font-size: 11px;
        }

        .consent-check {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            margin-bottom: 17px;
            cursor: pointer;
            color: var(--text);
            font-size: 13px;
        }

        .consent-check input {
            display: block;
            width: 21px;
            height: 21px;
            flex: 0 0 21px;
            margin: 2px 0 0;
            accent-color: var(--green);
            cursor: pointer;
        }

        .submit-button,
        .continue-button {
            display: flex;
            width: 100%;
            min-height: 49px;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 9px;
            background: var(--green);
            color: white;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            transition: background 0.2s ease;
        }

        .submit-button:hover,
        .continue-button:hover {
            background: var(--green-dark);
        }

        .submit-button:focus-visible,
        .continue-button:focus-visible,
        .policy summary:focus-visible,
        .consent-check input:focus-visible {
            outline: 3px solid #9ac8aa;
            outline-offset: 3px;
        }

        .error {
            margin: 0 0 13px;
            color: var(--danger);
            font-size: 12px;
        }

        .success-panel {
            margin-bottom: 16px;
            padding: 18px;
            border: 1px solid #b8dec4;
            border-radius: 10px;
            background: var(--green-light);
            color: var(--green-dark);
            font-size: 13px;
        }

        .success-panel strong {
            display: block;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .footer {
            padding: 20px 10px 5px;
            color: var(--muted);
            text-align: center;
            font-size: 11px;
        }

        @media (max-width: 520px) {
            body {
                padding: 17px 11px;
            }

            .brand h1 {
                font-size: 22px;
            }

            .panel-header {
                padding: 21px 17px 17px;
            }

            .panel-header h2 {
                font-size: 20px;
            }

            .policy-list {
                padding: 15px 14px;
            }

            .consent-area {
                padding: 18px 16px 20px;
            }
        }
    </style>
</head>

<body>
    @php
        $personnel = auth('field_personnel')->user();
    @endphp

    <main class="page">

        <header class="brand">
            <div class="brand-mark" aria-hidden="true">eP</div>
            <h1>e-Poblacion</h1>
            <p>Barangay Poblacion Record Management System</p>
        </header>

        <section class="panel">

            <div class="panel-header">
                <p class="eyebrow">Field Personnel Access</p>

                <h2>Policies &amp; Consent</h2>

                <p>
                    Before accessing Field Collection, please review the
                    Terms &amp; Conditions, Privacy Policy, and Data Privacy Notice.
                    Your acceptance will be recorded under your Field Personnel account.
                </p>

                @if ($personnel)
                    <div class="personnel">
                        <strong>Account:</strong>
                        {{ $personnel->full_name }}
                    </div>
                @endif
            </div>

            <div class="policy-list">

                <details class="policy" id="terms">
                    <summary>1. Terms &amp; Conditions</summary>

                    <div class="policy-content">
                        <p>
                            By accessing Field Collection in e-Poblacion, you agree
                            to use the system only for official Barangay Poblacion
                            field-collection work and to follow these terms.
                        </p>

                        <ul>
                            <li>
                                <strong>Authorized use only.</strong>
                                Access is limited to Field Personnel accounts
                                issued by the barangay. Do not use another
                                person's account.
                            </li>
                            <li>
                                <strong>Protect your account.</strong>
                                Keep your username and passcode confidential.
                                Never share your credentials with other individuals.
                            </li>
                            <li>
                                <strong>Keep collected information accurate.</strong>
                                Enter household, resident, and other assigned
                                collection information accurately and only for
                                legitimate barangay purposes.
                            </li>
                            <li>
                                <strong>Respect access limits.</strong>
                                Do not attempt to view, access, or change records
                                beyond your assigned permissions. Do not copy
                                or disclose information without authority.
                            </li>
                            <li>
                                <strong>Keep the system safe.</strong>
                                Do not disrupt the system, bypass its security
                                features, or use it for personal or unlawful purposes.
                            </li>
                            <li>
                                <strong>Activity may be recorded.</strong>
                                System activity may be logged to support
                                accountability and the protection of barangay records.
                            </li>
                            <li>
                                <strong>Changes to these terms.</strong>
                                The barangay may update these terms when needed.
                                You may be asked to review and accept an updated
                                policy version before continuing to use the system.
                            </li>
                        </ul>

                        <p class="disclaimer">
                            These terms are provided for general information and
                            do not constitute a certified legal document. Questions
                            may be raised with the Barangay Poblacion office.
                        </p>
                    </div>
                </details>

                <details class="policy" id="privacy">
                    <summary>2. Privacy Policy</summary>

                    <div class="policy-content">
                        <p>
                            e-Poblacion is an internal Barangay Record Management
                            System. Authorized Field Personnel use its restricted
                            Field Collection functions to support official barangay
                            data-collection activities. It is not an online portal
                            for residents.
                        </p>

                        <h3>What is handled</h3>
                        <p>
                            Personal information needed for assigned barangay
                            field-collection activities, such as resident and
                            household details, contact information, and other
                            information required for legitimate barangay services.
                            The information categories are described in the
                            <a href="#notice">Data Privacy Notice</a>.
                        </p>

                        <h3>Why it is handled</h3>
                        <p>
                            Information is used only for legitimate barangay
                            purposes, including maintaining accurate records,
                            supporting household data collection, and carrying
                            out assigned barangay activities.
                        </p>

                        <h3>How it is protected</h3>
                        <ul>
                            <li>
                                Access is restricted to authorized personnel
                                according to their assigned system permissions.
                            </li>
                            <li>
                                Field Personnel accounts are protected by
                                individual login credentials.
                            </li>
                            <li>
                                Authorized personnel must keep their credentials
                                private and must not share their passcodes.
                            </li>
                        </ul>

                        <h3>Use and disclosure</h3>
                        <p>
                            Records are used for official barangay functions
                            and are disclosed only to authorized personnel or
                            where required or permitted by applicable law.
                        </p>

                        <h3>Your rights</h3>
                        <p>
                            You may ask the Barangay Poblacion office about
                            personal data held about you, including requests
                            for access or correction. For privacy concerns,
                            contact the barangay office directly.
                        </p>

                        <p class="disclaimer">
                            This policy is provided for general information and
                            does not constitute a certified legal compliance
                            statement. Formal compliance is verified separately
                            by the barangay.
                        </p>
                    </div>
                </details>

                <details class="policy" id="notice">
                    <summary>3. Data Privacy Notice</summary>

                    <div class="policy-content">
                        <p>
                            e-Poblacion collects and processes personal information
                            for legitimate barangay record-management purposes,
                            including resident records, household records, and
                            information needed for assigned field-collection
                            activities.
                        </p>

                        <p>
                            The information that may be maintained in the system
                            may include:
                        </p>

                        <ul>
                            <li>Full name</li>
                            <li>Address and household details</li>
                            <li>Contact information</li>
                            <li>Records related to barangay services</li>
                            <li>Documents and other supporting information, where required</li>
                        </ul>

                        <p>
                            Personal information is collected only for legitimate
                            barangay purposes and is handled in accordance with
                            applicable Philippine data privacy requirements,
                            including the Data Privacy Act of 2012. Access to
                            records is restricted to authorized barangay personnel
                            according to their assigned system permissions.
                        </p>

                        <p>
                            Authorized personnel should protect their account
                            credentials and must not share their passcodes
                            with other individuals.
                        </p>

                        <p>
                            You have the right to be informed, to access, and
                            to request the correction of your personal data.
                            For privacy concerns, you may contact the
                            Barangay Poblacion office directly.
                        </p>

                        <p class="disclaimer">
                            This notice is provided for general information
                            and does not constitute a certified legal compliance
                            statement. Formal compliance is verified separately
                            by the barangay.
                        </p>
                    </div>
                </details>

            </div>

            <div class="consent-area">

                <div class="version">
                    Policy version: {{ $policyVersion }}
                </div>

                @if ($alreadyAccepted)

                    <div class="success-panel" role="status">
                        <strong>Policy acceptance recorded</strong>
                        Your account has already accepted this policy version.
                        You may continue to your Field Collection Dashboard.
                    </div>

                    <a
                        href="{{ route('field-personnel.home') }}"
                        class="continue-button"
                    >
                        Continue to Dashboard
                    </a>

                @else

                    @if (session('success'))
                        <div class="success-panel" role="status">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->has('accept_policies'))
                        <p class="error" role="alert">
                            {{ $errors->first('accept_policies') }}
                        </p>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('field-personnel.consent.accept') }}"
                    >
                        @csrf

                        <label class="consent-check" for="accept_policies">
                            <input
                                type="checkbox"
                                id="accept_policies"
                                name="accept_policies"
                                value="1"
                                required
                            >

                            <span>
                                I confirm that I have reviewed and understood
                                the Terms &amp; Conditions, Privacy Policy,
                                and Data Privacy Notice. I agree to follow
                                these policies when using Field Collection.
                            </span>
                        </label>

                        <button type="submit" class="submit-button">
                            Accept &amp; Continue
                        </button>
                    </form>

                @endif

            </div>
        </section>

        <footer class="footer">
            &copy; {{ date('Y') }} e-Poblacion. All rights reserved.
            <br>
            Barangay Poblacion, Bani, Pangasinan
        </footer>

    </main>
</body>
</html>
