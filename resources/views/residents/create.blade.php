@extends('layouts.app')

@section('title', 'Add Resident | e-Poblacion')

@push('styles')
<style>
    .residents-page {
        --resident-surface:#ffffff; --resident-surface-soft:#f6f7fb; --resident-text:#252b43; --resident-muted:#7b8198;
        --resident-border:#e7e9f0; --resident-shadow:0 6px 18px rgba(31,38,72,.06);
        --rp-accent:#5b3df0; --rp-accent-soft:#efecff; --rp-ok:#17915f; --rp-ok-soft:#e4f6ed;
        --rp-danger:#d6394b; --rp-danger-soft:#fdecee; --rp-warn:#8a5a00; --rp-warn-soft:#fff4d6;
    }

    html[data-theme="dark"] .residents-page {
        --resident-surface:#1b1d2a; --resident-surface-soft:#232636; --resident-text:#f1f3f8; --resident-muted:#aeb4c5;
        --resident-border:rgba(255,255,255,.09); --resident-shadow:0 6px 18px rgba(0,0,0,.2);
        --rp-accent:#a99aff; --rp-accent-soft:rgba(169,154,255,.16); --rp-ok:#65d69b; --rp-ok-soft:rgba(101,214,155,.14);
        --rp-danger:#ff8f9b; --rp-danger-soft:rgba(255,143,155,.12); --rp-warn:#ffd978; --rp-warn-soft:rgba(255,193,7,.13);
    }

    .residents-page .breadcrumb-text {
        color:var(--resident-muted);
        font-size:12px;
        font-weight:700;
        letter-spacing:.35px;
        margin-bottom:6px;
    }

    .residents-page .page-title {
        color:var(--resident-text);
    }

    .residents-page .page-subtitle,
    .residents-page .text-muted {
        color:var(--resident-muted) !important;
    }

    .residents-page .card {
        background:var(--resident-surface);
        color:var(--resident-text);
        border:1px solid var(--resident-border) !important;
        border-radius:14px;
        box-shadow:var(--resident-shadow);
    }

    /* Form sections */
    .rf-sec {
        padding:24px 28px;
        border-bottom:1px solid var(--resident-border);
    }

    .rf-sec-head {
        display:flex;
        gap:12px;
        align-items:center;
        margin-bottom:18px;
    }

    .rf-ico {
        width:38px;
        height:38px;
        border-radius:11px;
        display:grid;
        place-items:center;
        background:var(--rp-accent-soft);
        color:var(--rp-accent);
        font-size:1.05rem;
    }

    .rf-sec-head h2 {
        font-size:1.02rem;
        font-weight:700;
        margin:0;
    }

    .rf-sec-head p {
        margin:0;
        font-size:.83rem;
        color:var(--resident-muted);
    }

    .residents-page .form-label {
        font-weight:600;
        font-size:.875rem;
        margin-bottom:6px;
        color:var(--resident-text);
    }

    .rf-req {
        color:var(--rp-danger);
        margin-left:2px;
    }

    .residents-page .form-control,
    .residents-page .form-select {
        background-color:var(--resident-surface-soft);
        color:var(--resident-text);
        border:1px solid var(--resident-border);
        border-radius:10px;
        padding:.6rem .8rem;
    }

    .residents-page .form-control::placeholder {
        color:var(--resident-muted);
        opacity:.8;
    }

    .residents-page .form-control:focus,
    .residents-page .form-select:focus {
        background-color:var(--resident-surface);
        color:var(--resident-text);
        border-color:var(--rp-accent);
        box-shadow:0 0 0 .2rem var(--rp-accent-soft);
    }

    .residents-page .form-control.is-invalid,
    .residents-page .form-select.is-invalid {
        border-color:var(--rp-danger);
        background-image:none;
    }

    .residents-page .invalid-feedback {
        color:var(--rp-danger);
    }

    .residents-page .form-text {
        color:var(--resident-muted);
    }

    .residents-page textarea.form-control {
        resize:vertical;
    }

    html[data-theme="dark"] .residents-page .form-select option {
        background:#1b1d2a;
        color:#f1f3f8;
    }

    html[data-theme="dark"] .residents-page input[type="date"] {
        color-scheme:dark;
    }

    .rf-bar {
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
        padding:16px 28px;
        background:var(--resident-surface-soft);
    }

    .residents-page .btn-soft {
        background:var(--resident-surface);
        border:1px solid var(--resident-border);
        color:var(--resident-text);
        font-weight:600;
    }

    .residents-page .btn-soft:hover {
        background:var(--resident-surface-soft);
        color:var(--resident-text);
    }

    /* Alerts */
    .rf-alert {
        display:flex;
        gap:12px;
        padding:14px 16px;
        border-radius:12px;
        margin-bottom:16px;
        border:1px solid transparent;
    }

    .rf-alert ul {
        margin:6px 0 0;
        padding-left:18px;
    }

    .rf-alert.err {
        background:var(--rp-danger-soft);
        color:var(--rp-danger);
        border-color:var(--rp-danger);
    }

    .rf-alert.warn {
        background:var(--rp-warn-soft);
        color:var(--rp-warn);
        border-color:var(--rp-warn);
    }

    /* Live preview */
    .rf-preview {
        position:sticky;
        top:90px;
    }

    .rf-preview .card-body {
        padding:26px 22px;
        text-align:center;
    }

    .rf-avatar {
        width:76px;
        height:76px;
        border-radius:22px;
        margin:0 auto 14px;
        display:grid;
        place-items:center;
        background:var(--rp-accent-soft);
        color:var(--rp-accent);
        font-size:1.5rem;
        font-weight:700;
    }

    .rf-preview h5 {
        font-weight:700;
        margin:0 0 2px;
        overflow-wrap:anywhere;
    }

    .rf-facts {
        list-style:none;
        margin:18px 0 0;
        padding:0;
        text-align:left;
    }

    .rf-facts li {
        display:flex;
        gap:10px;
        align-items:flex-start;
        padding:9px 0;
        border-top:1px solid var(--resident-border);
        font-size:.9rem;
        overflow-wrap:anywhere;
    }

    .rf-facts i {
        color:var(--resident-muted);
        margin-top:2px;
    }

    .rf-facts .is-empty {
        color:var(--resident-muted);
    }

    .rf-meter {
        margin-top:16px;
        text-align:left;
        font-size:.85rem;
    }

    .rf-meter .progress {
        height:8px;
        background:var(--resident-surface-soft);
        margin-top:6px;
    }

    .rf-meter .progress-bar {
        background:var(--rp-accent);
        transition:width .25s ease;
    }

    @media (max-width: 767.98px) {
        .residents-page .page-title {
            font-size:1.7rem;
        }

        .rf-sec {
            padding:20px;
        }

        .rf-bar {
            padding:14px 20px;
            flex-direction:column-reverse;
            align-items:stretch;
        }

        .rf-bar .rf-btns {
            display:flex;
            flex-direction:column-reverse;
            gap:8px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .rf-meter .progress-bar {
            transition:none;
        }
    }
</style>
@endpush


@section('content')

@php
    // req: true = required. type defaults to text.
    $sections = [
        [
            'title' => 'Name',
            'icon' => 'bi-person-vcard',
            'hint'  => "As it appears on the resident's valid ID.",
            'fields' => [
                ['name' => 'first_name',  'label' => 'First name',  'col' => 6, 'req' => true,  'max' => 255],
                ['name' => 'middle_name', 'label' => 'Middle name', 'col' => 6, 'req' => false, 'max' => 255],
                ['name' => 'last_name',   'label' => 'Last name',   'col' => 8, 'req' => true,  'max' => 255],
                ['name' => 'suffix',      'label' => 'Suffix',      'col' => 4, 'req' => false, 'max' => 50, 'ph' => 'Jr., Sr., III'],
            ],
        ],
        [
            'title' => 'Personal details',
            'icon' => 'bi-card-list',
            'hint'  => 'Used for certificates and demographic reports.',
            'fields' => [
                ['name' => 'birth_date',   'label' => 'Birth date',   'col' => 4, 'req' => true, 'type' => 'date'],
                ['name' => 'sex',          'label' => 'Sex',          'col' => 4, 'req' => true, 'type' => 'select', 'options' => ['Male', 'Female']],
                ['name' => 'civil_status', 'label' => 'Civil status', 'col' => 4, 'req' => true, 'type' => 'select', 'options' => ['Single', 'Married', 'Widowed', 'Separated']],

                /*
                |--------------------------------------------------------------------------
                | Individual Residency
                |--------------------------------------------------------------------------
                |
                | This belongs to the individual resident record.
                | It is not automatically inherited from the household.
                |
                */
                [
                    'name' => 'residency_start_year',
                    'label' => 'Residency start year',
                    'col' => 4,
                    'req' => false,
                    'type' => 'number',
                    'ph' => 'e.g. 2000',
                    'help' => "Enter the year this individual resident started living in Barangay Poblacion, if known."
                ],
            ],
        ],
        [
            'title' => 'Contact',
            'icon' => 'bi-telephone',
            'hint'  => 'Optional. How the barangay can reach this resident.',
            'fields' => [
                /*
                | Mobile number and email are OPTIONAL. Both are type="text" with no
                | required/pattern so the browser never blocks blank, N/A, or None.
                | The server converts blank/N/A/None to NULL and still validates real values.
                */
                ['name' => 'contact_number', 'label' => 'Mobile number', 'col' => 6, 'req' => false, 'type' => 'text', 'max' => 20, 'ph' => 'Optional — 09171234567 or N/A', 'help' => 'Optional. Example: 09171234567. You may leave it blank or enter N/A or None.'],
                ['name' => 'email',          'label' => 'Email address',  'col' => 6, 'req' => false, 'type' => 'text', 'max' => 255, 'ph' => 'Optional — name@email.com or N/A', 'help' => 'Optional. You may leave it blank or enter N/A or None.'],
            ],
        ],
        [
            'title' => 'Address and work',
            'icon' => 'bi-geo-alt',
            'hint'  => 'Current residence within the barangay.',
            'fields' => [
                ['name' => 'address',    'label' => 'Complete address', 'col' => 12, 'req' => true,  'type' => 'textarea', 'max' => 1000, 'ph' => 'House no., street, purok'],
                ['name' => 'occupation', 'label' => 'Occupation',       'col' => 6,  'req' => false, 'max' => 255],
            ],
        ],
    ];
@endphp

<div class="container-fluid residents-page">

    <div class="mb-4">
        <div class="breadcrumb-text">E-POBLACION / RESIDENTS / ADD RESIDENT</div>
        <h1 class="page-title mb-1">Add Resident</h1>
        <p class="page-subtitle mb-0">
            Register a new resident of Barangay Poblacion.
        </p>
    </div>

    @if ($errors->has('duplicate'))
        <div class="rf-alert warn" role="alert">
            <i class="bi bi-person-exclamation"></i>
            <div>
                <strong>Possible duplicate resident</strong>
                <div>{{ $errors->first('duplicate') }}</div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="rf-alert err" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>Fix these before saving:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="row g-4">

        {{-- FORM --}}
        <div class="col-lg-8">
            <form
                method="POST"
                action="{{ route('residents.store') }}"
                id="resident-form"
                class="card"
            >
                @csrf

                @foreach($sections as $s)
                    <section class="rf-sec">
                        <div class="rf-sec-head">
                            <span class="rf-ico">
                                <i class="bi {{ $s['icon'] }}"></i>
                            </span>

                            <div>
                                <h2>{{ $s['title'] }}</h2>
                                <p>{{ $s['hint'] }}</p>
                            </div>
                        </div>

                        <div class="row g-3">

                            @foreach($s['fields'] as $f)

                                @php
                                    $n       = $f['name'];
                                    $type    = isset($f['type']) ? $f['type'] : 'text';
                                    $value   = old($n);
                                    $cls     = ($type === 'select' ? 'form-select' : 'form-control') . ($errors->has($n) ? ' is-invalid' : '');
                                    $reqAttr = $f['req'] ? 'required' : '';
                                    $ph      = isset($f['ph']) ? $f['ph'] : '';
                                @endphp

                                <div class="col-md-{{ $f['col'] }}">

                                    <label
                                        for="f-{{ $n }}"
                                        class="form-label"
                                    >
                                        {{ $f['label'] }}

                                        @if($f['req'])
                                            <span
                                                class="rf-req"
                                                aria-hidden="true"
                                            >*</span>
                                        @endif
                                    </label>

                                    @if($type === 'select')

                                        <select
                                            id="f-{{ $n }}"
                                            name="{{ $n }}"
                                            class="{{ $cls }}"
                                            {{ $reqAttr }}
                                        >
                                            <option value="">
                                                Select {{ strtolower($f['label']) }}
                                            </option>

                                            @foreach($f['options'] as $opt)
                                                <option
                                                    value="{{ $opt }}"
                                                    {{ $value === $opt ? 'selected' : '' }}
                                                >
                                                    {{ $opt }}
                                                </option>
                                            @endforeach
                                        </select>

                                    @elseif($type === 'textarea')

                                        <textarea
                                            id="f-{{ $n }}"
                                            name="{{ $n }}"
                                            rows="3"
                                            class="{{ $cls }}"
                                            maxlength="{{ $f['max'] }}"
                                            placeholder="{{ $ph }}"
                                            {{ $reqAttr }}
                                        >{{ $value }}</textarea>

                                    @else

                                        <input
                                            id="f-{{ $n }}"
                                            type="{{ $type }}"
                                            name="{{ $n }}"
                                            class="{{ $cls }}"
                                            value="{{ $value }}"
                                            placeholder="{{ $ph }}"
                                            {{ $reqAttr }}

                                            @if(isset($f['max']))
                                                maxlength="{{ $f['max'] }}"
                                            @endif

                                            @if($n === 'contact_number')
                                                inputmode="tel"
                                                autocomplete="tel"
                                            @endif

                                            @if($n === 'email')
                                                inputmode="email"
                                                autocomplete="email"
                                            @endif

                                            @if($type === 'number' && $n === 'residency_start_year')
                                                min="1900"
                                                max="{{ date('Y') }}"
                                                step="1"
                                            @endif

                                            @if($type === 'date')
                                                max="{{ date('Y-m-d') }}"
                                            @endif
                                        >

                                    @endif

                                    @if(isset($f['help']))
                                        <div class="form-text">
                                            {{ $f['help'] }}
                                        </div>
                                    @endif

                                    @error($n)
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                            @endforeach

                        </div>
                    </section>
                @endforeach

                <div class="rf-bar">

                    <span class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i>
                        Fields marked
                        <span class="rf-req">*</span>
                        are required.
                    </span>

                    <div class="rf-btns d-flex gap-2">

                        <a
                            href="{{ route('residents.index') }}"
                            class="btn btn-soft"
                        >
                            <i class="bi bi-arrow-left me-1"></i>
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-person-plus me-1"></i>
                            Register Resident
                        </button>

                    </div>
                </div>

            </form>
        </div>


        {{-- LIVE PREVIEW --}}
        <aside class="col-lg-4 d-none d-lg-block">

            <div class="card rf-preview">

                <div class="card-body">

                    <div
                        class="rf-avatar"
                        id="pv-initials"
                    >?</div>

                    <h5 id="pv-name">
                        New resident
                    </h5>

                    <div class="text-muted small">
                        Preview updates as you type
                    </div>

                    <ul class="rf-facts">

                        <li>
                            <i class="bi bi-cake2"></i>
                            <span
                                id="pv-age"
                                class="is-empty"
                            >
                                Birth date not set
                            </span>
                        </li>

                        <li>
                            <i class="bi bi-person"></i>
                            <span
                                id="pv-sex"
                                class="is-empty"
                            >
                                Sex and civil status not set
                            </span>
                        </li>

                        <li>
                            <i class="bi bi-house-door"></i>
                            <span
                                id="pv-residency"
                                class="is-empty"
                            >
                                Residency start year not set
                            </span>
                        </li>

                        <li>
                            <i class="bi bi-telephone"></i>
                            <span
                                id="pv-phone"
                                class="is-empty"
                            >
                                No mobile number
                            </span>
                        </li>

                        <li>
                            <i class="bi bi-geo-alt"></i>
                            <span
                                id="pv-address"
                                class="is-empty"
                            >
                                No address
                            </span>
                        </li>

                    </ul>

                    <div class="rf-meter">

                        <div class="d-flex justify-content-between">
                            <span>Record completeness</span>
                            <strong id="pv-pct">0%</strong>
                        </div>

                        <div
                            class="progress"
                            role="progressbar"
                            aria-label="Record completeness"
                        >
                            <div
                                class="progress-bar"
                                id="pv-bar"
                                style="width:0%"
                            ></div>
                        </div>

                    </div>

                </div>

            </div>

        </aside>

    </div>
</div>


<script>
(function () {

    var form = document.getElementById('resident-form');

    if (!form) return;


    function val(n) {

        var el = form.elements[n];

        return el
            ? (el.value || '').trim()
            : '';
    }


    function set(id, text, fallback) {

        var el = document.getElementById(id);

        if (!el) return;

        el.textContent = text || fallback;

        el.classList.toggle(
            'is-empty',
            !text
        );
    }


    function update() {

        var first = val('first_name');
        var last  = val('last_name');

        var name = [
            first,
            val('middle_name'),
            last,
            val('suffix')
        ]
        .filter(Boolean)
        .join(' ');


        document.getElementById('pv-name').textContent =
            name || 'New resident';


        document.getElementById('pv-initials').textContent =
            (
                (first.charAt(0) + last.charAt(0))
                .toUpperCase()
            ) || '?';


        /*
        |--------------------------------------------------------------------------
        | AGE PREVIEW
        |--------------------------------------------------------------------------
        */

        var age = '';

        if (val('birth_date')) {

            var b = new Date(
                val('birth_date') + 'T00:00:00'
            );

            var t = new Date();

            if (!isNaN(b)) {

                var a =
                    t.getFullYear() -
                    b.getFullYear();

                var m =
                    t.getMonth() -
                    b.getMonth();

                if (
                    m < 0 ||
                    (
                        m === 0 &&
                        t.getDate() < b.getDate()
                    )
                ) {
                    a--;
                }

                if (a >= 0) {
                    age = a + ' years old';
                }
            }
        }


        set(
            'pv-age',
            age,
            'Birth date not set'
        );


        /*
        |--------------------------------------------------------------------------
        | PERSONAL DETAILS PREVIEW
        |--------------------------------------------------------------------------
        */

        set(
            'pv-sex',
            [
                val('sex'),
                val('civil_status')
            ]
            .filter(Boolean)
            .join(', '),
            'Sex and civil status not set'
        );


        /*
        |--------------------------------------------------------------------------
        | INDIVIDUAL RESIDENCY PREVIEW
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | Start year: 2000
        | Current year: 2026
        | Result: 26 years of residency
        |
        */

        var residencyStart =
            val('residency_start_year');

        var residencyText = '';

        if (residencyStart) {

            var currentYear =
                new Date().getFullYear();

            var startYear =
                parseInt(
                    residencyStart,
                    10
                );

            if (
                !isNaN(startYear) &&
                startYear >= 1900 &&
                startYear <= currentYear
            ) {

                var years =
                    currentYear - startYear;

                residencyText =
                    'Since ' +
                    startYear +
                    ' • ' +
                    years +
                    (
                        years === 1
                            ? ' year'
                            : ' years'
                    ) +
                    ' of residency';
            }
        }

        set(
            'pv-residency',
            residencyText,
            'Residency start year not set'
        );


        /*
        |--------------------------------------------------------------------------
        | CONTACT / ADDRESS PREVIEW
        |--------------------------------------------------------------------------
        */

        set(
            'pv-phone',
            val('contact_number'),
            'No mobile number'
        );

        set(
            'pv-address',
            val('address'),
            'No address'
        );


        /*
        |--------------------------------------------------------------------------
        | RECORD COMPLETENESS
        |--------------------------------------------------------------------------
        |
        | Residency is intentionally NOT required because the exact year
        | may not always be known when the resident is first registered.
        |
        */

        var keys = [
            'first_name',
            'last_name',
            'birth_date',
            'sex',
            'civil_status',
            'contact_number',
            'email',
            'address',
            'occupation'
        ];

        var done =
            keys.filter(function (k) {
                return val(k) !== '';
            }).length;

        var pct =
            Math.round(
                done / keys.length * 100
            );

        document.getElementById(
            'pv-pct'
        ).textContent =
            pct + '%';

        document.getElementById(
            'pv-bar'
        ).style.width =
            pct + '%';
    }


    form.addEventListener(
        'input',
        update
    );

    form.addEventListener(
        'change',
        update
    );

    update();

})();
</script>

@endsection