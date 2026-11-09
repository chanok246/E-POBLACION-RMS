@extends('field-personnel.layouts.app')

@section('title', 'Household Collection')

@section('page-heading')
    <h1 class="fc-page-title">Household Collection</h1>
    <p class="fc-page-description">
        Record household information for verification by the Barangay.
    </p>
@endsection

@section('content')
<div class="fc-household-page">

    <div class="fc-page-actions">
        <a href="{{ route('field-personnel.home') }}" class="fc-button fc-button-secondary">
            <span aria-hidden="true">←</span> Back to Dashboard
        </a>
    </div>

    <div class="fc-alert fc-alert-info">
        <span class="fc-alert-icon" aria-hidden="true">ⓘ</span>
        <div>
            <strong>Important reminder</strong>
            <p class="fc-info-text">
                Submitted information will be saved as
                <strong>Pending for Verification</strong>.
                It will not become an official household or resident record
                until approved by the authorized Captain or Secretary.
            </p>
        </div>
    </div>

    <form id="householdCollectionForm"
          method="POST"
          action="{{ url('/field-personnel/households') }}">
        @csrf

        {{-- HOUSEHOLD INFORMATION --}}
        <section class="fc-card fc-form-card">
            <div class="fc-section-heading">
                <span class="fc-section-icon" aria-hidden="true">⌂</span>
                <div>
                    <h2 class="fc-card-title">Household Information</h2>
                    <p class="fc-card-description">Enter the household's location and address.</p>
                </div>
            </div>

            <div class="fc-form-grid">
                <div class="fc-form-group fc-span-6">
                    <label for="purok" class="fc-label">
                        Purok <span class="fc-required">*</span>
                    </label>
                    <input type="text"
                           id="purok"
                           name="household[purok]"
                           class="fc-input @error('household.purok') fc-input-invalid @enderror"
                           value="{{ old('household.purok') }}"
                           maxlength="100"
                           required>
                    @error('household.purok')
                        <span class="fc-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="fc-form-group fc-span-12">
                    <label for="address" class="fc-label">
                        Complete Household Address <span class="fc-required">*</span>
                    </label>
                    <textarea id="address"
                              name="household[address]"
                              class="fc-textarea @error('household.address') fc-input-invalid @enderror"
                              rows="3"
                              maxlength="255"
                              required>{{ old('household.address') }}</textarea>
                    @error('household.address')
                        <span class="fc-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </section>

        {{-- GPS LOCATION --}}
        <section class="fc-card fc-form-card">
            <div class="fc-section-heading">
                <span class="fc-section-icon" aria-hidden="true">⌖</span>
                <div>
                    <h2 class="fc-card-title">Household GPS Location</h2>
                    <p class="fc-card-description">
                        Capture the household's location using this device.
                    </p>
                </div>
            </div>

            <p class="fc-help-text">
                Allow location permission when your browser asks. For browser-based
                GPS access, use HTTPS or localhost.
            </p>

            <input type="hidden" name="household[latitude]" id="gpsLatitude"
                   value="{{ old('household.latitude') }}">
            <input type="hidden" name="household[longitude]" id="gpsLongitude"
                   value="{{ old('household.longitude') }}">
            <input type="hidden" name="household[gps_accuracy_meters]" id="gpsAccuracy"
                   value="{{ old('household.gps_accuracy_meters') }}">
            <input type="hidden" name="household[gps_captured_at]" id="gpsCapturedAt"
                   value="{{ old('household.gps_captured_at') }}">

            <div class="fc-action-row">
                <button type="button" id="captureGpsButton" class="fc-button fc-button-primary">
                    <span aria-hidden="true">⌖</span> Capture Household GPS
                </button>
                <button type="button" id="clearGpsButton" class="fc-button fc-button-secondary">
                    Clear GPS
                </button>
            </div>

            <div id="gpsStatus" class="fc-alert fc-alert-neutral fc-gps-status"
                 role="status" aria-live="polite">
                GPS location has not been captured.
            </div>

            <div class="fc-gps-details">
                <div>
                    <span class="fc-detail-label">Latitude</span>
                    <strong id="latitudeDisplay">Not captured</strong>
                </div>
                <div>
                    <span class="fc-detail-label">Longitude</span>
                    <strong id="longitudeDisplay">Not captured</strong>
                </div>
                <div>
                    <span class="fc-detail-label">GPS accuracy</span>
                    <strong id="accuracyDisplay">Not available</strong>
                </div>
                <div>
                    <span class="fc-detail-label">Captured at</span>
                    <strong id="capturedAtDisplay">Not captured</strong>
                </div>
            </div>

            <p class="fc-help-text fc-help-bottom">
                GPS accuracy depends on the device and available location signals.
            </p>
        </section>

        {{-- HOUSEHOLD HEAD --}}
        <section class="fc-card fc-form-card">
            <div class="fc-section-heading">
                <span class="fc-section-icon" aria-hidden="true">♙</span>
                <div>
                    <h2 class="fc-card-title">Household Head</h2>
                    <p class="fc-card-description">Enter the details of the household head.</p>
                </div>
            </div>

            <div class="fc-form-grid">
                <div class="fc-form-group fc-span-4">
                    <label class="fc-label">First Name <span class="fc-required">*</span></label>
                    <input type="text" name="household_head[first_name]"
                           class="fc-input" value="{{ old('household_head.first_name') }}"
                           maxlength="100" required>
                </div>

                <div class="fc-form-group fc-span-4">
                    <label class="fc-label">Middle Name</label>
                    <input type="text" name="household_head[middle_name]"
                           class="fc-input" value="{{ old('household_head.middle_name') }}"
                           maxlength="100">
                </div>

                <div class="fc-form-group fc-span-4">
                    <label class="fc-label">Last Name <span class="fc-required">*</span></label>
                    <input type="text" name="household_head[last_name]"
                           class="fc-input" value="{{ old('household_head.last_name') }}"
                           maxlength="100" required>
                </div>

                <div class="fc-form-group fc-span-4">
                    <label class="fc-label">Suffix</label>
                    <input type="text" name="household_head[suffix]"
                           class="fc-input" value="{{ old('household_head.suffix') }}"
                           maxlength="30" placeholder="Jr., Sr., III">
                </div>

                <div class="fc-form-group fc-span-4">
                    <label class="fc-label">Birth Date <span class="fc-required">*</span></label>
                    <input type="date" name="household_head[birth_date]"
                           class="fc-input" value="{{ old('household_head.birth_date') }}"
                           max="{{ now()->toDateString() }}" required>
                </div>

                <div class="fc-form-group fc-span-4">
                    <label class="fc-label">Sex <span class="fc-required">*</span></label>
                    <select name="household_head[sex]" class="fc-select" required>
                        <option value="">Select sex</option>
                        <option value="Male" @selected(old('household_head.sex') === 'Male')>Male</option>
                        <option value="Female" @selected(old('household_head.sex') === 'Female')>Female</option>
                    </select>
                </div>

                <div class="fc-form-group fc-span-4">
                    <label class="fc-label">Civil Status <span class="fc-required">*</span></label>
                    <select name="household_head[civil_status]" class="fc-select" required>
                        <option value="">Select civil status</option>
                        @foreach(['Single', 'Married', 'Widowed', 'Separated', 'Annulled'] as $status)
                            <option value="{{ $status }}"
                                @selected(old('household_head.civil_status') === $status)>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="fc-form-group fc-span-4">
                    <label class="fc-label">Contact Number</label>
                    <div class="fc-phone-group">
                        <span>+63</span>
                        <input type="tel" name="household_head[contact_number]"
                               class="fc-input mobile-number"
                               value="{{ old('household_head.contact_number') }}"
                               inputmode="numeric" maxlength="10"
                               pattern="9[0-9]{9}" placeholder="9XXXXXXXXX">
                    </div>
                    <small class="fc-hint">Optional. Enter 10 digits starting with 9.</small>
                </div>

                <div class="fc-form-group fc-span-4">
                    <label class="fc-label">Email Address</label>
                    <input type="email" name="household_head[email]"
                           class="fc-input" value="{{ old('household_head.email') }}"
                           maxlength="255">
                </div>

                <div class="fc-form-group fc-span-4">
                    <label class="fc-label">Residency Type <span class="fc-required">*</span></label>
                    <select name="household_head[residency_type]"
                            class="fc-select residency-type" required>
                        <option value="">Select residency</option>
                        <option value="Permanent"
                            @selected(old('household_head.residency_type') === 'Permanent')>Permanent</option>
                        <option value="Temporary"
                            @selected(old('household_head.residency_type') === 'Temporary')>Temporary</option>
                    </select>
                </div>

                <div class="fc-form-group fc-span-4 residency-year-wrapper">
                    <label class="fc-label">
                        Residency Start Year
                        <span class="fc-required residency-year-required" hidden>*</span>
                    </label>
                    <input type="number" name="household_head[residency_start_year]"
                           class="fc-input residency-year"
                           value="{{ old('household_head.residency_start_year') }}"
                           min="1900" max="{{ now()->year }}" step="1">
                    <small class="fc-hint">Required for Temporary residents.</small>
                </div>

                <div class="fc-form-group fc-span-8">
                    <label class="fc-label">Occupation</label>
                    <input type="text" name="household_head[occupation]"
                           class="fc-input" value="{{ old('household_head.occupation') }}"
                           maxlength="150">
                </div>
            </div>
        </section>

        {{-- HOUSEHOLD MEMBERS --}}
        <section class="fc-card fc-form-card">
            <div class="fc-section-heading fc-section-heading-between">
                <div class="fc-section-heading">
                    <span class="fc-section-icon" aria-hidden="true">♧</span>
                    <div>
                        <h2 class="fc-card-title">Other Household Members</h2>
                        <p class="fc-card-description">Add the other individuals living in this household.</p>
                    </div>
                </div>

                <button type="button" id="addMemberButton" class="fc-button fc-button-primary">
                    <span aria-hidden="true">＋</span> Add Member
                </button>
            </div>

            <p class="fc-help-text">
                The household head is already recorded above. Add only the other household members.
            </p>

            <div id="membersContainer">
                @php
                    $oldMembers = old('members', []);
                @endphp

                @foreach($oldMembers as $index => $member)
                    <div class="fc-member-card" data-member-index="{{ $index }}">
                        <div class="fc-member-heading">
                            <h3 class="fc-member-title">Household Member {{ $loop->iteration }}</h3>
                            <button type="button" class="fc-button fc-button-danger fc-button-small remove-member">
                                Remove
                            </button>
                        </div>

                        <div class="fc-form-grid">
                            <div class="fc-form-group fc-span-4">
                                <label class="fc-label">First Name <span class="fc-required">*</span></label>
                                <input type="text" name="members[{{ $index }}][first_name]"
                                       class="fc-input" value="{{ $member['first_name'] ?? '' }}"
                                       maxlength="100" required>
                            </div>
                            <div class="fc-form-group fc-span-4">
                                <label class="fc-label">Middle Name</label>
                                <input type="text" name="members[{{ $index }}][middle_name]"
                                       class="fc-input" value="{{ $member['middle_name'] ?? '' }}"
                                       maxlength="100">
                            </div>
                            <div class="fc-form-group fc-span-4">
                                <label class="fc-label">Last Name <span class="fc-required">*</span></label>
                                <input type="text" name="members[{{ $index }}][last_name]"
                                       class="fc-input" value="{{ $member['last_name'] ?? '' }}"
                                       maxlength="100" required>
                            </div>
                            <div class="fc-form-group fc-span-4">
                                <label class="fc-label">Suffix</label>
                                <input type="text" name="members[{{ $index }}][suffix]"
                                       class="fc-input" value="{{ $member['suffix'] ?? '' }}"
                                       maxlength="30">
                            </div>
                            <div class="fc-form-group fc-span-4">
                                <label class="fc-label">Birth Date <span class="fc-required">*</span></label>
                                <input type="date" name="members[{{ $index }}][birth_date]"
                                       class="fc-input" value="{{ $member['birth_date'] ?? '' }}"
                                       max="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="fc-form-group fc-span-4">
                                <label class="fc-label">Sex <span class="fc-required">*</span></label>
                                <select name="members[{{ $index }}][sex]" class="fc-select" required>
                                    <option value="">Select sex</option>
                                    <option value="Male" @selected(($member['sex'] ?? '') === 'Male')>Male</option>
                                    <option value="Female" @selected(($member['sex'] ?? '') === 'Female')>Female</option>
                                </select>
                            </div>
                            <div class="fc-form-group fc-span-4">
                                <label class="fc-label">Civil Status <span class="fc-required">*</span></label>
                                <select name="members[{{ $index }}][civil_status]" class="fc-select" required>
                                    <option value="">Select civil status</option>
                                    @foreach(['Single', 'Married', 'Widowed', 'Separated', 'Annulled'] as $status)
                                        <option value="{{ $status }}"
                                            @selected(($member['civil_status'] ?? '') === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="fc-form-group fc-span-4">
                                <label class="fc-label">Relationship to Head <span class="fc-required">*</span></label>
                                <input type="text" name="members[{{ $index }}][relationship]"
                                       class="fc-input" value="{{ $member['relationship'] ?? '' }}"
                                       maxlength="100" placeholder="e.g. Spouse, Son, Daughter" required>
                            </div>
                            <div class="fc-form-group fc-span-4">
                                <label class="fc-label">Contact Number</label>
                                <div class="fc-phone-group">
                                    <span>+63</span>
                                    <input type="tel" name="members[{{ $index }}][contact_number]"
                                           class="fc-input mobile-number"
                                           value="{{ $member['contact_number'] ?? '' }}"
                                           inputmode="numeric" maxlength="10"
                                           pattern="9[0-9]{9}" placeholder="9XXXXXXXXX">
                                </div>
                                <small class="fc-hint">Optional. 10 digits starting with 9.</small>
                            </div>
                            <div class="fc-form-group fc-span-4">
                                <label class="fc-label">Email Address</label>
                                <input type="email" name="members[{{ $index }}][email]"
                                       class="fc-input" value="{{ $member['email'] ?? '' }}"
                                       maxlength="255">
                            </div>
                            <div class="fc-form-group fc-span-4">
                                <label class="fc-label">Residency Type <span class="fc-required">*</span></label>
                                <select name="members[{{ $index }}][residency_type]"
                                        class="fc-select residency-type" required>
                                    <option value="">Select residency</option>
                                    <option value="Permanent" @selected(($member['residency_type'] ?? '') === 'Permanent')>Permanent</option>
                                    <option value="Temporary" @selected(($member['residency_type'] ?? '') === 'Temporary')>Temporary</option>
                                </select>
                            </div>
                            <div class="fc-form-group fc-span-4 residency-year-wrapper">
                                <label class="fc-label">
                                    Residency Start Year
                                    <span class="fc-required residency-year-required" hidden>*</span>
                                </label>
                                <input type="number" name="members[{{ $index }}][residency_start_year]"
                                       class="fc-input residency-year"
                                       value="{{ $member['residency_start_year'] ?? '' }}"
                                       min="1900" max="{{ now()->year }}" step="1">
                                <small class="fc-hint">Required for Temporary residents.</small>
                            </div>
                            <div class="fc-form-group fc-span-8">
                                <label class="fc-label">Occupation</label>
                                <input type="text" name="members[{{ $index }}][occupation]"
                                       class="fc-input" value="{{ $member['occupation'] ?? '' }}"
                                       maxlength="150">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="noMembersMessage" class="fc-empty-members"
                 @if(count($oldMembers)) hidden @endif>
                <span aria-hidden="true">♧</span>
                <strong>No additional household members added.</strong>
                <small>Use the Add Member button to include other household members.</small>
            </div>
        </section>

        {{-- SUBMISSION ACTIONS --}}
        <section class="fc-card fc-submit-card">
            <label class="fc-confirmation">
                <input type="checkbox" id="submissionConfirmation" required>
                <span>
                    I confirm that the information entered is based on the household data
                    collected and is ready for verification.
                </span>
            </label>

            <div class="fc-submit-actions">
                <button type="submit" id="submitHouseholdButton"
                        class="fc-button fc-button-primary fc-submit-button">
                    Submit for Verification
                </button>
                <button type="button" id="clearFormButton"
                        class="fc-button fc-button-danger">
                    Clear Form
                </button>
            </div>

            <p class="fc-help-text fc-submit-note">
                Submitting this form does not automatically create official resident or
                household records. The submission will first be reviewed by the authorized
                Barangay officials.
            </p>
        </section>
    </form>
</div>

{{-- TEMPLATE FOR DYNAMIC HOUSEHOLD MEMBERS --}}
<template id="memberTemplate">
    <div class="fc-member-card" data-member-index="__INDEX__">
        <div class="fc-member-heading">
            <h3 class="fc-member-title">Household Member</h3>
            <button type="button" class="fc-button fc-button-danger fc-button-small remove-member">
                Remove
            </button>
        </div>

        <div class="fc-form-grid">
            <div class="fc-form-group fc-span-4">
                <label class="fc-label">First Name <span class="fc-required">*</span></label>
                <input type="text" name="members[__INDEX__][first_name]" class="fc-input" maxlength="100" required>
            </div>
            <div class="fc-form-group fc-span-4">
                <label class="fc-label">Middle Name</label>
                <input type="text" name="members[__INDEX__][middle_name]" class="fc-input" maxlength="100">
            </div>
            <div class="fc-form-group fc-span-4">
                <label class="fc-label">Last Name <span class="fc-required">*</span></label>
                <input type="text" name="members[__INDEX__][last_name]" class="fc-input" maxlength="100" required>
            </div>
            <div class="fc-form-group fc-span-4">
                <label class="fc-label">Suffix</label>
                <input type="text" name="members[__INDEX__][suffix]" class="fc-input" maxlength="30">
            </div>
            <div class="fc-form-group fc-span-4">
                <label class="fc-label">Birth Date <span class="fc-required">*</span></label>
                <input type="date" name="members[__INDEX__][birth_date]"
                       class="fc-input" max="{{ now()->toDateString() }}" required>
            </div>
            <div class="fc-form-group fc-span-4">
                <label class="fc-label">Sex <span class="fc-required">*</span></label>
                <select name="members[__INDEX__][sex]" class="fc-select" required>
                    <option value="">Select sex</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div class="fc-form-group fc-span-4">
                <label class="fc-label">Civil Status <span class="fc-required">*</span></label>
                <select name="members[__INDEX__][civil_status]" class="fc-select" required>
                    <option value="">Select civil status</option>
                    @foreach(['Single', 'Married', 'Widowed', 'Separated', 'Annulled'] as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fc-form-group fc-span-4">
                <label class="fc-label">Relationship to Head <span class="fc-required">*</span></label>
                <input type="text" name="members[__INDEX__][relationship]"
                       class="fc-input" maxlength="100"
                       placeholder="e.g. Spouse, Son, Daughter" required>
            </div>
            <div class="fc-form-group fc-span-4">
                <label class="fc-label">Contact Number</label>
                <div class="fc-phone-group">
                    <span>+63</span>
                    <input type="tel" name="members[__INDEX__][contact_number]"
                           class="fc-input mobile-number" inputmode="numeric"
                           maxlength="10" pattern="9[0-9]{9}" placeholder="9XXXXXXXXX">
                </div>
                <small class="fc-hint">Optional. 10 digits starting with 9.</small>
            </div>
            <div class="fc-form-group fc-span-4">
                <label class="fc-label">Email Address</label>
                <input type="email" name="members[__INDEX__][email]" class="fc-input" maxlength="255">
            </div>
            <div class="fc-form-group fc-span-4">
                <label class="fc-label">Residency Type <span class="fc-required">*</span></label>
                <select name="members[__INDEX__][residency_type]"
                        class="fc-select residency-type" required>
                    <option value="">Select residency</option>
                    <option value="Permanent">Permanent</option>
                    <option value="Temporary">Temporary</option>
                </select>
            </div>
            <div class="fc-form-group fc-span-4 residency-year-wrapper">
                <label class="fc-label">
                    Residency Start Year
                    <span class="fc-required residency-year-required" hidden>*</span>
                </label>
                <input type="number" name="members[__INDEX__][residency_start_year]"
                       class="fc-input residency-year"
                       min="1900" max="{{ now()->year }}" step="1">
                <small class="fc-hint">Required for Temporary residents.</small>
            </div>
            <div class="fc-form-group fc-span-8">
                <label class="fc-label">Occupation</label>
                <input type="text" name="members[__INDEX__][occupation]"
                       class="fc-input" maxlength="150">
            </div>
        </div>
    </div>
</template>
@endsection

@push('styles')
<style>
    .fc-household-page {
        width: 100%;
        max-width: 100%;
        color: var(--fc-text, #26352e);
    }

    .fc-household-page *,
    .fc-household-page *::before,
    .fc-household-page *::after {
        box-sizing: border-box;
    }

    .fc-page-actions {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 16px;
    }

    .fc-info-text {
        margin: 4px 0 0;
        line-height: 1.6;
    }

    .fc-form-card {
        margin-bottom: 18px;
    }

    .fc-section-heading {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        min-width: 0;
    }

    .fc-section-heading-between {
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .fc-section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 40px;
        width: 40px;
        height: 40px;
        border-radius: 11px;
        background: var(--fc-green-light, #e8f4ed);
        color: var(--fc-green-dark, #185b40);
        font-size: 22px;
        font-weight: 700;
    }

    .fc-section-heading .fc-card-title {
        margin: 0 0 4px;
    }

    .fc-form-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 0 16px;
    }

    .fc-span-4 { grid-column: span 4; }
    .fc-span-6 { grid-column: span 6; }
    .fc-span-8 { grid-column: span 8; }
    .fc-span-12 { grid-column: span 12; }

    .fc-household-page .fc-form-group {
        min-width: 0;
        margin-bottom: 17px;
    }

    .fc-household-page .fc-label {
        display: block;
        margin-bottom: 7px;
        color: #34483d;
        font-size: 13px;
        font-weight: 700;
    }

    .fc-household-page .fc-input,
    .fc-household-page .fc-select,
    .fc-household-page .fc-textarea {
        display: block;
        width: 100%;
        min-width: 0;
        min-height: 44px;
        padding: 10px 12px;
        border: 1px solid var(--fc-border, #e5e9e6);
        border-radius: 9px;
        outline: none;
        background: #fff;
        color: var(--fc-text, #26352e);
        font: inherit;
        font-size: 14px;
        transition: border-color .2s, box-shadow .2s;
    }

    .fc-household-page .fc-textarea {
        min-height: 88px;
        resize: vertical;
    }

    .fc-household-page .fc-input:focus,
    .fc-household-page .fc-select:focus,
    .fc-household-page .fc-textarea:focus {
        border-color: var(--fc-green, #237a57);
        box-shadow: 0 0 0 3px rgba(35, 122, 87, .12);
    }

    .fc-household-page .fc-input-invalid {
        border-color: #b42335;
    }

    .fc-household-page .fc-required {
        color: #b42335;
    }

    .fc-household-page .fc-hint,
    .fc-household-page .fc-help-text {
        display: block;
        margin-top: 6px;
        color: var(--fc-muted, #718078);
        font-size: 11px;
        line-height: 1.55;
    }

    .fc-household-page .fc-help-text {
        margin: 0 0 15px;
        font-size: 12px;
    }

    .fc-household-page .fc-help-bottom {
        margin: 13px 0 0;
    }

    .fc-phone-group {
        display: flex;
        width: 100%;
    }

    .fc-phone-group > span {
        display: inline-flex;
        align-items: center;
        padding: 0 12px;
        border: 1px solid var(--fc-border, #e5e9e6);
        border-right: 0;
        border-radius: 9px 0 0 9px;
        background: #f5f8f6;
        color: #53645a;
        font-size: 13px;
        font-weight: 700;
    }

    .fc-phone-group .fc-input {
        border-radius: 0 9px 9px 0;
    }

    .fc-action-row,
    .fc-submit-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 9px;
        margin-bottom: 14px;
    }

    .fc-household-page .fc-button {
        font-family: inherit;
    }

    .fc-gps-status {
        margin: 0 0 14px;
    }

    .fc-gps-details {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        padding: 14px;
        border: 1px solid var(--fc-border, #e5e9e6);
        border-radius: 10px;
        background: #fafcfb;
    }

    .fc-gps-details > div {
        min-width: 0;
    }

    .fc-gps-details strong {
        display: block;
        margin-top: 3px;
        overflow-wrap: anywhere;
        font-size: 13px;
    }

    .fc-detail-label {
        display: block;
        color: var(--fc-muted, #718078);
        font-size: 11px;
    }

    .fc-member-card {
        margin-top: 15px;
        padding: 17px;
        border: 1px solid var(--fc-border, #e5e9e6);
        border-radius: 11px;
        background: #fafcfb;
    }

    .fc-member-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 15px;
    }

    .fc-member-title {
        margin: 0;
        color: var(--fc-green-dark, #185b40);
        font-size: 14px;
        font-weight: 800;
    }

    .fc-empty-members {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        padding: 28px 15px;
        border: 1px dashed #cbd6ce;
        border-radius: 10px;
        color: var(--fc-muted, #718078);
        text-align: center;
    }

    .fc-empty-members > span {
        color: var(--fc-green, #237a57);
        font-size: 27px;
    }

    .fc-empty-members strong {
        color: var(--fc-text, #26352e);
        font-size: 13px;
    }

    .fc-empty-members small {
        font-size: 11px;
    }

    .fc-button-small {
        min-height: 34px;
        padding: 6px 10px;
        font-size: 11px;
    }

    .fc-submit-card {
        margin-bottom: 22px;
    }

    .fc-confirmation {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 18px;
        color: var(--fc-text, #26352e);
        font-size: 13px;
        line-height: 1.6;
        cursor: pointer;
    }

    .fc-confirmation input {
        flex: 0 0 17px;
        width: 17px;
        height: 17px;
        margin-top: 2px;
        accent-color: var(--fc-green, #237a57);
    }

    .fc-submit-actions {
        margin-bottom: 0;
    }

    .fc-submit-button {
        flex: 1 1 220px;
    }

    .fc-submit-note {
        margin: 13px 0 0;
    }

    @media (max-width: 767px) {
        .fc-span-4,
        .fc-span-6,
        .fc-span-8 {
            grid-column: span 6;
        }

        .fc-household-page .fc-card {
            padding: 17px;
        }
    }

    @media (max-width: 575px) {
        .fc-page-actions {
            justify-content: stretch;
        }

        .fc-page-actions .fc-button {
            width: 100%;
        }

        .fc-span-4,
        .fc-span-6,
        .fc-span-8,
        .fc-span-12 {
            grid-column: span 12;
        }

        .fc-section-heading-between {
            align-items: flex-start;
        }

        .fc-section-heading-between > .fc-button {
            width: 100%;
        }

        .fc-gps-details {
            grid-template-columns: 1fr;
        }

        .fc-action-row,
        .fc-submit-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .fc-action-row .fc-button,
        .fc-submit-actions .fc-button {
            width: 100%;
        }

        .fc-member-card {
            padding: 13px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('householdCollectionForm');

    if (!form) {
        return;
    }

    const membersContainer = document.getElementById('membersContainer');
    const memberTemplate = document.getElementById('memberTemplate');
    const addMemberButton = document.getElementById('addMemberButton');
    const clearFormButton = document.getElementById('clearFormButton');
    const noMembersMessage = document.getElementById('noMembersMessage');

    const captureGpsButton = document.getElementById('captureGpsButton');
    const clearGpsButton = document.getElementById('clearGpsButton');
    const gpsStatus = document.getElementById('gpsStatus');

    const gpsLatitude = document.getElementById('gpsLatitude');
    const gpsLongitude = document.getElementById('gpsLongitude');
    const gpsAccuracy = document.getElementById('gpsAccuracy');
    const gpsCapturedAt = document.getElementById('gpsCapturedAt');

    const latitudeDisplay = document.getElementById('latitudeDisplay');
    const longitudeDisplay = document.getElementById('longitudeDisplay');
    const accuracyDisplay = document.getElementById('accuracyDisplay');
    const capturedAtDisplay = document.getElementById('capturedAtDisplay');

    let nextMemberIndex = Math.max(
        -1,
        ...Array.from(membersContainer.querySelectorAll('[data-member-index]'))
            .map(card => Number(card.dataset.memberIndex))
    ) + 1;

    function updateMemberDisplay() {
        const cards = membersContainer.querySelectorAll('.fc-member-card');

        cards.forEach((card, index) => {
            const heading = card.querySelector('.fc-member-title');

            if (heading) {
                heading.textContent = 'Household Member ' + (index + 1);
            }
        });

        noMembersMessage.hidden = cards.length > 0;
    }

    function updateResidencyRequirement(select) {
        const wrapper = select.closest('.fc-form-group')
            ?.parentElement
            ?.querySelector('.residency-year-wrapper');

        if (!wrapper) {
            return;
        }

        const yearInput = wrapper.querySelector('.residency-year');
        const requiredMark = wrapper.querySelector('.residency-year-required');
        const isTemporary = select.value === 'Temporary';

        yearInput.required = isTemporary;
        requiredMark.hidden = !isTemporary;

        if (!isTemporary) {
            yearInput.setCustomValidity('');
        }
    }

    function applyResidencyRules(root) {
        root.querySelectorAll('.residency-type').forEach(select => {
            updateResidencyRequirement(select);
        });
    }

    function addMember() {
        const html = memberTemplate.innerHTML.replaceAll(
            '__INDEX__',
            String(nextMemberIndex)
        );

        membersContainer.insertAdjacentHTML('beforeend', html);
        nextMemberIndex++;

        const newCard = membersContainer.lastElementChild;

        applyResidencyRules(newCard);
        updateMemberDisplay();

        newCard.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    addMemberButton.addEventListener('click', addMember);

    membersContainer.addEventListener('click', function (event) {
        const removeButton = event.target.closest('.remove-member');

        if (!removeButton) {
            return;
        }

        const card = removeButton.closest('.fc-member-card');

        if (card) {
            card.remove();
            updateMemberDisplay();
        }
    });

    form.addEventListener('change', function (event) {
        if (event.target.matches('.residency-type')) {
            updateResidencyRequirement(event.target);
        }
    });

    /*
     * Philippine mobile number:
     * 10 digits beginning with 9. The +63 prefix is separate.
     */
    form.addEventListener('input', function (event) {
        if (!event.target.matches('.mobile-number')) {
            return;
        }

        let value = event.target.value.replace(/\D/g, '');

        if (value.startsWith('0')) {
            value = value.substring(1);
        }

        if (value.startsWith('63')) {
            value = value.substring(2);
        }

        event.target.value = value.substring(0, 10);
    });

    function showGpsStatus(message, type) {
        const statusClasses = {
            success: 'fc-alert-success',
            danger: 'fc-alert-error',
            info: 'fc-alert-info',
            secondary: 'fc-alert-neutral'
        };

        gpsStatus.className = 'fc-alert fc-gps-status ' +
            (statusClasses[type] || 'fc-alert-neutral');

        gpsStatus.textContent = message;
    }

    function restoreGpsButton() {
        captureGpsButton.disabled = false;
        captureGpsButton.textContent = '⌖ Capture Household GPS';
    }

    function clearGps() {
        gpsLatitude.value = '';
        gpsLongitude.value = '';
        gpsAccuracy.value = '';
        gpsCapturedAt.value = '';

        latitudeDisplay.textContent = 'Not captured';
        longitudeDisplay.textContent = 'Not captured';
        accuracyDisplay.textContent = 'Not available';
        capturedAtDisplay.textContent = 'Not captured';

        showGpsStatus('GPS location has not been captured.', 'secondary');
        restoreGpsButton();
    }

    function captureGps() {
        if (!navigator.geolocation) {
            showGpsStatus(
                'This browser does not support GPS location access.',
                'danger'
            );
            return;
        }

        if (!window.isSecureContext) {
            showGpsStatus(
                'GPS access requires a secure HTTPS connection or localhost. Open this page through the deployed HTTPS address or localhost.',
                'danger'
            );
            return;
        }

        captureGpsButton.disabled = true;
        captureGpsButton.textContent = 'Capturing GPS...';

        showGpsStatus(
            'Requesting your device location. Please wait...',
            'info'
        );

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const coords = position.coords;
                const capturedAt = new Date();

                gpsLatitude.value = Number(coords.latitude).toFixed(7);
                gpsLongitude.value = Number(coords.longitude).toFixed(7);
                gpsAccuracy.value = Number(coords.accuracy).toFixed(2);
                gpsCapturedAt.value = capturedAt.toISOString();

                latitudeDisplay.textContent = gpsLatitude.value;
                longitudeDisplay.textContent = gpsLongitude.value;
                accuracyDisplay.textContent =
                    Number(coords.accuracy).toFixed(2) + ' meters';
                capturedAtDisplay.textContent = capturedAt.toLocaleString();

                showGpsStatus(
                    'Household GPS location captured successfully.',
                    'success'
                );

                restoreGpsButton();
            },
            function (error) {
                let message = 'Unable to capture GPS location.';

                if (error.code === error.PERMISSION_DENIED) {
                    message = 'Location permission was denied. Allow location access in your browser settings.';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    message = 'Your device location is currently unavailable. Try again outdoors or near a window.';
                } else if (error.code === error.TIMEOUT) {
                    message = 'GPS request timed out. Please try again.';
                }

                showGpsStatus(message, 'danger');
                restoreGpsButton();
            },
            {
                enableHighAccuracy: true,
                timeout: 20000,
                maximumAge: 0
            }
        );
    }

    captureGpsButton.addEventListener('click', captureGps);
    clearGpsButton.addEventListener('click', clearGps);

    clearFormButton.addEventListener('click', function () {
        const confirmed = window.confirm(
            'Are you sure you want to clear all household information entered in this form?'
        );

        if (!confirmed) {
            return;
        }

        form.reset();

        membersContainer.innerHTML = '';
        nextMemberIndex = 0;

        document.getElementById('submissionConfirmation').checked = false;

        clearGps();
        updateMemberDisplay();
        applyResidencyRules(form);

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    form.addEventListener('submit', function (event) {
        applyResidencyRules(form);

        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
            return;
        }

        const confirmed = window.confirm(
            'Submit this household information for Captain/Secretary verification?'
        );

        if (!confirmed) {
            event.preventDefault();
            return;
        }

        const submitButton = document.getElementById('submitHouseholdButton');

        submitButton.disabled = true;
        submitButton.textContent = 'Submitting...';
    });

    /*
     * Restore previously captured GPS values after validation errors.
     */
    if (gpsLatitude.value && gpsLongitude.value) {
        latitudeDisplay.textContent = gpsLatitude.value;
        longitudeDisplay.textContent = gpsLongitude.value;

        if (gpsAccuracy.value) {
            accuracyDisplay.textContent = gpsAccuracy.value + ' meters';
        }

        if (gpsCapturedAt.value) {
            const restoredDate = new Date(gpsCapturedAt.value);

            if (!Number.isNaN(restoredDate.getTime())) {
                capturedAtDisplay.textContent = restoredDate.toLocaleString();
            }
        }

        showGpsStatus(
            'Previously captured GPS information has been restored.',
            'success'
        );
    }

    updateMemberDisplay();
    applyResidencyRules(form);
});
</script>
@endpush
