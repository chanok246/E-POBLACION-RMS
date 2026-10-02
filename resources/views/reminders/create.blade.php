@extends('layouts.app')

@section('title', 'Create Reminder | e-Poblacion')

@php
    /* Icons per schedule type (same set the details page uses) */
    $typeIcons = [
        'Meeting'           => 'people-fill',
        'Hearing'           => 'person-lines-fill',
        'Operation'         => 'truck',
        'Event'             => 'calendar-event-fill',
        'Barangay Activity' => 'building',
        'Deadline'          => 'hourglass-split',
        'Inspection'        => 'clipboard-check-fill',
        'Follow-up'         => 'arrow-repeat',
        'General Reminder'  => 'bell-fill',
        'Other'             => 'three-dots',
    ];

    /* How many minutes before the start each pre-reminder fires */
    $offsetMinutes = [
        'at_time'    => 0,
        '30_minutes' => 30,
        '1_hour'     => 60,
        '2_hours'    => 120,
        '6_hours'    => 360,
        '12_hours'   => 720,
        '1_day'      => 1440,
        '2_days'     => 2880,
        '3_days'     => 4320,
    ];

    $priorityMeta = [
        'urgent'    => ['icon' => 'exclamation-triangle-fill', 'hint' => 'Needs immediate attention'],
        'important' => ['icon' => 'exclamation-circle-fill',   'hint' => 'Prepare in advance'],
        'normal'    => ['icon' => 'dash-circle-fill',          'hint' => 'Regular scheduled activity'],
    ];

    /*
     * First visit: "At scheduled time" is pre-selected (same default the
     * quick-create popup uses). After a failed validation, keep exactly
     * what the user had chosen.
     */
    $selectedOffsets = session()->hasOldInput()
        ? (array) old('reminder_offsets', [])
        : (collect($reminderOptions)->has('at_time') ? ['at_time'] : []);

    $selectedType     = old('type');
    $selectedPriority = old('priority', 'normal');
@endphp

