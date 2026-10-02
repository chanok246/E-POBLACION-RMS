@extends('layouts.app')

@section('title', 'Upload Official Template | e-Poblacion')

@section('content')

<div class="container-fluid py-4">

    {{-- ================================================================
         PAGE HEADER
         ================================================================ --}}

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">

        <div>

            <div class="d-flex align-items-center gap-2 mb-2">

                <a
                    href="{{ route('document-templates.index') }}"
                    class="btn btn-sm btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left"></i>
                </a>

                <span class="text-muted small">
                    Official Document Templates
                </span>

            </div>

            <h1 class="h3 fw-bold mb-1">
                Upload Official Template
            </h1>

            <p class="text-muted mb-0">
                Register an official barangay document template for use
                in the Record Management System.
            </p>

        </div>

    </div>


    {{-- ================================================================
         VALIDATION ERRORS
         ================================================================ --}}

    @if($errors->any())

        <div class="alert alert-danger border-0 shadow-sm">

            <div class="d-flex align-items-start gap-3">

                <i class="bi bi-exclamation-triangle fs-4"></i>

                <div>

                    <div class="fw-bold mb-2">
                        Please correct the following errors:
                    </div>

                    <ul class="mb-0">

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            </div>

        </div>

    @endif


    {{-- ================================================================
         MAIN FORM
         ================================================================ --}}

    <div class="row justify-content-center">

        <div class="col-12 col-xl-8">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-transparent border-0 py-4 px-4">

                    <div class="d-flex align-items-center gap-3">

                        <div
                            class="rounded-3 d-flex align-items-center justify-content-center"
                            style="width: 48px; height: 48px; background: rgba(13, 110, 253, 0.1);"
                        >
                            <i class="bi bi-file-earmark-word fs-4 text-primary"></i>
                        </div>

                        <div>

                            <h5 class="fw-bold mb-1">
                                Official Template Details
                            </h5>

                            <p class="text-muted small mb-0">
                                Upload the approved barangay document template
                                in Microsoft Word format.
                            </p>

                        </div>

                    </div>

                </div>


                <div class="card-body px-4 pb-4">

                    <form
                        action="{{ route('document-templates.store') }}"
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        @csrf


                        {{-- ====================================================
                             DOCUMENT TYPE
                             ==================================================== --}}

                        <div class="mb-4">

                            <label
                                for="document_type"
                                class="form-label fw-semibold"
                            >
                                Document Type
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                name="document_type"
                                id="document_type"
                                class="form-select @error('document_type') is-invalid @enderror"
                                required
                            >

                                <option value="">
                                    Select document type
                                </option>

                                @foreach($documentTypes as $documentType)

                                    <option
                                        value="{{ $documentType }}"
                                        @selected(
                                            old('document_type') === $documentType
                                        )
                                    >
                                        {{ $documentType }}
                                    </option>

                                @endforeach

                            </select>

                            @error('document_type')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                            <div class="form-text">
                                Select the type of barangay document that
                                this official template represents.
                            </div>

                        </div>


                        {{-- ====================================================
                             TEMPLATE NAME
                             ==================================================== --}}

                        <div class="mb-4">

                            <label
                                for="template_name"
                                class="form-label fw-semibold"
                            >
                                Template Name
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="template_name"
                                id="template_name"
                                value="{{ old('template_name') }}"
                                class="form-control @error('template_name') is-invalid @enderror"
                                placeholder="Example: Official Certificate of Indigency"
                                maxlength="255"
                                required
                            >

                            @error('template_name')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                            <div class="form-text">
                                Use a clear name that identifies the official
                                barangay template.
                            </div>

                        </div>


                        {{-- ====================================================
                             TEMPLATE FILE
                             ==================================================== --}}

                        <div class="mb-4">

                            <label
                                for="template_file"
                                class="form-label fw-semibold"
                            >
                                Official Template File
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="file"
                                name="template_file"
                                id="template_file"
                                class="form-control @error('template_file') is-invalid @enderror"
                                accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                required
                            >

                            @error('template_file')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                            <div class="form-text">
                                Accepted file format: <strong>DOCX</strong>.
                                Maximum file size: <strong>5 MB</strong>.
                            </div>

                        </div>


                        {{-- ====================================================
                             TEMPLATE REQUIREMENTS
                             ==================================================== --}}

                        <div class="alert alert-light border mb-4">

                            <div class="d-flex align-items-start gap-3">

                                <i class="bi bi-info-circle text-primary fs-5"></i>

                                <div>

                                    <h6 class="fw-bold mb-2">
                                        Template Requirements
                                    </h6>

                                    <ul class="small text-muted mb-0 ps-3">

                                        <li class="mb-1">
                                            The official template should be
                                            provided by the barangay.
                                        </li>

                                        <li class="mb-1">
                                            The file must be in
                                            <strong>.docx</strong> format.
                                        </li>

                                        <li class="mb-1">
                                            Keep the official document layout,
                                            wording, header, footer, and
                                            signature areas intact.
                                        </li>

                                        <li class="mb-1">
                                            Use placeholders for information
                                            that the system will populate.
                                        </li>

                                        <li>
                                            The uploaded template will become
                                            the active template for its
                                            selected document type.
                                        </li>

                                    </ul>

                                </div>

                            </div>

                        </div>


                        {{-- ====================================================
                             PLACEHOLDER REFERENCE
                             ==================================================== --}}

                        <div class="card bg-light border mb-4">

                            <div class="card-body">

                                <h6 class="fw-bold mb-3">

                                    <i class="bi bi-braces me-2"></i>

                                    Available Placeholders

                                </h6>

                                <p class="small text-muted mb-3">
                                    When preparing the DOCX template, use the
                                    following placeholders where information
                                    should be inserted automatically:
                                </p>

                                <div class="row g-2">

                                    <div class="col-12 col-md-6">

                                        <code>
                                            ${resident_name}
                                        </code>

                                        <small class="text-muted d-block">
                                            Complete resident name
                                        </small>

                                    </div>

                                    <div class="col-12 col-md-6">

                                        <code>
                                            ${first_name}
                                        </code>

                                        <small class="text-muted d-block">
                                            Resident first name
                                        </small>

                                    </div>

                                    <div class="col-12 col-md-6">

                                        <code>
                                            ${middle_name}
                                        </code>

                                        <small class="text-muted d-block">
                                            Resident middle name
                                        </small>

                                    </div>

                                    <div class="col-12 col-md-6">

                                        <code>
                                            ${last_name}
                                        </code>

                                        <small class="text-muted d-block">
                                            Resident last name
                                        </small>

                                    </div>

                                    <div class="col-12 col-md-6">

                                        <code>
                                            ${suffix}
                                        </code>

                                        <small class="text-muted d-block">
                                            Name suffix
                                        </small>

                                    </div>

                                    <div class="col-12 col-md-6">

                                        <code>
                                            ${civil_status}
                                        </code>

                                        <small class="text-muted d-block">
                                            Civil status
                                        </small>

                                    </div>

                                    <div class="col-12 col-md-6">

                                        <code>
                                            ${purpose}
                                        </code>

                                        <small class="text-muted d-block">
                                            Document request purpose
                                        </small>

                                    </div>

                                    <div class="col-12 col-md-6">

                                        <code>
                                            ${remarks}
                                        </code>

                                        <small class="text-muted d-block">
                                            Document request remarks
                                        </small>

                                    </div>

                                    <div class="col-12 col-md-6">

                                        <code>
                                            ${request_code}
                                        </code>

                                        <small class="text-muted d-block">
                                            Document request code
                                        </small>

                                    </div>

                                    <div class="col-12 col-md-6">

                                        <code>
                                            ${date_requested}
                                        </code>

                                        <small class="text-muted d-block">
                                            Date the request was created
                                        </small>

                                    </div>

                                    <div class="col-12 col-md-6">

                                        <code>
                                            ${date_issued}
                                        </code>

                                        <small class="text-muted d-block">
                                            Date the document is generated
                                        </small>

                                    </div>

                                    <div class="col-12 col-md-6">

                                        <code>
                                            ${document_type}
                                        </code>

                                        <small class="text-muted d-block">
                                            Document type
                                        </small>

                                    </div>

                                </div>

                            </div>

                        </div>


                        {{-- ====================================================
                             WARNING
                             ==================================================== --}}

                        <div class="alert alert-warning border-0">

                            <div class="d-flex align-items-start gap-3">

                                <i class="bi bi-exclamation-triangle fs-5"></i>

                                <div class="small">

                                    <strong>Important:</strong>

                                    Uploading a new template for the same
                                    document type will automatically deactivate
                                    the currently active template for that
                                    document type.

                                </div>

                            </div>

                        </div>


                        {{-- ====================================================
                             ACTION BUTTONS
                             ==================================================== --}}

                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">

                            <a
                                href="{{ route('document-templates.index') }}"
                                class="btn btn-outline-secondary"
                            >
                                <i class="bi bi-x-circle me-1"></i>
                                Cancel
                            </a>

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                <i class="bi bi-cloud-arrow-up me-1"></i>
                                Upload Official Template
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection