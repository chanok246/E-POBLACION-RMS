
@extends('field-personnel.layouts.app')

@section('title', 'Household Collection')

@section('content')
<div class="container-fluid py-3 field-collection-page">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h4 class="fw-bold mb-1">Household Collection</h4>
                    <p class="text-muted mb-0 small">
                        Record household information for verification by the Barangay.
                    </p>
                </div>

                <a href="{{ route('field-personnel.home') }}"
                   class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong>Please review the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="alert alert-info border-0">
                <div class="d-flex gap-2">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <div>
                        <strong>Important reminder</strong>
                        <p class="mb-0 small">
                            Submitted information will be saved as
                            <strong>Pending for Verification</strong>.
                            It will not become an official household or resident
                            record until approved by the authorized Captain or Secretary.
                        </p>
                    </div>
                </div>
            </div>

            <form id="householdCollectionForm"
                  method="POST"
                  action="{{ url('/field-personnel/households') }}">
                @csrf

                {{-- HOUSEHOLD INFORMATION --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-house-door me-2"></i>
                            Household Information
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="purok" class="form-label fw-semibold">
                                    Purok <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       id="purok"
                                       name="household[purok]"
                                       class="form-control @error('household.purok') is-invalid @enderror"
                                       value="{{ old('household.purok') }}"
                                       maxlength="100"
                                       required>
                                @error('household.purok')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label fw-semibold">
                                    Complete Household Address
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea id="address"
                                          name="household[address]"
                                          class="form-control @error('household.address') is-invalid @enderror"
                                          rows="2"
                                          maxlength="255"
                                          required>{{ old('household.address') }}</textarea>
                                @error('household.address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- GPS LOCATION --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-geo-alt me-2"></i>
                            Household GPS Location
                        </h5>
                    </div>

                    <div class="card-body">
                        <p class="text-muted small">
                            Capture the location of the household using this phone.
                            Allow location permission when your browser asks.
                        </p>

                        <input type="hidden"
                               name="household[latitude]"
                               id="gpsLatitude"
                               value="{{ old('household.latitude') }}">

                        <input type="hidden"
                               name="household[longitude]"
                               id="gpsLongitude"
                               value="{{ old('household.longitude') }}">

                        <input type="hidden"
                               name="household[gps_accuracy_meters]"
                               id="gpsAccuracy"
                               value="{{ old('household.gps_accuracy_meters') }}">

                        <input type="hidden"
                               name="household[gps_captured_at]"
                               id="gpsCapturedAt"
                               value="{{ old('household.gps_captured_at') }}">

                        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                            <button type="button"
                                    id="captureGpsButton"
                                    class="btn btn-primary">
                                <i class="bi bi-crosshair me-1"></i>
                                Capture Household GPS
                            </button>

                            <button type="button"
                                    id="clearGpsButton"
                                    class="btn btn-outline-secondary">
                                Clear GPS
                            </button>
                        </div>

                        <div id="gpsStatus"
                             class="alert alert-secondary small mb-0"
                             role="status"
                             aria-live="polite">
                            GPS location has not been captured.
                        </div>

                        <div class="row g-2 mt-2 small">
                            <div class="col-12 col-sm-6">
                                <span class="text-muted">Latitude:</span>
                                <strong id="latitudeDisplay">Not captured</strong>
                            </div>
                            <div class="col-12 col-sm-6">
                                <span class="text-muted">Longitude:</span>
                                <strong id="longitudeDisplay">Not captured</strong>
                            </div>
                            <div class="col-12 col-sm-6">
                                <span class="text-muted">GPS accuracy:</span>
                                <strong id="accuracyDisplay">Not available</strong>
                            </div>
                            <div class="col-12 col-sm-6">
                                <span class="text-muted">Captured at:</span>
                                <strong id="capturedAtDisplay">Not captured</strong>
                            </div>
                        </div>

                        <small class="text-muted d-block mt-3">
                            GPS accuracy depends on the device and available location
                            signals. Location access generally requires HTTPS or localhost.
                        </small>
                    </div>
                </div>

                {{-- HOUSEHOLD HEAD --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-person-badge me-2"></i>
                            Household Head
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">
                                    First Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="household_head[first_name]"
                                       class="form-control"
                                       value="{{ old('household_head.first_name') }}"
                                       maxlength="100"
                                       required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Middle Name</label>
                                <input type="text"
                                       name="household_head[middle_name]"
                                       class="form-control"
                                       value="{{ old('household_head.middle_name') }}"
                                       maxlength="100">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">
                                    Last Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="household_head[last_name]"
                                       class="form-control"
                                       value="{{ old('household_head.last_name') }}"
                                       maxlength="100"
                                       required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Suffix</label>
                                <input type="text"
                                       name="household_head[suffix]"
                                       class="form-control"
                                       value="{{ old('household_head.suffix') }}"
                                       maxlength="30"
                                       placeholder="Jr., Sr., III">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">
                                    Birth Date <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       name="household_head[birth_date]"
                                       class="form-control"
                                       value="{{ old('household_head.birth_date') }}"
                                       max="{{ now()->toDateString() }}"
                                       required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">
                                    Sex <span class="text-danger">*</span>
                                </label>
                                <select name="household_head[sex]" class="form-select" required>
                                    <option value="">Select sex</option>
                                    <option value="Male" @selected(old('household_head.sex') === 'Male')>Male</option>
                                    <option value="Female" @selected(old('household_head.sex') === 'Female')>Female</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">
                                    Civil Status <span class="text-danger">*</span>
                                </label>
                                <select name="household_head[civil_status]" class="form-select" required>
                                    <option value="">Select civil status</option>
                                    @foreach(['Single', 'Married', 'Widowed', 'Separated', 'Annulled'] as $status)
                                        <option value="{{ $status }}"
                                            @selected(old('household_head.civil_status') === $status)>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Contact Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">+63</span>
                                    <input type="tel"
                                           name="household_head[contact_number]"
                                           class="form-control mobile-number"
                                           value="{{ old('household_head.contact_number') }}"
                                           inputmode="numeric"
                                           maxlength="10"
                                           pattern="9[0-9]{9}"
                                           placeholder="9XXXXXXXXX">
                                </div>
                                <small class="text-muted">Optional. Enter 10 digits starting with 9.</small>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email"
                                       name="household_head[email]"
                                       class="form-control"
                                       value="{{ old('household_head.email') }}"
                                       maxlength="255">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">
                                    Residency Type <span class="text-danger">*</span>
                                </label>
                                <select name="household_head[residency_type]"
                                        class="form-select residency-type"
                                        required>
                                    <option value="">Select residency</option>
                                    <option value="Permanent"
                                        @selected(old('household_head.residency_type') === 'Permanent')>
                                        Permanent
                                    </option>
                                    <option value="Temporary"
                                        @selected(old('household_head.residency_type') === 'Temporary')>
                                        Temporary
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4 residency-year-wrapper">
                                <label class="form-label fw-semibold">
                                    Residency Start Year
                                    <span class="text-danger residency-year-required" hidden>*</span>
                                </label>
                                <input type="number"
                                       name="household_head[residency_start_year]"
                                       class="form-control residency-year"
                                       value="{{ old('household_head.residency_start_year') }}"
                                       min="1900"
                                       max="{{ now()->year }}"
                                       step="1">
                                <small class="text-muted">
                                    Required for Temporary residents.
                                </small>
                            </div>

                            <div class="col-12 col-md-8">
                                <label class="form-label fw-semibold">Occupation</label>
                                <input type="text"
                                       name="household_head[occupation]"
                                       class="form-control"
                                       value="{{ old('household_head.occupation') }}"
                                       maxlength="150">
                            </div>

                        </div>
                    </div>
                </div>

                {{-- HOUSEHOLD MEMBERS --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-people me-2"></i>
                            Other Household Members
                        </h5>

                        <button type="button"
                                id="addMemberButton"
                                class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i>
                            Add Member
                        </button>
                    </div>

                    <div class="card-body">
                        <p class="text-muted small">
                            Add the other individuals living in this household.
                            The household head is already recorded above.
                        </p>

                        <div id="membersContainer">
                            @php
                                $oldMembers = old('members', []);
                            @endphp

                            @foreach($oldMembers as $index => $member)
                                <div class="member-card border rounded-3 p-3 mb-3"
                                     data-member-index="{{ $index }}">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold mb-0 member-heading">
                                            Household Member {{ $loop->iteration }}
                                        </h6>
                                        <button type="button"
                                                class="btn btn-outline-danger btn-sm remove-member">
                                            <i class="bi bi-trash"></i> Remove
                                        </button>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold">First Name *</label>
                                            <input type="text"
                                                   name="members[{{ $index }}][first_name]"
                                                   class="form-control"
                                                   value="{{ $member['first_name'] ?? '' }}"
                                                   maxlength="100"
                                                   required>
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold">Middle Name</label>
                                            <input type="text"
                                                   name="members[{{ $index }}][middle_name]"
                                                   class="form-control"
                                                   value="{{ $member['middle_name'] ?? '' }}"
                                                   maxlength="100">
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold">Last Name *</label>
                                            <input type="text"
                                                   name="members[{{ $index }}][last_name]"
                                                   class="form-control"
                                                   value="{{ $member['last_name'] ?? '' }}"
                                                   maxlength="100"
                                                   required>
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold">Suffix</label>
                                            <input type="text"
                                                   name="members[{{ $index }}][suffix]"
                                                   class="form-control"
                                                   value="{{ $member['suffix'] ?? '' }}"
                                                   maxlength="30">
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold">Birth Date *</label>
                                            <input type="date"
                                                   name="members[{{ $index }}][birth_date]"
                                                   class="form-control"
                                                   value="{{ $member['birth_date'] ?? '' }}"
                                                   max="{{ now()->toDateString() }}"
                                                   required>
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold">Sex *</label>
                                            <select name="members[{{ $index }}][sex]"
                                                    class="form-select"
                                                    required>
                                                <option value="">Select sex</option>
                                                <option value="Male" @selected(($member['sex'] ?? '') === 'Male')>Male</option>
                                                <option value="Female" @selected(($member['sex'] ?? '') === 'Female')>Female</option>
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold">Civil Status *</label>
                                            <select name="members[{{ $index }}][civil_status]"
                                                    class="form-select"
                                                    required>
                                                <option value="">Select civil status</option>
                                                @foreach(['Single', 'Married', 'Widowed', 'Separated', 'Annulled'] as $status)
                                                    <option value="{{ $status }}"
                                                        @selected(($member['civil_status'] ?? '') === $status)>
                                                        {{ $status }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold">Relationship to Head *</label>
                                            <input type="text"
                                                   name="members[{{ $index }}][relationship]"
                                                   class="form-control"
                                                   value="{{ $member['relationship'] ?? '' }}"
                                                   maxlength="100"
                                                   placeholder="e.g. Spouse, Son, Daughter"
                                                   required>
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold">Contact Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text">+63</span>
                                                <input type="tel"
                                                       name="members[{{ $index }}][contact_number]"
                                                       class="form-control mobile-number"
                                                       value="{{ $member['contact_number'] ?? '' }}"
                                                       inputmode="numeric"
                                                       maxlength="10"
                                                       pattern="9[0-9]{9}"
                                                       placeholder="9XXXXXXXXX">
                                            </div>
                                            <small class="text-muted">Optional. 10 digits starting with 9.</small>
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold">Email Address</label>
                                            <input type="email"
                                                   name="members[{{ $index }}][email]"
                                                   class="form-control"
                                                   value="{{ $member['email'] ?? '' }}"
                                                   maxlength="255">
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold">Residency Type *</label>
                                            <select name="members[{{ $index }}][residency_type]"
                                                    class="form-select residency-type"
                                                    required>
                                                <option value="">Select residency</option>
                                                <option value="Permanent"
                                                    @selected(($member['residency_type'] ?? '') === 'Permanent')>
                                                    Permanent
                                                </option>
                                                <option value="Temporary"
                                                    @selected(($member['residency_type'] ?? '') === 'Temporary')>
                                                    Temporary
                                                </option>
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-4 residency-year-wrapper">
                                            <label class="form-label fw-semibold">
                                                Residency Start Year
                                                <span class="text-danger residency-year-required" hidden>*</span>
                                            </label>
                                            <input type="number"
                                                   name="members[{{ $index }}][residency_start_year]"
                                                   class="form-control residency-year"
                                                   value="{{ $member['residency_start_year'] ?? '' }}"
                                                   min="1900"
                                                   max="{{ now()->year }}"
                                                   step="1">
                                            <small class="text-muted">Required for Temporary residents.</small>
                                        </div>

                                        <div class="col-12 col-md-8">
                                            <label class="form-label fw-semibold">Occupation</label>
                                            <input type="text"
                                                   name="members[{{ $index }}][occupation]"
                                                   class="form-control"
                                                   value="{{ $member['occupation'] ?? '' }}"
                                                   maxlength="150">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div id="noMembersMessage"
                             class="text-center text-muted border rounded-3 p-4"
                             @if(count($oldMembers)) hidden @endif>
                            <i class="bi bi-people fs-2 d-block mb-2"></i>
                            No additional household members added.
                        </div>
                    </div>
                </div>

                {{-- SUBMISSION ACTIONS --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="submissionConfirmation"
                                   required>
                            <label class="form-check-label small" for="submissionConfirmation">
                                I confirm that the information entered is based on the
                                household data collected and is ready for verification.
                            </label>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <button type="submit"
                                    id="submitHouseholdButton"
                                    class="btn btn-success flex-fill">
                                <i class="bi bi-send me-1"></i>
                                Submit for Verification
                            </button>

                            <button type="button"
                                    id="clearFormButton"
                                    class="btn btn-outline-danger">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Clear Form
                            </button>
                        </div>

                        <small class="text-muted d-block mt-3">
                            Submitting this form does not automatically create official
                            resident or household records. The submission will first be
                            reviewed by the authorized Barangay officials.
                        </small>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- TEMPLATE FOR DYNAMIC HOUSEHOLD MEMBERS --}}
<template id="memberTemplate">
    <div class="member-card border rounded-3 p-3 mb-3"
         data-member-index="__INDEX__">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 member-heading">Household Member</h6>
            <button type="button"
                    class="btn btn-outline-danger btn-sm remove-member">
                <i class="bi bi-trash"></i> Remove
            </button>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">First Name *</label>
                <input type="text" name="members[__INDEX__][first_name]"
                       class="form-control" maxlength="100" required>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Middle Name</label>
                <input type="text" name="members[__INDEX__][middle_name]"
                       class="form-control" maxlength="100">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Last Name *</label>
                <input type="text" name="members[__INDEX__][last_name]"
                       class="form-control" maxlength="100" required>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Suffix</label>
                <input type="text" name="members[__INDEX__][suffix]"
                       class="form-control" maxlength="30">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Birth Date *</label>
                <input type="date" name="members[__INDEX__][birth_date]"
                       class="form-control" max="{{ now()->toDateString() }}" required>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Sex *</label>
                <select name="members[__INDEX__][sex]" class="form-select" required>
                    <option value="">Select sex</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Civil Status *</label>
                <select name="members[__INDEX__][civil_status]" class="form-select" required>
                    <option value="">Select civil status</option>
                    @foreach(['Single', 'Married', 'Widowed', 'Separated', 'Annulled'] as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Relationship to Head *</label>
                <input type="text"
                       name="members[__INDEX__][relationship]"
                       class="form-control"
                       maxlength="100"
                       placeholder="e.g. Spouse, Son, Daughter"
                       required>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Contact Number</label>
                <div class="input-group">
                    <span class="input-group-text">+63</span>
                    <input type="tel"
                           name="members[__INDEX__][contact_number]"
                           class="form-control mobile-number"
                           inputmode="numeric"
                           maxlength="10"
                           pattern="9[0-9]{9}"
                           placeholder="9XXXXXXXXX">
                </div>
                <small class="text-muted">Optional. 10 digits starting with 9.</small>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email"
                       name="members[__INDEX__][email]"
                       class="form-control"
                       maxlength="255">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Residency Type *</label>
                <select name="members[__INDEX__][residency_type]"
                        class="form-select residency-type"
                        required>
                    <option value="">Select residency</option>
                    <option value="Permanent">Permanent</option>
                    <option value="Temporary">Temporary</option>
                </select>
            </div>

            <div class="col-12 col-md-4 residency-year-wrapper">
                <label class="form-label fw-semibold">
                    Residency Start Year
                    <span class="text-danger residency-year-required" hidden>*</span>
                </label>
                <input type="number"
                       name="members[__INDEX__][residency_start_year]"
                       class="form-control residency-year"
                       min="1900"
                       max="{{ now()->year }}"
                       step="1">
                <small class="text-muted">Required for Temporary residents.</small>
            </div>

            <div class="col-12 col-md-8">
                <label class="form-label fw-semibold">Occupation</label>
                <input type="text"
                       name="members[__INDEX__][occupation]"
                       class="form-control"
                       maxlength="150">
            </div>
        </div>
    </div>
</template>

<style>
    .field-collection-page .card {
        border-radius: 12px;
    }

    .field-collection-page .card-header {
        border-bottom: 1px solid #edf0f2;
        border-radius: 12px 12px 0 0;
    }

    .field-collection-page .form-label {
        font-size: .9rem;
    }

    .field-collection-page .form-control,
    .field-collection-page .form-select,
    .field-collection-page .input-group-text {
        min-height: 43px;
    }

    .field-collection-page textarea.form-control {
        min-height: 75px;
    }

    .field-collection-page .member-card {
        background: #fbfcfd;
    }

    @media (max-width: 575.98px) {
        .field-collection-page .btn {
            min-height: 42px;
        }

        .field-collection-page .card-body {
            padding: 1rem;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('householdCollectionForm');
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
        const cards = membersContainer.querySelectorAll('.member-card');

        cards.forEach((card, index) => {
            const heading = card.querySelector('.member-heading');
            if (heading) {
                heading.textContent = 'Household Member ' + (index + 1);
            }
        });

        noMembersMessage.hidden = cards.length > 0;
    }

    function updateResidencyRequirement(select) {
        const card = select.closest('.card-body') || select.closest('.member-card');
        const wrapper = select.closest('.row')?.querySelector(
            '.residency-year-wrapper'
        );

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

        const card = removeButton.closest('.member-card');

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
     * Philippine mobile number input:
     * exactly 10 digits beginning with 9.
     * The +63 prefix is displayed separately.
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
        gpsStatus.className = 'alert alert-' + type + ' small mb-0';
        gpsStatus.textContent = message;
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
    }

    function captureGps() {
        if (!navigator.geolocation) {
            showGpsStatus(
                'This browser does not support GPS location access.',
                'danger'
            );
            return;
        }

        captureGpsButton.disabled = true;
        captureGpsButton.textContent = 'Capturing GPS...';

        showGpsStatus(
            'Requesting your phone location. Please wait...',
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

                captureGpsButton.disabled = false;
                captureGpsButton.innerHTML =
                    '<i class="bi bi-crosshair me-1"></i> Capture Household GPS';
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

                captureGpsButton.disabled = false;
                captureGpsButton.innerHTML =
                    '<i class="bi bi-crosshair me-1"></i> Capture Household GPS';
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
        submitButton.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
    });

    /*
     * Restore GPS display if validation returns the user to this form.
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
@endsection