@push('styles')
<style>
    .rmc { max-width: 1180px; margin: 0 auto; }

    /* ---------- Header ---------- */
    .rmc-head { display: flex; align-items: center; gap: 14px; margin-bottom: 22px; }
    .rmc-back {
        width: 42px; height: 42px; flex: 0 0 42px; display: grid; place-items: center; border-radius: 50%;
        border: 1.5px solid var(--border-color); background: var(--surface); color: var(--text-dark);
        text-decoration: none; transition: border-color .15s, background-color .15s, color .15s;
    }
    .rmc-back:hover { border-color: var(--primary); color: var(--primary); background: var(--accent-soft); }
    .rmc-head h1 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -.02em; color: var(--text-dark); }
    .rmc-head p { margin: 3px 0 0; font-size: 14px; color: var(--muted-text); }

    /* ---------- Layout ---------- */
    .rmc-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 22px; align-items: start; }
    .rmc-side { position: sticky; top: 84px; display: grid; gap: 18px; }

    .rmc-card {
        background: var(--surface); border: 1px solid var(--border-color); border-radius: 20px;
        box-shadow: var(--shadow); margin-bottom: 18px;
    }
    .rmc-side .rmc-card { margin-bottom: 0; }
    .rmc-card-head { display: flex; align-items: center; gap: 12px; padding: 20px 24px 0; }
    .rmc-step {
        width: 32px; height: 32px; flex: 0 0 32px; display: grid; place-items: center; border-radius: 50%;
        background: var(--accent-soft); color: var(--primary); font-size: 14px; font-weight: 800;
    }
    .rmc-card-head h2 { margin: 0; font-size: 16px; font-weight: 800; color: var(--text-dark); }
    .rmc-card-head p { margin: 2px 0 0; font-size: 12.5px; color: var(--muted-text); }
    .rmc-card-body { padding: 20px 24px 24px; }

    /* ---------- Fields ---------- */
    .rmc-field + .rmc-field { margin-top: 20px; }
    .rmc-label { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px; font-size: 13px; font-weight: 700; color: var(--text-dark); }
    .rmc-label .req { color: #dc3545; margin-left: 2px; }
    .rmc-label .opt { font-weight: 500; color: var(--muted-text); font-size: 12px; }
    .rmc-count { font-size: 11.5px; font-weight: 600; color: var(--muted-text); }
    .rmc-count.is-near { color: #b07800; }

    .rmc-input, .rmc-textarea {
        width: 100%; padding: 12px 14px; border: 1.5px solid var(--border-soft); border-radius: 14px;
        background: var(--input-bg); color: var(--input-text); font: 500 14.5px 'Figtree', sans-serif;
        transition: border-color .15s, box-shadow .15s;
    }
    .rmc-input::placeholder, .rmc-textarea::placeholder { color: var(--muted-text); opacity: .8; }
    .rmc-input:focus, .rmc-textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px var(--accent-soft); }
    .rmc-input.is-lg { padding: 14px 16px; font-size: 16px; font-weight: 600; }
    .rmc-input.is-invalid, .rmc-textarea.is-invalid { border-color: #dc3545; }
    .rmc-textarea { resize: vertical; min-height: 118px; line-height: 1.55; }
    .rmc-error { margin-top: 6px; font-size: 12.5px; font-weight: 600; color: #dc3545; }
    .rmc-help { margin-top: 6px; font-size: 12px; color: var(--muted-text); }

    .rmc-iconfield { position: relative; }
    .rmc-iconfield > i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted-text); pointer-events: none; }
    .rmc-iconfield .rmc-input { padding-left: 40px; }

    .rmc-two { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    /* ---------- Quick chips ---------- */
    .rmc-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .rmc-chip {
        padding: 6px 12px; border: 1.5px solid var(--border-color); border-radius: 999px; background: var(--surface-secondary);
        color: var(--muted-text); font: 600 12px 'Figtree', sans-serif; cursor: pointer;
        transition: border-color .15s, color .15s, background-color .15s;
    }
    .rmc-chip:hover { border-color: var(--primary); color: var(--primary); }
    .rmc-chip.is-on { background: var(--primary); border-color: var(--primary); color: var(--on-accent); }

    /* ---------- Type cards ---------- */
    .rmc-types { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
    .rmc-type { position: relative; display: block; cursor: pointer; }
    .rmc-type input { position: absolute; opacity: 0; inset: 0; cursor: pointer; }
    .rmc-type-box {
        display: flex; align-items: center; gap: 10px; padding: 12px 13px; border: 1.5px solid var(--border-color);
        border-radius: 14px; background: var(--surface-secondary); color: var(--text-dark); font-size: 13px; font-weight: 700;
        transition: border-color .15s, background-color .15s, transform .15s;
    }
    .rmc-type-box i { width: 30px; height: 30px; flex: 0 0 30px; display: grid; place-items: center; border-radius: 9px; background: var(--accent-soft); color: var(--primary); font-size: 14px; }
    .rmc-type:hover .rmc-type-box { border-color: var(--primary); transform: translateY(-1px); }
    .rmc-type:has(input:checked) .rmc-type-box { border-color: var(--primary); background: var(--accent-soft); box-shadow: 0 0 0 3px var(--accent-soft); }
    .rmc-type:has(input:checked) .rmc-type-box i { background: var(--primary); color: var(--on-accent); }
    .rmc-type:has(input:focus-visible) .rmc-type-box { outline: 3px solid #ffb627; outline-offset: 2px; }

    /* ---------- Notification options ---------- */
    .rmc-offsets { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 10px; }
    .rmc-offset { position: relative; display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 1.5px solid var(--border-color); border-radius: 14px; background: var(--surface-secondary); cursor: pointer; transition: border-color .15s, background-color .15s, opacity .15s; }
    .rmc-offset input { position: absolute; opacity: 0; inset: 0; cursor: pointer; }
    .rmc-offset-box { width: 22px; height: 22px; flex: 0 0 22px; display: grid; place-items: center; border-radius: 7px; border: 1.5px solid var(--border-soft); background: var(--input-bg); color: transparent; font-size: 13px; transition: all .15s; }
    .rmc-offset-copy strong { display: block; font-size: 13px; font-weight: 700; color: var(--text-dark); }
    .rmc-offset-copy small { display: block; margin-top: 1px; font-size: 11.5px; color: var(--muted-text); }
    .rmc-offset:hover { border-color: var(--primary); }
    .rmc-offset:has(input:checked) { border-color: var(--primary); background: var(--accent-soft); }
    .rmc-offset:has(input:checked) .rmc-offset-box { background: var(--primary); border-color: var(--primary); color: var(--on-accent); }
    .rmc-offset:has(input:focus-visible) { outline: 3px solid #ffb627; outline-offset: 2px; }
    .rmc-offset.is-past { opacity: .5; cursor: not-allowed; }
    .rmc-offset.is-past input { cursor: not-allowed; }
    .rmc-offset.is-past:hover { border-color: var(--border-color); }
    .rmc-offset-hint { margin-top: 12px; padding: 10px 13px; border-radius: 12px; background: var(--surface-tertiary); color: var(--muted-text); font-size: 12.5px; display: none; }
    .rmc-offset-hint.is-on { display: flex; gap: 8px; align-items: center; }

    /* ---------- Priority ---------- */
    .rmc-prio { display: grid; gap: 8px; }
    .rmc-prio label { position: relative; display: block; cursor: pointer; }
    .rmc-prio input { position: absolute; opacity: 0; inset: 0; cursor: pointer; }
    .rmc-prio-box { display: flex; align-items: center; gap: 12px; padding: 11px 13px; border: 1.5px solid var(--border-color); border-radius: 14px; background: var(--surface-secondary); transition: border-color .15s, background-color .15s; }
    .rmc-prio-box i { width: 32px; height: 32px; flex: 0 0 32px; display: grid; place-items: center; border-radius: 10px; font-size: 15px; }
    .rmc-prio-box strong { display: block; font-size: 13px; color: var(--text-dark); }
    .rmc-prio-box small { display: block; font-size: 11.5px; color: var(--muted-text); }
    .rmc-prio label:hover .rmc-prio-box { border-color: var(--border-soft); }
    .rmc-prio label:has(input:focus-visible) .rmc-prio-box { outline: 3px solid #ffb627; outline-offset: 2px; }

    .rmc-prio .p-urgent i    { background: rgba(220, 53, 69, .13);  color: #dc3545; }
    .rmc-prio .p-important i { background: rgba(245, 176, 49, .18); color: #b07800; }
    .rmc-prio .p-normal i    { background: var(--surface-tertiary); color: var(--muted-text); }
    html[data-theme="dark"] .rmc-prio .p-urgent i    { color: #ff7b87; }
    html[data-theme="dark"] .rmc-prio .p-important i { color: #f2c45f; }
    .rmc-prio label:has(input:checked) .p-urgent    { border-color: #dc3545; background: rgba(220, 53, 69, .08); }
    .rmc-prio label:has(input:checked) .p-important { border-color: #e0a12c; background: rgba(245, 176, 49, .10); }
    .rmc-prio label:has(input:checked) .p-normal    { border-color: var(--primary); background: var(--accent-soft); }

    /* ---------- Live preview ---------- */
    .rmc-pv-label { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px 0; font-size: 12px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: var(--muted-text); }
    .rmc-pv-live { display: inline-flex; align-items: center; gap: 6px; font-size: 10.5px; color: #12805a; }
    .rmc-pv-live::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; animation: rmcPulse 1.6s ease-in-out infinite; }
    html[data-theme="dark"] .rmc-pv-live { color: #58d9a2; }
    @keyframes rmcPulse { 50% { opacity: .25; } }

    .rmc-pv { padding: 16px 20px 20px; }
    .rmc-pv-top { display: flex; gap: 14px; align-items: flex-start; }
    .rmc-datebox { width: 60px; flex: 0 0 60px; text-align: center; border: 1.5px solid var(--border-color); border-radius: 14px; overflow: hidden; background: var(--surface-secondary); }
    .rmc-datebox span { display: block; padding: 3px 0; background: var(--primary); color: var(--on-accent); font-size: 10.5px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .rmc-datebox strong { display: block; padding: 5px 0 6px; font-size: 24px; font-weight: 800; line-height: 1; color: var(--text-dark); }
    .rmc-pv-main { min-width: 0; }
    .rmc-pv-title { font-size: 16px; font-weight: 800; line-height: 1.3; color: var(--text-dark); overflow-wrap: anywhere; }
    .rmc-pv-title.is-empty { color: var(--muted-text); font-weight: 700; }
    .rmc-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 7px; }
    .rmc-tag { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 999px; background: var(--surface-tertiary); color: var(--text-dark); font-size: 11.5px; font-weight: 700; }
    .rmc-tag.t-urgent    { background: rgba(220, 53, 69, .13);  color: #dc3545; }
    .rmc-tag.t-important { background: rgba(245, 176, 49, .18); color: #b07800; }
    html[data-theme="dark"] .rmc-tag.t-urgent    { color: #ff7b87; }
    html[data-theme="dark"] .rmc-tag.t-important { color: #f2c45f; }

    .rmc-pv-list { list-style: none; margin: 16px 0 0; padding: 14px 0 0; border-top: 1px dashed var(--border-soft); display: grid; gap: 10px; }
    .rmc-pv-list li { display: flex; gap: 10px; align-items: flex-start; font-size: 13px; color: var(--text-dark); line-height: 1.4; }
    .rmc-pv-list i { width: 18px; margin-top: 1px; color: var(--primary); text-align: center; }
    .rmc-pv-list .is-muted { color: var(--muted-text); }
    .rmc-pv-notify { display: block; font-size: 12px; color: var(--muted-text); margin-top: 2px; }

    /* ---------- Actions ---------- */
    .rmc-actions { padding: 20px; display: grid; gap: 10px; }
    .rmc-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 13px 18px; border: 0; border-radius: 14px; font: 700 14.5px 'Figtree', sans-serif; text-decoration: none; cursor: pointer; transition: filter .15s, transform .15s, background-color .15s; }
    .rmc-btn:active { transform: scale(.98); }
    .rmc-btn-primary { background: var(--primary); color: var(--on-accent); box-shadow: 0 8px 18px color-mix(in srgb, var(--primary) 28%, transparent); }
    .rmc-btn-primary:hover { filter: brightness(1.08); color: var(--on-accent); }
    .rmc-btn-primary:disabled { opacity: .75; cursor: progress; }
    .rmc-btn-ghost { background: transparent; color: var(--muted-text); border: 1.5px solid var(--border-color); }
    .rmc-btn-ghost:hover { border-color: var(--border-soft); background: var(--surface-secondary); color: var(--text-dark); }
    .rmc-note { text-align: center; font-size: 12px; color: var(--muted-text); }
    .rmc-note i { color: var(--primary); }

    @media (max-width: 1100px) {
        .rmc-grid { grid-template-columns: 1fr; }
        .rmc-side { position: static; }
    }
    @media (max-width: 576px) {
        .rmc-two { grid-template-columns: 1fr; }
        .rmc-card-head, .rmc-card-body { padding-left: 18px; padding-right: 18px; }
        .rmc-head h1 { font-size: 22px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .rmc *, .rmc *::before { transition: none !important; animation: none !important; }
    }
</style>
@endpush

@section('content')

<div class="rmc">

    {{-- ================= HEADER ================= --}}
    <header class="rmc-head">
        <a href="{{ route('reminders.index') }}" class="rmc-back" title="Back to Schedule & Reminders" aria-label="Back to Schedule and Reminders">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1>Create Reminder</h1>
            <p>Schedule a meeting, hearing, operation, event, or other important activity.</p>
        </div>
    </header>

    <form method="POST" action="{{ route('reminders.store') }}" id="reminderForm">
        @csrf

        <div class="rmc-grid">

            {{-- ================= LEFT: FORM ================= --}}
            <div>

                {{-- 1. WHAT --}}
                <section class="rmc-card">
                    <div class="rmc-card-head">
                        <span class="rmc-step">1</span>
                        <div>
                            <h2>What is it?</h2>
                            <p>Give it a clear title and pick the type of activity.</p>
                        </div>
                    </div>

                    <div class="rmc-card-body">

                        <div class="rmc-field">
                            <label for="title" class="rmc-label">
                                <span>Title <span class="req">*</span></span>
                                <span class="rmc-count" id="titleCount">0 / 255</span>
                            </label>
                            <input
                                type="text" id="title" name="title"
                                class="rmc-input is-lg @error('title') is-invalid @enderror"
                                value="{{ old('title') }}" maxlength="255" required autofocus
                                placeholder="e.g. Monthly Barangay Officials Meeting"
                            >
                            @error('title') <div class="rmc-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="rmc-field">
                            <div class="rmc-label"><span>Type <span class="req">*</span></span></div>
                            <div class="rmc-types" role="radiogroup" aria-label="Schedule type">
                                @foreach ($types as $index => $type)
                                    <label class="rmc-type">
                                        <input
                                            type="radio" name="type" value="{{ $type }}"
                                            @checked($selectedType === $type) {{ $index === 0 ? 'required' : '' }}
                                        >
                                        <span class="rmc-type-box">
                                            <i class="bi bi-{{ $typeIcons[$type] ?? 'calendar3' }}"></i>
                                            <span>{{ $type }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('type') <div class="rmc-error">{{ $message }}</div> @enderror
                        </div>

                    </div>
                </section>

                {{-- 2. WHEN & WHERE --}}
                <section class="rmc-card">
                    <div class="rmc-card-head">
                        <span class="rmc-step">2</span>
                        <div>
                            <h2>When and where?</h2>
                            <p>Pick a date and time. Quick buttons save a few clicks.</p>
                        </div>
                    </div>

                    <div class="rmc-card-body">

                        <div class="rmc-two">
                            <div class="rmc-field">
                                <label for="scheduled_date" class="rmc-label"><span>Date <span class="req">*</span></span></label>
                                <input
                                    type="date" id="scheduled_date" name="scheduled_date"
                                    class="rmc-input @error('scheduled_date') is-invalid @enderror"
                                    value="{{ old('scheduled_date') }}" required
                                >
                                <div class="rmc-chips" id="dateChips" aria-label="Quick dates">
                                    <button type="button" class="rmc-chip" data-days="0">Today</button>
                                    <button type="button" class="rmc-chip" data-days="1">Tomorrow</button>
                                    <button type="button" class="rmc-chip" data-days="7">In a week</button>
                                </div>
                                @error('scheduled_date') <div class="rmc-error">{{ $message }}</div> @enderror
                            </div>

                            <div class="rmc-field" style="margin-top:0">
                                <label for="scheduled_time" class="rmc-label"><span>Time <span class="req">*</span></span></label>
                                <input
                                    type="time" id="scheduled_time" name="scheduled_time"
                                    class="rmc-input @error('scheduled_time') is-invalid @enderror"
                                    value="{{ old('scheduled_time') }}" required
                                >
                                <div class="rmc-chips" id="timeChips" aria-label="Quick times">
                                    <button type="button" class="rmc-chip" data-time="08:00">8:00 AM</button>
                                    <button type="button" class="rmc-chip" data-time="09:00">9:00 AM</button>
                                    <button type="button" class="rmc-chip" data-time="10:00">10:00 AM</button>
                                    <button type="button" class="rmc-chip" data-time="13:00">1:00 PM</button>
                                    <button type="button" class="rmc-chip" data-time="15:00">3:00 PM</button>
                                </div>
                                @error('scheduled_time') <div class="rmc-error">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="rmc-field">
                            <label for="location" class="rmc-label">
                                <span>Location</span><span class="opt">Optional</span>
                            </label>
                            <div class="rmc-iconfield">
                                <i class="bi bi-geo-alt"></i>
                                <input
                                    type="text" id="location" name="location"
                                    class="rmc-input @error('location') is-invalid @enderror"
                                    value="{{ old('location') }}" maxlength="255" placeholder="e.g. Barangay Hall"
                                >
                            </div>
                            @error('location') <div class="rmc-error">{{ $message }}</div> @enderror
                        </div>

                    </div>
                </section>

                {{-- 3. NOTES --}}
                <section class="rmc-card">
                    <div class="rmc-card-head">
                        <span class="rmc-step">3</span>
                        <div>
                            <h2>Notes and preparation</h2>
                            <p>Anything that helps the assigned personnel get ready.</p>
                        </div>
                    </div>

                    <div class="rmc-card-body">
                        <div class="rmc-field">
                            <label for="notes" class="rmc-label">
                                <span>Notes <span class="opt">Optional</span></span>
                                <span class="rmc-count" id="notesCount">0 / 5000</span>
                            </label>
                            <textarea
                                id="notes" name="notes" maxlength="5000"
                                class="rmc-textarea @error('notes') is-invalid @enderror"
                                placeholder="Example: Prepare previous meeting minutes, attendance sheet, and agenda."
                            >{{ old('notes') }}</textarea>
                            @error('notes') <div class="rmc-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </section>

                {{-- 4. NOTIFY --}}
                <section class="rmc-card">
                    <div class="rmc-card-head">
                        <span class="rmc-step">4</span>
                        <div>
                            <h2>Remind me</h2>
                            <p>Choose one or more. The exact time updates as you set the date.</p>
                        </div>
                    </div>

                    <div class="rmc-card-body">
                        <div class="rmc-offsets" id="offsets">
                            @foreach ($reminderOptions as $value => $label)
                                @php $checked = in_array($value, $selectedOffsets, true); @endphp
                                <label class="rmc-offset" for="reminder_{{ $value }}">
                                    <input
                                        class="rmc-offset-input" type="checkbox" name="reminder_offsets[]"
                                        value="{{ $value }}" id="reminder_{{ $value }}" @checked($checked)
                                    >
                                    <span class="rmc-offset-box"><i class="bi bi-check-lg"></i></span>
                                    <span class="rmc-offset-copy">
                                        <strong>{{ $label }}</strong>
                                        <small class="rmc-offset-when">Set date and time first</small>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div class="rmc-offset-hint" id="offsetHint" role="status">
                            <i class="bi bi-info-circle"></i>
                            <span>No reminder selected yet. Choose at least one so you are notified.</span>
                        </div>

                        @error('reminder_offsets')   <div class="rmc-error">{{ $message }}</div> @enderror
                        @error('reminder_offsets.*') <div class="rmc-error">{{ $message }}</div> @enderror
                    </div>
                </section>

            </div>

            {{-- ================= RIGHT: PRIORITY / PREVIEW / ACTIONS ================= --}}
            <aside class="rmc-side">

                <section class="rmc-card">
                    <div class="rmc-card-head">
                        <div>
                            <h2>Priority</h2>
                            <p>How important is it?</p>
                        </div>
                    </div>
                    <div class="rmc-card-body">
                        <div class="rmc-prio" role="radiogroup" aria-label="Priority">
                            @foreach ($priorities as $value => $label)
                                <label>
                                    <input type="radio" name="priority" value="{{ $value }}" @checked($selectedPriority === $value)>
                                    <span class="rmc-prio-box p-{{ $value }}">
                                        <i class="bi bi-{{ $priorityMeta[$value]['icon'] ?? 'dash-circle-fill' }}"></i>
                                        <span>
                                            <strong>{{ $label }}</strong>
                                            <small>{{ $priorityMeta[$value]['hint'] ?? '' }}</small>
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('priority') <div class="rmc-error">{{ $message }}</div> @enderror
                    </div>
                </section>

                <section class="rmc-card" aria-live="polite">
                    <div class="rmc-pv-label">
                        <span>Preview</span>
                        <span class="rmc-pv-live">Live</span>
                    </div>
                    <div class="rmc-pv">
                        <div class="rmc-pv-top">
                            <div class="rmc-datebox">
                                <span id="pvMonth">---</span>
                                <strong id="pvDay">--</strong>
                            </div>
                            <div class="rmc-pv-main">
                                <div class="rmc-pv-title is-empty" id="pvTitle">Untitled reminder</div>
                                <div class="rmc-tags">
                                    <span class="rmc-tag" id="pvType"><i class="bi bi-calendar3"></i> <span>Type</span></span>
                                    <span class="rmc-tag" id="pvPriority">Normal</span>
                                </div>
                            </div>
                        </div>

                        <ul class="rmc-pv-list">
                            <li><i class="bi bi-clock"></i><span id="pvWhen" class="is-muted">Pick a date and time</span></li>
                            <li><i class="bi bi-hourglass-split"></i><span id="pvRel" class="is-muted">Not scheduled yet</span></li>
                            <li><i class="bi bi-geo-alt"></i><span id="pvWhere" class="is-muted">No location</span></li>
                            <li>
                                <i class="bi bi-bell"></i>
                                <span>
                                    <span id="pvNotify" class="is-muted">No reminder selected</span>
                                </span>
                            </li>
                        </ul>
                    </div>
                </section>

                <section class="rmc-card">
                    <div class="rmc-actions">
                        <button type="submit" class="rmc-btn rmc-btn-primary" id="saveBtn">
                            <i class="bi bi-calendar-plus"></i> Save Reminder
                        </button>
                        <a href="{{ route('reminders.index') }}" class="rmc-btn rmc-btn-ghost">Cancel</a>
                        <div class="rmc-note"><i class="bi bi-shield-check me-1"></i>Saved reminders are recorded in the audit trail.</div>
                    </div>
                </section>

            </aside>

        </div>
    </form>

</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const $  = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

    const form   = $('#reminderForm');
    if (!form) return;

    const title  = $('#title'),  notes = $('#notes'), place = $('#location');
    const date   = $('#scheduled_date'), time = $('#scheduled_time');
    const saveBtn = $('#saveBtn');

    const OFFSET_MIN = @json($offsetMinutes);
    const TYPE_ICONS = @json($typeIcons);

    const pad = n => String(n).padStart(2, '0');
    const ymd = d => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    const fmtDay  = d => d.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
    const fmtShort = d => d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    const fmtTime = d => d.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
    const startOfDay = d => new Date(d.getFullYear(), d.getMonth(), d.getDate());

    /* Past dates cannot be picked */
    date.min = ymd(new Date());

    function getStart() {
        if (!date.value || !time.value) return null;
        const d = new Date(date.value + 'T' + time.value + ':00');
        return isNaN(d.getTime()) ? null : d;
    }

    function relative(start) {
        const now  = new Date();
        const mins = Math.round((start - now) / 60000);
        if (mins < -1) return 'This time has already passed';
        if (mins < 60) return 'Starts in ' + Math.max(mins, 0) + ' minute' + (mins === 1 ? '' : 's');
        const days = Math.round((startOfDay(start) - startOfDay(now)) / 864e5);
        if (days === 0) { const h = Math.round(mins / 60); return 'Today, in about ' + h + ' hour' + (h === 1 ? '' : 's'); }
        if (days === 1) return 'Tomorrow';
        return 'In ' + days + ' days';
    }

    /* ---------- Counters ---------- */
    function counter(input, el, max) {
        const n = input.value.length;
        el.textContent = n + ' / ' + max;
        el.classList.toggle('is-near', n > max * 0.9);
    }

    /* ---------- Quick chips ---------- */
    function syncChips() {
        const today = startOfDay(new Date());
        $$('#dateChips .rmc-chip').forEach(function (chip) {
            const d = new Date(today); d.setDate(d.getDate() + parseInt(chip.dataset.days, 10));
            chip.classList.toggle('is-on', date.value === ymd(d));
        });
        $$('#timeChips .rmc-chip').forEach(function (chip) {
            chip.classList.toggle('is-on', time.value === chip.dataset.time);
        });
    }

    $('#dateChips').addEventListener('click', function (e) {
        const chip = e.target.closest('.rmc-chip'); if (!chip) return;
        const d = startOfDay(new Date()); d.setDate(d.getDate() + parseInt(chip.dataset.days, 10));
        date.value = ymd(d);
        refresh();
    });

    $('#timeChips').addEventListener('click', function (e) {
        const chip = e.target.closest('.rmc-chip'); if (!chip) return;
        time.value = chip.dataset.time;
        refresh();
    });

    /* ---------- Pre-reminder options: real notify time, skip those in the past ---------- */
    function updateOffsets() {
        const start = getStart(), now = Date.now();

        $$('.rmc-offset').forEach(function (el) {
            const input = $('input', el), when = $('.rmc-offset-when', el);
            const mins  = OFFSET_MIN[input.value];

            if (start === null || mins === undefined) {
                input.disabled = false;
                el.classList.remove('is-past');
                when.textContent = start === null ? 'Set date and time first' : '';
                return;
            }

            const at = new Date(start.getTime() - mins * 60000);

            if (at.getTime() < now - 60000) {
                input.checked  = false;
                input.disabled = true;
                el.classList.add('is-past');
                when.textContent = 'Already passed';
            } else {
                input.disabled = false;
                el.classList.remove('is-past');
                when.textContent = fmtShort(at) + ' at ' + fmtTime(at);
            }
        });

        const any = $$('.rmc-offset input:checked').length > 0;
        $('#offsetHint').classList.toggle('is-on', !any);
    }

    /* ---------- Live preview ---------- */
    function updatePreview() {
        const t = title.value.trim();
        const pvTitle = $('#pvTitle');
        pvTitle.textContent = t || 'Untitled reminder';
        pvTitle.classList.toggle('is-empty', !t);

        const typeInput = $('input[name="type"]:checked');
        const pvType = $('#pvType');
        $('span', pvType).textContent = typeInput ? typeInput.value : 'Type';
        $('i', pvType).className = 'bi bi-' + ((typeInput && TYPE_ICONS[typeInput.value]) || 'calendar3');

        const pr = $('input[name="priority"]:checked');
        const pvPr = $('#pvPriority');
        const prVal = pr ? pr.value : 'normal';
        pvPr.textContent = pr ? pr.closest('label').querySelector('strong').textContent : 'Normal';
        pvPr.className = 'rmc-tag' + (prVal === 'urgent' ? ' t-urgent' : prVal === 'important' ? ' t-important' : '');

        const start = getStart();
        const dOnly = date.value ? new Date(date.value + 'T00:00:00') : null;

        $('#pvMonth').textContent = dOnly ? dOnly.toLocaleDateString(undefined, { month: 'short' }) : '---';
        $('#pvDay').textContent   = dOnly ? dOnly.getDate() : '--';

        const when = $('#pvWhen'), rel = $('#pvRel');
        if (start) {
            when.textContent = fmtDay(start) + ' at ' + fmtTime(start);
            when.classList.remove('is-muted');
            rel.textContent = relative(start);
            rel.classList.toggle('is-muted', start < new Date());
        } else if (dOnly) {
            when.textContent = fmtDay(dOnly) + ' (add a time)';
            when.classList.remove('is-muted');
            rel.textContent = 'Not scheduled yet';
            rel.classList.add('is-muted');
        } else {
            when.textContent = 'Pick a date and time';
            when.classList.add('is-muted');
            rel.textContent = 'Not scheduled yet';
            rel.classList.add('is-muted');
        }

        const where = $('#pvWhere'), loc = place.value.trim();
        where.textContent = loc || 'No location';
        where.classList.toggle('is-muted', !loc);

        const picked = $$('.rmc-offset input:checked').map(function (i) {
            return i.closest('.rmc-offset').querySelector('strong').textContent.trim();
        });
        const pvN = $('#pvNotify');
        pvN.textContent = picked.length ? picked.join(', ') : 'No reminder selected';
        pvN.classList.toggle('is-muted', !picked.length);
    }

    function refresh() {
        counter(title, $('#titleCount'), 255);
        counter(notes, $('#notesCount'), 5000);
        syncChips();
        updateOffsets();
        updatePreview();
    }

    /* ---------- Wire up ---------- */
    ['input', 'change'].forEach(function (evt) {
        form.addEventListener(evt, refresh);
    });

    /* Ctrl / Cmd + Enter saves from anywhere in the form */
    form.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            form.requestSubmit();
        }
    });

    /* Prevent double submits and show progress */
    form.addEventListener('submit', function (e) {
        if (form.dataset.busy === '1') { e.preventDefault(); return; }
        form.dataset.busy = '1';
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Saving…';
    });

    /* Browser back button restores the page with the button still locked */
    window.addEventListener('pageshow', function () {
        form.dataset.busy = '0';
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="bi bi-calendar-plus"></i> Save Reminder';
        refresh();
    });

    refresh();
})();
</script>
@endpush