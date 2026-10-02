@extends('layouts.app')

@section('title', 'Official Document Templates | e-Poblacion')

@section('content')

<div class="container-fluid py-4">

    {{-- ================================================================
         PAGE HEADER
         ================================================================ --}}

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">

        <div>
            <h1 class="h3 fw-bold mb-1">
                Official Document Templates
            </h1>

            <p class="text-muted mb-0">
                Manage the official barangay document templates used by the
                e-Poblacion Record Management System.
            </p>
        </div>

        @if(auth()->user()->role === 'captain' || auth()->user()->role === 'secretary')
            <a
                href="{{ route('document-templates.create') }}"
                class="btn btn-primary"
            >
                <i class="bi bi-upload me-1"></i>
                Upload Official Template
            </a>
        @endif

    </div>


    {{-- ================================================================
         INFORMATION NOTICE
         ================================================================ --}}

    <div class="alert alert-info border-0 shadow-sm mb-4">

        <div class="d-flex align-items-start gap-3">

            <div class="fs-4">
                <i class="bi bi-file-earmark-text"></i>
            </div>

            <div>
                <h6 class="fw-bold mb-1">
                    Official Template Registry
                </h6>

                <p class="mb-0">
                    Official barangay document templates are stored in
                    private application storage and are used as the source
                    templates when generating documents from approved
                    document requests.
                </p>
            </div>

        </div>

    </div>


    {{-- ================================================================
         FLASH MESSAGES
         ================================================================ --}}

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show" role="alert">

            <i class="bi bi-check-circle me-2"></i>

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>

        </div>

    @endif


    @if(session('info'))

        <div class="alert alert-info alert-dismissible fade show" role="alert">

            <i class="bi bi-info-circle me-2"></i>

            {{ session('info') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>

        </div>

    @endif


    {{-- ================================================================
         VALIDATION ERRORS
         ================================================================ --}}

    @if($errors->any())

        <div class="alert alert-danger">

            <div class="fw-bold mb-2">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Please correct the following:
            </div>

            <ul class="mb-0">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- ================================================================
         TEMPLATE TABLE
         ================================================================ --}}

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-transparent border-0 py-3">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">

                <div>
                    <h5 class="fw-bold mb-1">
                        Registered Templates
                    </h5>

                    <small class="text-muted">
                        Official templates currently registered in the system.
                    </small>
                </div>

                <span class="badge bg-secondary">
                    {{ $templates->count() }}
                    {{ Str::plural('Template', $templates->count()) }}
                </span>

            </div>

        </div>


        <div class="card-body p-0">

            @if($templates->isEmpty())

                {{-- ====================================================
                     EMPTY STATE
                     ==================================================== --}}

                <div class="text-center py-5 px-4">

                    <div class="mb-3">

                        <i
                            class="bi bi-file-earmark-x"
                            style="font-size: 3rem;"
                        ></i>

                    </div>

                    <h5 class="fw-bold">
                        No Official Templates Registered
                    </h5>

                    <p class="text-muted mb-4">
                        No official barangay document templates have been
                        uploaded yet.
                    </p>

                    @if(auth()->user()->role === 'captain' || auth()->user()->role === 'secretary')

                        <a
                            href="{{ route('document-templates.create') }}"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-upload me-1"></i>
                            Upload First Template
                        </a>

                    @endif

                </div>

            @else

                {{-- ====================================================
                     TEMPLATE TABLE
                     ==================================================== --}}

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th class="px-4">
                                    Document Type
                                </th>

                                <th>
                                    Template Name
                                </th>

                                <th>
                                    File Type
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Last Updated
                                </th>

                                <th class="text-end px-4">
                                    Action
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($templates as $template)

                                <tr>

                                    {{-- Document Type --}}

                                    <td class="px-4">

                                        <div class="fw-semibold">
                                            {{ $template->document_type }}
                                        </div>

                                    </td>


                                    {{-- Template Name --}}

                                    <td>

                                        <div class="d-flex align-items-center gap-2">

                                            <i class="bi bi-file-earmark-word fs-5"></i>

                                            <span>
                                                {{ $template->template_name }}
                                            </span>

                                        </div>

                                    </td>


                                    {{-- File Type --}}

                                    <td>

                                        <span class="badge text-bg-secondary text-uppercase">
                                            {{ $template->file_type }}
                                        </span>

                                    </td>


                                    {{-- Status --}}

                                    <td>

                                        @if($template->is_active)

                                            <span class="badge text-bg-success">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Active
                                            </span>

                                        @else

                                            <span class="badge text-bg-secondary">
                                                <i class="bi bi-dash-circle me-1"></i>
                                                Inactive
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Last Updated --}}

                                    <td>

                                        <span class="text-muted">

                                            {{ optional($template->updated_at)->format('M d, Y h:i A') }}

                                        </span>

                                    </td>


                                    {{-- Actions --}}

                                    <td class="text-end px-4">

                                        @if(
                                            $template->is_active &&
                                            (
                                                auth()->user()->role === 'captain' ||
                                                auth()->user()->role === 'secretary'
                                            )
                                        )

                                            <form
                                                action="{{ route(
                                                    'document-templates.deactivate',
                                                    $template
                                                ) }}"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm(
                                                    'Deactivate this official document template?'
                                                );"
                                            >

                                                @csrf

                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Deactivate Template"
                                                >
                                                    <i class="bi bi-pause-circle me-1"></i>
                                                    Deactivate
                                                </button>

                                            </form>

                                        @elseif(!$template->is_active)

                                            <span class="text-muted small">
                                                No action
                                            </span>

                                        @else

                                            <span class="text-muted small">
                                                Active
                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @endif

        </div>

    </div>


    {{-- ================================================================
         TEMPLATE MANAGEMENT NOTE
         ================================================================ --}}

    <div class="card border-0 shadow-sm mt-4">

        <div class="card-body">

            <div class="d-flex align-items-start gap-3">

                <div class="fs-4">
                    <i class="bi bi-shield-check"></i>
                </div>

                <div>

                    <h6 class="fw-bold mb-1">
                        Template Management Policy
                    </h6>

                    <p class="text-muted mb-0">

                        Only the Barangay Captain and Secretary can upload
                        or deactivate official document templates. The
                        registered templates are stored privately and are
                        separate from individual document requests.

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection