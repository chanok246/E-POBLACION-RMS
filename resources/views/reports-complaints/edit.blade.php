@extends('layouts.app')

@section('title', 'Edit Report / Complaint')

{{-- Shared button + text-field styles --}}
@push('styles')

@endpush

@section('content')

@php
    $r = $reportsComplaint;

    /* Safe date/time formatting whether the attribute is cast or a plain string. */
    $fmt = function ($v, $format) {
        if (!$v) { return ''; }
        if (is_string($v)) { return $format === 'H:i' ? substr($v, 0, 5) : substr($v, 0, 10); }
        return $v->format($format);
    };

    /*
     * Category suggestions. Hints only: the Category field is free text.
     */
    $communitySuggest = collect($communityCategories)->merge([
        'Environmental Incident', 'Snake Sighting', 'Stray Animals',
        'Flooding', 'Garbage Disposal', 'Damaged Street Light',
    ])->unique()->values()->all();

    $personSuggest = collect($personCategories)->merge([
        'Personal Conflict', 'Verbal Harassment', 'Property Dispute',
    ])->unique()->values()->all();

    /* Purok: use the saved value, or recover it from an older combined location. */
    $savedPurok = old('purok', $r->purok);
    if (empty($savedPurok) && !empty($r->location)) {
        $first = trim(explode(',', $r->location)[0] ?? '');
        if (str_starts_with(strtolower($first), 'purok') || strtolower($first) === 'centro') {
            $savedPurok = $first;
        }
    }

    $isCentro   = fn ($p) => (bool) preg_match('/^\s*(centro|sentro)\s*$/i', (string) $p);
    $isPurokOne = fn ($p) => (bool) preg_match('/^\s*(purok\s*)?(1|uno)\s*([-,]?\s*(centro|sentro))?\s*$/i', (string) $p);
    $hasPurokOne = collect($puroks)->contains(fn ($p) => $isPurokOne($p));
    $purokOptions = [];
    foreach ($puroks as $p) {
        if ($isCentro($p)) {
            if ($hasPurokOne) { continue; }
            $purokOptions[] = ['value' => $p, 'label' => 'Purok 1 - Centro', 'order' => 1];
        } elseif ($isPurokOne($p)) {
            $purokOptions[] = ['value' => $p, 'label' => 'Purok 1 - Centro', 'order' => 1];
        } else {
            preg_match('/\d+/', (string) $p, $m);
            $purokOptions[] = ['value' => $p, 'label' => $p, 'order' => isset($m[0]) ? (int) $m[0] : 999];
        }
    }
    $purokOptions = collect($purokOptions)->sortBy('order')->values()->all();

    $currentType   = old('report_type', $r->report_type);
    $currentStatus = old('status', $r->status);

    $compSrc = old('complainant_source', ($r->complainant_id || old('complainant_id')) ? 'resident' : 'manual');
    $subjSrc = old('subject_source', ($r->subject_resident_id || old('subject_resident_id')) ? 'resident' : 'manual');

    $selectedComplainant = old('complainant_id', $r->complainant_id);
    $selectedSubject     = old('subject_resident_id', $r->subject_resident_id);

    /* Closed is set only through the dedicated Close action. */
    $statusChoices = collect($statuses)->reject(fn ($s) => $s === 'Closed' && $r->status !== 'Closed')->values()->all();

    $showRoute = route('reports-complaints.show', ['reports_complaint' => $r->id]);
@endphp

<div class="container-fluid py-4 rcp" style="max-width: 980px;">

    {{-- HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Edit Report / Complaint</h3>
            <div class="rcp-sub">
                Updating <b class="rcp-code">{{ $r->record_code }}</b>
                &middot; saved status: <span class="rcp-pill">{{ $r->status }}</span>
            </div>
        </div>
        <a href="{{ $showRoute }}" class="btn btn-sm rcp-btn-ghost">
            <i class="bi bi-arrow-left me-1"></i> Back to record
        </a>
    </div>

    {{-- VALIDATION ERRORS --}}
    @if ($errors->any())
        <div class="rcp-note rcp-note-danger mb-3" role="alert">
            <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle me-1"></i> Please correct the following:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('reports-complaints.update', ['reports_complaint' => $r->id]) }}"
          id="editForm" novalidate>
        @csrf
        @method('PUT')

        {{-- 1. TYPE & CATEGORY --}}
        <section class="rcp-card">
            <div class="rcp-card-h">
                <span class="rcp-step">1</span>
                <div>
                    <h5 class="rcp-title">Type &amp; category</h5>
                    <div class="rcp-sub">The type is the kind of record; the category describes the specific concern.</div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <input type="radio" class="btn-check" name="report_type" id="typeCommunity"
                           value="community" autocomplete="off" {{ $currentType === 'community' ? 'checked' : '' }}>
                    <label for="typeCommunity" class="rcp-choice">
                        <i class="bi bi-buildings rcp-choice-ic"></i>
                        <span>
                            <b>Community Report</b>
                            <small>Environment, roads, drainage, garbage, flooding, animals and other public concerns.</small>
                        </span>
                    </label>
                </div>
                <div class="col-md-6">
                    <input type="radio" class="btn-check" name="report_type" id="typePerson"
                           value="person" autocomplete="off" {{ $currentType === 'person' ? 'checked' : '' }}>
                    <label for="typePerson" class="rcp-choice">
                        <i class="bi bi-person-exclamation rcp-choice-ic"></i>
                        <span>
                            <b>Person Complaint</b>
                            <small>A complaint or dispute between a complainant and another person.</small>
                        </span>
                    </label>
                </div>
            </div>
            @error('report_type') <div class="rcp-error mb-2">{{ $message }}</div> @enderror

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="category" class="rcp-lbl">Category <span class="rcp-req">*</span></label>
                    <input type="text" name="category" id="category" list="categoryOptions"
                           class="form-control @error('category') is-invalid @enderror"
                           value="{{ old('category', $r->category) }}" maxlength="255" autocomplete="off" required
                           placeholder="Type any category or pick a suggestion">
                    <datalist id="categoryOptions"></datalist>
                    <div class="rcp-hint">Suggestions only. You can type any category.</div>
                    @error('category') <div class="rcp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="record_code" class="rcp-lbl">Record code</label>
                    <input type="text" id="record_code" class="form-control" value="{{ $r->record_code }}" readonly>
                </div>
                <div class="col-12">
                    <label for="title" class="rcp-lbl">Title <span class="rcp-req">*</span></label>
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $r->title) }}" maxlength="255" required>
                    @error('title') <div class="rcp-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </section>

        {{-- 2. COMPLAINANT --}}
        <section class="rcp-card">
            <div class="rcp-card-h">
                <span class="rcp-step">2</span>
                <div>
                    <h5 class="rcp-title">Complainant / reporting person</h5>
                    <div class="rcp-sub">Who submitted the report or complaint.</div>
                </div>
            </div>

            <div class="rcp-seg mb-3">
                <input type="radio" class="btn-check" name="complainant_source" id="complainantResidentSource"
                       value="resident" autocomplete="off" {{ $compSrc === 'resident' ? 'checked' : '' }}>
                <label for="complainantResidentSource"><i class="bi bi-person-check me-1"></i> Poblacion resident</label>

                <input type="radio" class="btn-check" name="complainant_source" id="complainantManualSource"
                       value="manual" autocomplete="off" {{ $compSrc === 'manual' ? 'checked' : '' }}>
                <label for="complainantManualSource"><i class="bi bi-person-vcard me-1"></i> Non-resident / not found</label>
            </div>

            <div id="complainantResidentSection">
                <label for="complainant_id" class="rcp-lbl">Select resident <span class="rcp-req">*</span></label>
                <select name="complainant_id" id="complainant_id" class="form-select">
                    <option value="">-- Select Resident --</option>
                    @foreach ($residents as $resident)
                        @php
                            $rn = trim($resident->first_name . ' ' . ($resident->middle_name ? $resident->middle_name . ' ' : '') . $resident->last_name);
                        @endphp
                        <option value="{{ $resident->id }}" {{ (string) $selectedComplainant === (string) $resident->id ? 'selected' : '' }}>
                            {{ $rn }}@if ($resident->address) — {{ $resident->address }}@endif
                        </option>
                    @endforeach
                </select>
                <div class="rcp-hint">Type a name or address to search the resident records.</div>
            </div>

            <div id="complainantManualSection" hidden>
                <div class="rcp-note rcp-note-info mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    For people who are not registered Poblacion residents.
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="complainant_name" class="rcp-lbl">Full name <span class="rcp-req">*</span></label>
                        <input type="text" name="complainant_name" id="complainant_name" class="form-control"
                               value="{{ old('complainant_name', $r->complainant_name) }}">
                    </div>
                    <div class="col-md-6">
                        <label for="complainant_contact_number" class="rcp-lbl">Contact number</label>
                        <input type="text" name="complainant_contact_number" id="complainant_contact_number" class="form-control"
                               value="{{ old('complainant_contact_number', $r->complainant_contact_number) }}">
                    </div>
                    <div class="col-12">
                        <label for="complainant_address" class="rcp-lbl">Complete address</label>
                        <input type="text" name="complainant_address" id="complainant_address" class="form-control"
                               value="{{ old('complainant_address', $r->complainant_address) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="complainant_barangay" class="rcp-lbl">Barangay</label>
                        <input type="text" name="complainant_barangay" id="complainant_barangay" class="form-control"
                               value="{{ old('complainant_barangay', $r->complainant_barangay) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="complainant_municipality" class="rcp-lbl">Municipality / City</label>
                        <input type="text" name="complainant_municipality" id="complainant_municipality" class="form-control"
                               value="{{ old('complainant_municipality', $r->complainant_municipality) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="complainant_province" class="rcp-lbl">Province</label>
                        <input type="text" name="complainant_province" id="complainant_province" class="form-control"
                               value="{{ old('complainant_province', $r->complainant_province) }}">
                    </div>
                </div>
            </div>

            <div class="rcp-error" id="errComplainant" hidden></div>
            @error('complainant_id') <div class="rcp-error">{{ $message }}</div> @enderror
            @error('complainant_name') <div class="rcp-error">{{ $message }}</div> @enderror
        </section>

        {{-- 3. RESPONDENT (person complaints only) --}}
        <section class="rcp-card" id="respondentCard" hidden>
            <div class="rcp-card-h">
                <span class="rcp-step">3</span>
                <div>
                    <h5 class="rcp-title">Respondent / subject</h5>
                    <div class="rcp-sub">The person the complaint is about.</div>
                </div>
            </div>

            <div class="rcp-seg mb-3">
                <input type="radio" class="btn-check" name="subject_source" id="subjectResidentSource"
                       value="resident" autocomplete="off" {{ $subjSrc === 'resident' ? 'checked' : '' }}>
                <label for="subjectResidentSource"><i class="bi bi-person-check me-1"></i> Poblacion resident</label>

                <input type="radio" class="btn-check" name="subject_source" id="subjectManualSource"
                       value="manual" autocomplete="off" {{ $subjSrc === 'manual' ? 'checked' : '' }}>
                <label for="subjectManualSource"><i class="bi bi-person-vcard me-1"></i> Non-resident / not found</label>
            </div>

            <div id="subjectResidentSection">
                <label for="subject_resident_id" class="rcp-lbl">Select resident <span class="rcp-req">*</span></label>
                <select name="subject_resident_id" id="subject_resident_id" class="form-select">
                    <option value="">-- Select Resident --</option>
                    @foreach ($residents as $resident)
                        @php
                            $rn = trim($resident->first_name . ' ' . ($resident->middle_name ? $resident->middle_name . ' ' : '') . $resident->last_name);
                        @endphp
                        <option value="{{ $resident->id }}" {{ (string) $selectedSubject === (string) $resident->id ? 'selected' : '' }}>
                            {{ $rn }}@if ($resident->address) — {{ $resident->address }}@endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="subjectManualSection" hidden>
                <div class="rcp-note rcp-note-info mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    The respondent will not be added to the Residents module.
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="subject_name" class="rcp-lbl">Full name <span class="rcp-req">*</span></label>
                        <input type="text" name="subject_name" id="subject_name" class="form-control"
                               value="{{ old('subject_name', $r->subject_name) }}">
                    </div>
                    <div class="col-md-6">
                        <label for="subject_contact_number" class="rcp-lbl">Contact number</label>
                        <input type="text" name="subject_contact_number" id="subject_contact_number" class="form-control"
                               value="{{ old('subject_contact_number', $r->subject_contact_number) }}">
                    </div>
                    <div class="col-12">
                        <label for="subject_address" class="rcp-lbl">Complete address</label>
                        <input type="text" name="subject_address" id="subject_address" class="form-control"
                               value="{{ old('subject_address', $r->subject_address) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="subject_barangay" class="rcp-lbl">Barangay</label>
                        <input type="text" name="subject_barangay" id="subject_barangay" class="form-control"
                               value="{{ old('subject_barangay', $r->subject_barangay) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="subject_municipality" class="rcp-lbl">Municipality / City</label>
                        <input type="text" name="subject_municipality" id="subject_municipality" class="form-control"
                               value="{{ old('subject_municipality', $r->subject_municipality) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="subject_province" class="rcp-lbl">Province</label>
                        <input type="text" name="subject_province" id="subject_province" class="form-control"
                               value="{{ old('subject_province', $r->subject_province) }}">
                    </div>
                </div>
            </div>

            <div class="rcp-error" id="errSubject" hidden></div>
            @error('subject_resident_id') <div class="rcp-error">{{ $message }}</div> @enderror
            @error('subject_name') <div class="rcp-error">{{ $message }}</div> @enderror

            <div class="mt-3">
                <label for="relationship_to_complainant" class="rcp-lbl">Relationship to complainant</label>
                <input type="text" name="relationship_to_complainant" id="relationship_to_complainant" class="form-control"
                       value="{{ old('relationship_to_complainant', $r->relationship_to_complainant) }}"
                       placeholder="e.g. Neighbor, Relative, Former Partner, Acquaintance">
            </div>

            <div class="rcp-box mt-3">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" name="summons_requested"
                           id="summons_requested" value="1"
                           {{ old('summons_requested', $r->summons_requested) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="summons_requested">Request subpoena / summons</label>
                </div>
                <div class="rcp-hint mb-0">Use only when the respondent or others must be formally called for barangay proceedings.</div>
            </div>

            <div class="rcp-box mt-3" id="witnessesSection" hidden>
                <label for="witnesses" class="rcp-lbl">Witness name/s</label>
                <textarea name="witnesses" id="witnesses" rows="4" maxlength="5000" class="form-control"
                          placeholder="One witness name per line">{{ old('witnesses', $r->witnesses) }}</textarea>
                <div class="rcp-hint mb-0">Entered manually; used on the subpoena.</div>
            </div>
        </section>

        {{-- 4. HEARING (person complaints only) --}}
        <section class="rcp-card" id="hearingScheduleCard" hidden>
            <div class="rcp-card-h">
                <span class="rcp-step">4</span>
                <div>
                    <h5 class="rcp-title">Hearing / meeting schedule</h5>
                    <div class="rcp-sub">Optional. Adding a hearing date sets an active complaint to “Hearing Scheduled”. Clearing the date removes the schedule.</div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="hearing_date" class="rcp-lbl">Hearing date</label>
                    <input type="date" name="hearing_date" id="hearing_date" class="form-control"
                           value="{{ old('hearing_date', $fmt($r->hearing_date, 'Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label for="hearing_time" class="rcp-lbl">Hearing time</label>
                    <input type="time" name="hearing_time" id="hearing_time" class="form-control"
                           value="{{ old('hearing_time', $fmt($r->hearing_time, 'H:i')) }}">
                </div>
                <div class="col-md-4">
                    <label for="hearing_location" class="rcp-lbl">Location</label>
                    <input type="text" name="hearing_location" id="hearing_location" class="form-control"
                           value="{{ old('hearing_location', $r->hearing_location) }}" placeholder="e.g. Barangay Hall">
                </div>
            </div>
        </section>

        {{-- 5. INCIDENT --}}
        <section class="rcp-card">
            <div class="rcp-card-h">
                <span class="rcp-step" id="incidentStep">3</span>
                <div>
                    <h5 class="rcp-title">Incident details</h5>
                    <div class="rcp-sub">When and where it happened, and what happened.</div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <label for="incident_date" class="rcp-lbl">Incident date <span class="rcp-req">*</span></label>
                    <input type="date" name="incident_date" id="incident_date"
                           class="form-control @error('incident_date') is-invalid @enderror" required
                           value="{{ old('incident_date', $fmt($r->incident_date, 'Y-m-d')) }}">
                    @error('incident_date') <div class="rcp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3 col-6">
                    <label for="incident_time" class="rcp-lbl">Incident time</label>
                    <input type="time" name="incident_time" id="incident_time"
                           class="form-control @error('incident_time') is-invalid @enderror"
                           value="{{ old('incident_time', $fmt($r->incident_time, 'H:i')) }}">
                    @error('incident_time') <div class="rcp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3 col-6">
                    <label for="date_reported" class="rcp-lbl">Date reported</label>
                    <input type="date" name="date_reported" id="date_reported" class="form-control"
                           value="{{ old('date_reported', $fmt($r->date_reported, 'Y-m-d')) }}">
                </div>
                <div class="col-md-3 col-6">
                    <label for="time_reported" class="rcp-lbl">Time reported</label>
                    <input type="time" name="time_reported" id="time_reported" class="form-control"
                           value="{{ old('time_reported', $fmt($r->time_reported, 'H:i')) }}">
                </div>

                <div class="col-md-4">
                    <label for="purok" class="rcp-lbl">Purok <span class="rcp-req">*</span></label>
                    <select name="purok" id="purok" class="form-select @error('purok') is-invalid @enderror" required>
                        <option value="">-- Select Purok --</option>
                        @foreach ($purokOptions as $po)
                            <option value="{{ $po['value'] }}" {{ $savedPurok === $po['value'] ? 'selected' : '' }}>{{ $po['label'] }}</option>
                        @endforeach
                    </select>
                    @error('purok') <div class="rcp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2 col-6">
                    <label for="barangay" class="rcp-lbl">Barangay</label>
                    <input type="text" name="barangay" id="barangay" class="form-control" value="Poblacion" readonly required>
                </div>
                <div class="col-md-2 col-6">
                    <label for="municipality" class="rcp-lbl">Municipality</label>
                    <input type="text" name="municipality" id="municipality" class="form-control" value="Bani" readonly required>
                </div>
                <div class="col-md-2 col-6">
                    <label for="province" class="rcp-lbl">Province</label>
                    <input type="text" name="province" id="province" class="form-control" value="Pangasinan" readonly required>
                </div>
                <div class="col-md-2 col-6">
                    <label for="region" class="rcp-lbl">Region</label>
                    <input type="text" name="region" id="region" class="form-control" value="Region I - Ilocos Region" readonly required>
                </div>
                <div class="col-12">
                    <div class="rcp-hint mb-0"><i class="bi bi-lock-fill me-1"></i> Barangay, municipality, province and region are fixed for this system.</div>
                </div>

                <div class="col-12">
                    <label for="description" class="rcp-lbl">Description <span class="rcp-req">*</span></label>
                    <textarea name="description" id="description" rows="5" required
                              class="form-control @error('description') is-invalid @enderror">{{ old('description', $r->description) }}</textarea>
                    @error('description') <div class="rcp-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </section>

        {{-- 6. STATUS & ASSIGNMENT --}}
        <section class="rcp-card">
            <div class="rcp-card-h">
                <span class="rcp-step" id="statusStep">4</span>
                <div>
                    <h5 class="rcp-title">Status &amp; assignment</h5>
                    <div class="rcp-sub">Change the status only when the case has moved to another stage.</div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="status" class="rcp-lbl">Status <span class="rcp-req">*</span></label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        @foreach ($statusChoices as $status)
                            <option value="{{ $status }}" {{ $currentStatus === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                    @error('status') <div class="rcp-error">{{ $message }}</div> @enderror
                    <div class="rcp-hint" id="statusNote">The status stays <b>{{ $r->status }}</b> unless you choose another one.</div>
                </div>
                <div class="col-md-6">
                    <label for="assigned_to" class="rcp-lbl">Assigned personnel</label>
                    <select name="assigned_to" id="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                        <option value="">-- Unassigned --</option>
                        @foreach ($officials as $person)
                            <option value="{{ $person->id }}" {{ (string) old('assigned_to', $r->assigned_to) === (string) $person->id ? 'selected' : '' }}>
                                {{ $person->name }}@if ($person->role) — {{ ucfirst($person->role) }}@endif
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to') <div class="rcp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label for="remarks" class="rcp-lbl">Remarks</label>
                    <textarea name="remarks" id="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror"
                              placeholder="Internal remarks or processing notes...">{{ old('remarks', $r->remarks) }}</textarea>
                    @error('remarks') <div class="rcp-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </section>

        {{-- ACTIONS: Cancel = gray ghost, Save = indigo solid --}}
        <div class="rcp-actions">
            <a href="{{ $showRoute }}" class="btn rcp-btn-ghost px-4">Cancel</a>
            <button type="submit" class="btn rcp-btn-primary px-4">
                <i class="bi bi-save me-1"></i> Save changes
            </button>
        </div>
    </form>
</div>


<style>
    /* ===== Theme tokens (light by default, dark when the app is in dark mode) ===== */
    .rcp {
        --c-text: #1d2433;
        --c-muted: #667085;
        --c-card: #ffffff;
        --c-line: #e2e8f0;
        --c-field: #ffffff;
        --c-soft: #f3f6fb;
        --c-accent: #2563eb;
        --c-accent-ink: #ffffff;
        --c-accent-soft: rgba(37, 99, 235, .10);
        --c-danger: #d92d20;
        --c-danger-soft: #fef3f2;
        --c-info: #175cd3;
        --c-info-soft: #eff4ff;
        color: var(--c-text);
    }

    html[data-theme="dark"] .rcp,
    html[data-bs-theme="dark"] .rcp,
    body[data-theme="dark"] .rcp,
    body.dark-mode .rcp {
        --c-text: #e9eef7;
        --c-muted: #a3afc2;
        --c-card: #171f2e;
        --c-line: #2c384d;
        --c-field: #0f1623;
        --c-soft: #1e293b;
        --c-accent: #7aa8ff;
        --c-accent-ink: #0b1220;
        --c-accent-soft: rgba(122, 168, 255, .15);
        --c-danger: #ff8a80;
        --c-danger-soft: #3a1a1c;
        --c-info: #9ec1ff;
        --c-info-soft: #172640;
        color-scheme: dark;
    }

    .rcp [hidden] { display: none !important; }
    .rcp h3, .rcp h5, .rcp .fw-bold, .rcp .fw-semibold { color: var(--c-text); }

    .rcp-sub, .rcp-hint { color: var(--c-muted); font-size: .85rem; }
    .rcp-hint { margin-top: .35rem; }
    .rcp-req { color: var(--c-danger); }
    .rcp-error { color: var(--c-danger); font-size: .85rem; margin-top: .4rem; }
    .rcp-code { color: var(--c-text); }
    .rcp-pill {
        display: inline-block; padding: .1rem .6rem; border-radius: 999px;
        background: var(--c-accent-soft); color: var(--c-accent); font-weight: 600; font-size: .8rem;
    }

    /* Cards */
    .rcp-card {
        background: var(--c-card);
        border: 1px solid var(--c-line);
        border-radius: 14px;
        padding: 1.25rem 1.4rem 1.4rem;
        margin-bottom: 1rem;
        color: var(--c-text);
    }
    .rcp-card-h { display: flex; gap: .8rem; align-items: center; margin-bottom: 1.1rem; }
    .rcp-title { margin: 0; font-size: 1.05rem; font-weight: 700; }
    .rcp-step {
        width: 1.9rem; height: 1.9rem; flex: none; border-radius: 50%;
        display: grid; place-items: center; font-weight: 700; font-size: .85rem;
        background: var(--c-accent-soft); color: var(--c-accent);
    }

    .rcp-lbl { display: block; margin-bottom: .35rem; font-weight: 600; font-size: .9rem; color: var(--c-text); }

    .rcp-box { border: 1px solid var(--c-line); border-radius: 12px; padding: 1rem; background: var(--c-soft); }

    /* Fields (softened further by the shared _ui styles) */
    .rcp .form-control, .rcp .form-select {
        min-height: 44px;
        background-color: var(--c-field);
        color: var(--c-text);
        border: 1px solid var(--c-line);
        border-radius: 10px;
    }
    .rcp textarea.form-control { min-height: auto; }
    .rcp .form-control::placeholder { color: var(--c-muted); opacity: .85; }
    .rcp .form-control:focus, .rcp .form-select:focus {
        background-color: var(--c-field);
        color: var(--c-text);
        border-color: var(--c-accent);
        box-shadow: 0 0 0 .2rem var(--c-accent-soft);
    }
    .rcp .form-control[readonly], .rcp .form-control:disabled, .rcp .form-select:disabled {
        background-color: var(--c-soft);
        color: var(--c-muted);
        opacity: 1;
    }
    .rcp .form-control.is-invalid, .rcp .form-select.is-invalid { border-color: var(--c-danger); background-image: none; }
    .rcp .form-select option { background: var(--c-field); color: var(--c-text); }
    .rcp .form-check-input { background-color: var(--c-field); border-color: var(--c-line); }
    .rcp .form-check-input:checked { background-color: var(--c-accent); border-color: var(--c-accent); }

    /* Report type cards */
    .rcp-choice {
        display: flex; gap: .85rem; align-items: flex-start; height: 100%;
        padding: 1rem; cursor: pointer;
        border: 1.5px solid var(--c-line); border-radius: 12px;
        background: var(--c-card); color: var(--c-text);
        transition: border-color .15s, background .15s;
    }
    .rcp-choice b { display: block; }
    .rcp-choice small { display: block; margin-top: .15rem; color: var(--c-muted); }
    .rcp-choice-ic {
        width: 2.5rem; height: 2.5rem; flex: none; border-radius: 10px;
        display: grid; place-items: center; font-size: 1.2rem;
        background: var(--c-accent-soft); color: var(--c-accent);
    }
    .rcp-choice:hover { border-color: var(--c-accent); }
    .btn-check:checked + .rcp-choice { border-color: var(--c-accent); background: var(--c-accent-soft); }
    .btn-check:focus-visible + .rcp-choice,
    .btn-check:focus-visible + label { outline: 2px solid var(--c-accent); outline-offset: 2px; }

    /* Segmented source switch */
    .rcp-seg { display: inline-flex; padding: 4px; gap: 4px; border-radius: 12px; background: var(--c-soft); border: 1px solid var(--c-line); max-width: 100%; flex-wrap: wrap; }
    .rcp-seg label {
        margin: 0; padding: .5rem .9rem; border-radius: 9px; cursor: pointer;
        font-weight: 600; font-size: .9rem; color: var(--c-muted);
    }
    .rcp-seg .btn-check:checked + label { background: var(--c-card); color: var(--c-accent); box-shadow: 0 1px 3px rgba(0,0,0,.18); }

    /* Notes */
    .rcp-note { padding: .75rem 1rem; border-radius: 10px; font-size: .88rem; border: 1px solid transparent; }
    .rcp-note-info { background: var(--c-info-soft); color: var(--c-info); border-color: var(--c-line); }
    .rcp-note-danger { background: var(--c-danger-soft); color: var(--c-danger); border-color: var(--c-danger); }

    /* Buttons (final look comes from the shared _ui styles) */
    .rcp-btn-primary { background: var(--c-accent); color: var(--c-accent-ink); border: 0; border-radius: 10px; font-weight: 600; }
    .rcp-btn-primary:hover { filter: brightness(1.08); color: var(--c-accent-ink); }
    .rcp-btn-ghost { background: transparent; color: var(--c-text); border: 1px solid var(--c-line); border-radius: 10px; font-weight: 600; }
    .rcp-btn-ghost:hover { background: var(--c-soft); color: var(--c-text); }

    /* Sticky action bar */
    .rcp-actions {
        position: sticky; bottom: 0; z-index: 5;
        display: flex; justify-content: flex-end; gap: .6rem; flex-wrap: wrap;
        padding: .85rem 1rem; margin-bottom: 2rem;
        background: var(--c-card); border: 1px solid var(--c-line); border-radius: 14px;
        box-shadow: 0 -6px 18px rgba(0,0,0,.08);
    }

    /* Searchable resident picker */
    .rcp-combo { position: relative; }
    .rcp-combo-input { padding-right: 2.4rem; }
    .rcp-combo-clear {
        position: absolute; top: 50%; right: .5rem; transform: translateY(-50%);
        width: 1.9rem; height: 1.9rem; display: none; align-items: center; justify-content: center;
        border: 0; border-radius: 50%; background: transparent; color: var(--c-muted); cursor: pointer;
    }
    .rcp-combo.has-value .rcp-combo-clear { display: flex; }
    .rcp-combo-clear:hover { background: var(--c-accent-soft); color: var(--c-text); }
    .rcp-combo-list {
        position: absolute; z-index: 1055; top: calc(100% + 4px); left: 0; right: 0;
        max-height: 280px; overflow-y: auto; margin: 0; padding: .25rem; list-style: none;
        background: var(--c-card); color: var(--c-text);
        border: 1px solid var(--c-line); border-radius: 12px; box-shadow: 0 10px 28px rgba(0,0,0,.25);
    }
    .rcp-combo-item { padding: .5rem .75rem; border-radius: 8px; cursor: pointer; font-size: .92rem; }
    .rcp-combo-item small { display: block; color: var(--c-muted); }
    .rcp-combo-item.is-active, .rcp-combo-item:hover { background: var(--c-accent-soft); }
    .rcp-combo-empty { padding: .7rem .75rem; font-size: .85rem; color: var(--c-muted); }

    /* =====================================================================
       UI/UX COLOR SYSTEM  (one color = one meaning, same in all 4 pages)
       INDIGO = main action | GREEN = resolve | SLATE = close
       GRAY = secondary/safe | RED = destructive | TEAL/ROSE = record type
       ===================================================================== */
    .rcp {
        --ui-primary: #4338ca; --ui-primary-hover: #3730a3; --ui-primary-soft: #eeecfd; --ui-on-primary: #ffffff;
        --ui-success: #15803d; --ui-success-hover: #166534; --ui-success-soft: #e0f6ea;
        --ui-close: #475569;   --ui-close-soft: #eef0f4;
        --ui-danger: #dc2626;  --ui-danger-soft: #fdecec;
        --ui-neutral-text: #475569; --ui-neutral-line: #cbd2de; --ui-neutral-hover: #f1f4f9;
        --ui-field-bg: #ffffff; --ui-field-text: #111827; --ui-field-line: #d5dae5;
        --ui-placeholder: #a4abbb; --ui-field-locked: #f1f3f8;
    }
    html[data-theme="dark"] .rcp, html[data-bs-theme="dark"] .rcp, body[data-theme="dark"] .rcp, body.dark-mode .rcp {
        --ui-primary: #8b81f0; --ui-primary-hover: #a29af5; --ui-primary-soft: rgba(139,129,240,.16); --ui-on-primary: #12122a;
        --ui-success: #4ade80; --ui-success-hover: #6ee7a0; --ui-success-soft: rgba(74,222,128,.14);
        --ui-close: #94a3b8;   --ui-close-soft: rgba(148,163,184,.14);
        --ui-danger: #f87171;  --ui-danger-soft: rgba(248,113,113,.14);
        --ui-neutral-text: #c3cbda; --ui-neutral-line: #4a5570; --ui-neutral-hover: rgba(255,255,255,.06);
        --ui-field-bg: #141a25; --ui-field-text: #f3f6fc; --ui-field-line: #36405a;
        --ui-placeholder: #667089; --ui-field-locked: #1b2332;
    }

    /* ---- Buttons: shared shape ---- */
    .rcp .btn, .rcp .cta, .rcp .view-btn, .rcp .resolve-btn, .rcp .close-btn, .rcp .reset, .rcp .act, .rcp .rcp-btn-primary, .rcp .rcp-btn-ghost {
        font-weight: 700;
        transition: background-color .15s ease, border-color .15s ease, color .15s ease, box-shadow .15s ease, transform .15s ease;
    }

    /* INDIGO: main / constructive action */
    .rcp .btn-primary,
    .rcp .rcp-btn-primary,
    .rcp .cta,
    .rcp .act-brand {
        background: var(--ui-primary); border: 1.5px solid var(--ui-primary); color: var(--ui-on-primary); filter: none;
    }
    .rcp .btn-primary:hover,
    .rcp .rcp-btn-primary:hover,
    .rcp .cta:hover,
    .rcp .act-brand:hover {
        background: var(--ui-primary-hover); border-color: var(--ui-primary-hover); color: var(--ui-on-primary); filter: none;
    }

    /* GRAY GHOST: secondary / safe (Cancel, Back, View, Print, Reset) */
    .rcp .btn-outline-secondary,
    .rcp .rcp-btn-ghost,
    .rcp .view-btn,
    .rcp .act-line,
    .rcp .reset {
        background: transparent; border: 1.5px solid var(--ui-neutral-line); color: var(--ui-neutral-text);
    }
    .rcp .btn-outline-secondary:hover,
    .rcp .rcp-btn-ghost:hover,
    .rcp .view-btn:hover,
    .rcp .act-line:hover,
    .rcp .reset:hover {
        background: var(--ui-neutral-hover); border-color: var(--ui-neutral-text); color: var(--ui-neutral-text);
    }

    /* GREEN: Resolve (positive outcome) */
    .rcp .resolve-btn { background: var(--ui-success-soft); border: 1.5px solid var(--ui-success); color: var(--ui-success); }
    .rcp .resolve-btn:hover { background: var(--ui-success); border-color: var(--ui-success); color: #fff; }
    .rcp .act-green { background: var(--ui-success); border: 1.5px solid var(--ui-success); color: #fff; filter: none; }
    .rcp .act-green:hover { background: var(--ui-success-hover); border-color: var(--ui-success-hover); color: #fff; filter: none; }
    html[data-theme="dark"] .rcp .act-green, html[data-bs-theme="dark"] .rcp .act-green, body[data-theme="dark"] .rcp .act-green, body.dark-mode .rcp .act-green { color: #06210f; }

    /* SLATE: Close case (finality, different from Resolve green) */
    .rcp .close-btn,
    .rcp .act-close { background: var(--ui-close-soft); border: 1.5px solid var(--ui-close); color: var(--ui-close); }
    .rcp .close-btn:hover,
    .rcp .act-close:hover { background: var(--ui-close); border-color: var(--ui-close); color: #fff; }
    html[data-theme="dark"] .rcp .act-close:hover, html[data-bs-theme="dark"] .rcp .act-close:hover, body[data-theme="dark"] .rcp .act-close:hover, body.dark-mode .rcp .act-close:hover { color: #0f1620; }
    html[data-theme="dark"] .rcp .close-btn:hover, html[data-bs-theme="dark"] .rcp .close-btn:hover, body[data-theme="dark"] .rcp .close-btn:hover, body.dark-mode .rcp .close-btn:hover { color: #0f1620; }

    /* RED: destructive / disapprove */
    .rcp .btn-danger,
    .rcp .btn-outline-danger,
    .rcp .act-danger { background: var(--ui-danger-soft); border: 1.5px solid var(--ui-danger); color: var(--ui-danger); }
    .rcp .btn-danger:hover,
    .rcp .btn-outline-danger:hover,
    .rcp .act-danger:hover { background: var(--ui-danger); border-color: var(--ui-danger); color: #fff; }

    /* Disabled + focus */
    .rcp .btn:disabled,
    .rcp .cta:disabled,
    .rcp .act:disabled { opacity: .5; cursor: not-allowed; }
    .rcp .btn:focus-visible,
    .rcp .cta:focus-visible,
    .rcp .act:focus-visible,
    .rcp .view-btn:focus-visible,
    .rcp .resolve-btn:focus-visible,
    .rcp .close-btn:focus-visible,
    .rcp .reset:focus-visible {
        outline: 3px solid var(--ui-primary); outline-offset: 2px; box-shadow: none;
    }

    /* ---- Text fields: hint text must NOT look like typed text ---- */
    .rcp .form-control,
    .rcp .form-select {
        background-color: var(--ui-field-bg); color: var(--ui-field-text); border-color: var(--ui-field-line); font-weight: 500;
    }
    .rcp .form-control::placeholder,
    .rcp .form-control.rc-combo-input::placeholder,
    .rcp textarea.form-control::placeholder {
        color: var(--ui-placeholder) !important; font-weight: 400; font-style: normal; opacity: 1 !important;
    }
    .rcp .form-control:focus,
    .rcp .form-select:focus {
        border-color: var(--ui-primary); background-color: var(--ui-field-bg); color: var(--ui-field-text);
        box-shadow: 0 0 0 .2rem var(--ui-primary-soft);
    }
    .rcp .form-control:focus,
    .rcp .form-select:focus::placeholder { opacity: .5 !important; }

    /* Dropdown still on "-- Select --" looks like a hint, not a value */
    .rcp .form-select:has(option[value=""]:checked) { color: var(--ui-placeholder); font-weight: 400; }
    .rcp .form-select option { color: var(--ui-field-text); font-weight: 500; background-color: var(--ui-field-bg); }
    .rcp .form-select option[value=""] { color: var(--ui-placeholder); }

    /* Empty date/time: dim the mm/dd/yyyy mask (class toggled by the tiny script below) */
    .rcp input.ui-empty[type="date"]:not(:focus)::-webkit-datetime-edit,
    .rcp input.ui-empty[type="time"]:not(:focus)::-webkit-datetime-edit { color: var(--ui-placeholder); }

    /* Fixed / read-only values are information, not inputs */
    .rcp .form-control[readonly] {
        background-color: var(--ui-field-locked); border-style: dashed; color: var(--ui-field-text); font-weight: 600; cursor: default;
    }
    .rcp .form-control.is-invalid, .rcp .form-select.is-invalid { border-color: var(--ui-danger); }

</style>

<script>
/* Dim empty date/time inputs so the mm/dd/yyyy mask is not mistaken for a value. */
(function () {
    var q = '.rcp input[type="date"], .rcp input[type="time"]';
    function mark(el) { el.classList.toggle('ui-empty', !el.value); }
    document.querySelectorAll(q).forEach(function (el) {
        mark(el);
        el.addEventListener('input', function () { mark(el); });
        el.addEventListener('change', function () { mark(el); });
    });
})();
</script>



<script>
document.addEventListener('DOMContentLoaded', function () {

    const $ = (id) => document.getElementById(id);

    const suggestions    = @json(['community' => $communitySuggest, 'person' => $personSuggest]);
    const originalStatus = @json($r->status);

    const personRadio = $('typePerson');
    const form        = $('editForm');

    const isPerson = () => !!(personRadio && personRadio.checked);

    /* Show/hide a section and disable its fields so hidden data is never submitted. */
    function setSection(el, on) {
        if (!el) { return; }
        el.hidden = !on;
        el.querySelectorAll('input, select, textarea').forEach(function (f) { f.disabled = !on; });
    }

    function sourceIs(id) { return $(id) && $(id).checked; }

    /* ---------- Category: only the suggestion list changes, never the value ---------- */
    function updateCategorySuggestions() {
        const dl = $('categoryOptions');
        dl.innerHTML = '';
        (suggestions[isPerson() ? 'person' : 'community'] || []).forEach(function (s) {
            const o = document.createElement('option');
            o.value = s;
            dl.appendChild(o);
        });
    }

    /* ---------- Participants ---------- */
    function updateComplainant() {
        const resident = sourceIs('complainantResidentSource');
        setSection($('complainantResidentSection'), resident);
        setSection($('complainantManualSection'), !resident);
    }

    function updateSubject() {
        const person = isPerson();
        const resident = sourceIs('subjectResidentSource');
        setSection($('subjectResidentSection'), person && resident);
        setSection($('subjectManualSection'), person && !resident);
    }

    function updateWitnesses() {
        const sum = $('summons_requested');
        setSection($('witnessesSection'), isPerson() && sum && sum.checked);
    }

    function updateType() {
        const person = isPerson();

        setSection($('respondentCard'), person);
        setSection($('hearingScheduleCard'), person);

        $('incidentStep').textContent = person ? '5' : '3';
        $('statusStep').textContent   = person ? '6' : '4';

        updateSubject();
        updateWitnesses();
        updateCategorySuggestions();
    }

    function updateStatusNote() {
        const s = $('status').value;
        const note = $('statusNote');
        note.innerHTML = '';

        const b = document.createElement('b');

        if (s === originalStatus) {
            note.append('The status stays ');
            b.textContent = originalStatus;
            note.append(b, ' unless you choose another one.');
        } else {
            note.append('The status will change from ');
            b.textContent = originalStatus;
            const b2 = document.createElement('b');
            b2.textContent = s;
            note.append(b, ' to ', b2, ' when you save.');
        }
    }

    document.querySelectorAll('input[name="report_type"]').forEach(function (r) {
        r.addEventListener('change', updateType);
    });
    document.querySelectorAll('input[name="complainant_source"]').forEach(function (r) {
        r.addEventListener('change', updateComplainant);
    });
    document.querySelectorAll('input[name="subject_source"]').forEach(function (r) {
        r.addEventListener('change', updateSubject);
    });
    $('summons_requested').addEventListener('change', updateWitnesses);
    $('status').addEventListener('change', updateStatusNote);


    /* ---------- Searchable resident picker (the <select> stays the submitted field) ---------- */
    const norm = (t) => (t || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();

    function enhanceResident(select, placeholder) {
        if (!select) { return; }

        const items = Array.from(select.options).filter(function (o) { return o.value !== ''; }).map(function (o) {
            const label = o.textContent.replace(/\s+/g, ' ').trim();
            const parts = label.split(' — ');
            return { value: o.value, label: label, name: parts[0], address: parts.slice(1).join(' — '), search: norm(label) };
        });

        const wrap = document.createElement('div');
        wrap.className = 'rcp-combo';

        const input = document.createElement('input');
        input.type = 'text';
        input.id = select.id + '_search';
        input.className = 'form-control rcp-combo-input';
        input.placeholder = placeholder;
        input.autocomplete = 'off';

        const clear = document.createElement('button');
        clear.type = 'button';
        clear.className = 'rcp-combo-clear';
        clear.setAttribute('aria-label', 'Clear selection');
        clear.innerHTML = '<i class="bi bi-x-lg"></i>';

        const list = document.createElement('ul');
        list.className = 'rcp-combo-list';
        list.hidden = true;

        select.parentNode.insertBefore(wrap, select);
        wrap.append(input, clear, list, select);
        select.hidden = true;
        select.tabIndex = -1;

        const label = document.querySelector('label[for="' + select.id + '"]');
        if (label) { label.setAttribute('for', input.id); }

        let visible = [], active = -1;

        function current() { return items.find(function (i) { return i.value === select.value; }) || null; }
        function sync() {
            const c = current();
            input.value = c ? c.label : '';
            wrap.classList.toggle('has-value', !!c);
            input.disabled = select.disabled;
        }
        function close() { list.hidden = true; active = -1; }
        function highlight(i) {
            const nodes = list.querySelectorAll('.rcp-combo-item');
            if (!nodes.length) { active = -1; return; }
            active = (i + nodes.length) % nodes.length;
            nodes.forEach(function (n, k) { n.classList.toggle('is-active', k === active); });
            nodes[active].scrollIntoView({ block: 'nearest' });
        }
        function render(q) {
            const tokens = norm(q).split(/\s+/).filter(Boolean);
            const matches = items.filter(function (i) { return tokens.every(function (t) { return i.search.indexOf(t) !== -1; }); });
            visible = matches.slice(0, 100);
            list.innerHTML = '';
            if (!visible.length) {
                const e = document.createElement('li');
                e.className = 'rcp-combo-empty';
                e.textContent = 'No matching resident. Use “Non-resident / not found” if the person is not registered.';
                list.appendChild(e);
            }
            visible.forEach(function (item, idx) {
                const li = document.createElement('li');
                li.className = 'rcp-combo-item';
                li.dataset.index = idx;
                li.appendChild(document.createTextNode(item.name));
                if (item.address) {
                    const s = document.createElement('small');
                    s.textContent = item.address;
                    li.appendChild(s);
                }
                list.appendChild(li);
            });
            list.hidden = false;
            highlight(visible.length ? 0 : -1);
        }
        function choose(item) {
            select.value = item.value;
            sync();
            close();
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        input.addEventListener('focus', function () { input.select(); render(''); });
        input.addEventListener('input', function () { render(input.value); });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') { e.preventDefault(); list.hidden ? render(input.value) : highlight(active + 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); highlight(active - 1); }
            else if (e.key === 'Enter') { e.preventDefault(); if (!list.hidden && visible[active]) { choose(visible[active]); } }
            else if (e.key === 'Escape' || e.key === 'Tab') { close(); sync(); }
        });
        input.addEventListener('blur', function () { close(); sync(); });
        list.addEventListener('mousedown', function (e) {
            e.preventDefault();
            const row = e.target.closest('.rcp-combo-item');
            if (row && visible[row.dataset.index]) { choose(visible[row.dataset.index]); }
        });
        clear.addEventListener('mousedown', function (e) {
            e.preventDefault();
            select.value = '';
            sync();
            select.dispatchEvent(new Event('change', { bubbles: true }));
            input.focus();
        });

        /* Keep the visible box in sync when a section is enabled/disabled. */
        new MutationObserver(sync).observe(select, { attributes: true, attributeFilter: ['disabled'] });

        sync();
    }

    enhanceResident($('complainant_id'), 'Type a name or address to search residents…');
    enhanceResident($('subject_resident_id'), 'Type a name or address to search residents…');


    /* ---------- Submit checks ---------- */
    function showError(id, msg) {
        const el = $(id);
        el.textContent = msg;
        el.hidden = false;
    }

    form.addEventListener('submit', function (e) {

        $('errComplainant').hidden = true;
        $('errSubject').hidden = true;

        const cat = $('category');
        cat.value = cat.value.trim();

        if (!cat.value) {
            e.preventDefault();
            cat.focus();
            cat.reportValidity();
            return;
        }

        if (sourceIs('complainantResidentSource') && !$('complainant_id').value) {
            e.preventDefault();
            showError('errComplainant', 'Please select the complainant from the resident records.');
            $('complainant_id_search').focus();
            return;
        }

        if (sourceIs('complainantManualSource') && !$('complainant_name').value.trim()) {
            e.preventDefault();
            showError('errComplainant', 'Please enter the complainant\'s full name.');
            $('complainant_name').focus();
            return;
        }

        if (isPerson()) {

            if (sourceIs('subjectResidentSource') && !$('subject_resident_id').value) {
                e.preventDefault();
                showError('errSubject', 'Please select the respondent from the resident records.');
                $('subject_resident_id_search').focus();
                return;
            }

            if (sourceIs('subjectManualSource') && !$('subject_name').value.trim()) {
                e.preventDefault();
                showError('errSubject', 'Please enter the respondent\'s full name.');
                $('subject_name').focus();
                return;
            }

            if (
                sourceIs('complainantResidentSource') && sourceIs('subjectResidentSource')
                && $('complainant_id').value && $('complainant_id').value === $('subject_resident_id').value
            ) {
                e.preventDefault();
                showError('errSubject', 'The complainant and respondent cannot be the same resident.');
                return;
            }
        }

        if (!form.checkValidity()) {
            e.preventDefault();
            form.reportValidity();
        }
    });


    /* ---------- Initial state ---------- */
    updateComplainant();
    updateType();
    updateStatusNote();
});
</script>

@endsection
