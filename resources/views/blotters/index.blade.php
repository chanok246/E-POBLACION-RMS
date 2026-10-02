@extends('layouts.app')

@section('title', 'Blotter Records | e-Poblacion')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="mb-4">

        <div class="breadcrumb-text">
            E-POBLACION / BLOTTER RECORDS
        </div>

        <h1 class="page-title">
            Blotter Records
        </h1>

        <p class="page-subtitle">
            Manage civil incidents, complaints, and barangay blotter records.
        </p>

    </div>


    {{-- ACTION BAR --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h4 class="mb-1">
                Incident Records
            </h4>

            <p class="text-muted mb-0">
                View and manage recorded barangay incidents.
            </p>
        </div>

        <button type="button" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i>
            New Blotter Record
        </button>

    </div>


    {{-- TABLE --}}
    <div class="card">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>
                            <th>Blotter No.</th>
                            <th>Complainant</th>
                            <th>Respondent</th>
                            <th>Incident Type</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>

                    </thead>

                    <tbody>

                        <tr>

                            <td colspan="7" class="text-center py-5">

                                <i class="bi bi-journal-x fs-1"></i>

                                <p class="mt-3 mb-1">
                                    No blotter records yet.
                                </p>

                                <small class="text-muted">
                                    Blotter records will appear here once
                                    they are added.
                                </small>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- BACK BUTTON --}}

    <div class="mt-4">

        <a href="{{ route('dashboard') }}" class="btn btn-secondary">

            <i class="bi bi-arrow-left"></i>

            Back to Dashboard

        </a>

    </div>

</div>

@endsection
