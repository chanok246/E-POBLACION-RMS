<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reports_complaints', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Primary Key
            |--------------------------------------------------------------------------
            */

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Record Identification
            |--------------------------------------------------------------------------
            |
            | Example:
            | RC-2026-0001
            |
            */

            $table->string('record_code', 30)->unique();


            /*
            |--------------------------------------------------------------------------
            | Report Type
            |--------------------------------------------------------------------------
            |
            | community
            | person
            |
            */

            $table->string('report_type', 30)->index();


            /*
            |--------------------------------------------------------------------------
            | Category
            |--------------------------------------------------------------------------
            */

            $table->string('category', 150)->index();


            /*
            |--------------------------------------------------------------------------
            | Title
            |--------------------------------------------------------------------------
            */

            $table->string('title', 255);


            /*
            |--------------------------------------------------------------------------
            | Complainant
            |--------------------------------------------------------------------------
            */

            $table->foreignId('complainant_id')
                ->constrained('residents')
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Subject / Respondent
            |--------------------------------------------------------------------------
            |
            | Used for person-related complaints.
            |
            */

            $table->foreignId('subject_resident_id')
                ->nullable()
                ->constrained('residents')
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Relationship To Complainant
            |--------------------------------------------------------------------------
            */

            $table->string('relationship_to_complainant', 100)
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Date And Time Reported
            |--------------------------------------------------------------------------
            */

            $table->date('date_reported')
                ->nullable();

            $table->time('time_reported')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Incident Date And Time
            |--------------------------------------------------------------------------
            */

            $table->date('incident_date')
                ->nullable();

            $table->time('incident_time')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Location
            |--------------------------------------------------------------------------
            */

            $table->string('location', 255)
                ->nullable();

            $table->string('purok', 100)
                ->nullable()
                ->index();

            $table->string('barangay', 100)
                ->nullable();

            $table->string('municipality', 100)
                ->nullable();

            $table->string('province', 100)
                ->nullable();

            $table->string('region', 150)
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Complaint / Report Description
            |--------------------------------------------------------------------------
            */

            $table->text('description');


            /*
            |--------------------------------------------------------------------------
            | Workflow Status
            |--------------------------------------------------------------------------
            */

            $table->string('status', 60)
                ->default('Filed')
                ->index();


            /*
            |--------------------------------------------------------------------------
            | Confidentiality
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_confidential')
                ->default(false)
                ->index();


            /*
            |--------------------------------------------------------------------------
            | Assigned Official / Staff
            |--------------------------------------------------------------------------
            */

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Administrative Remarks
            |--------------------------------------------------------------------------
            */

            $table->text('remarks')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Resolution
            |--------------------------------------------------------------------------
            */

            $table->text('resolution')
                ->nullable();

            $table->timestamp('resolved_at')
                ->nullable();

            $table->timestamp('closed_at')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Summons Information
            |--------------------------------------------------------------------------
            |
            | Used primarily for person-related complaints.
            |
            | IMPORTANT:
            | These names MUST match:
            |
            | ReportComplaint.php
            | ReportComplaintController.php
            |
            */

            $table->boolean('summons_requested')
                ->default(false);

            $table->timestamp('summons_printed_at')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Hearing Information
            |--------------------------------------------------------------------------
            */

            $table->date('hearing_date')
                ->nullable();

            $table->time('hearing_time')
                ->nullable();

            $table->string('hearing_location', 255)
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Filing / Creation Information
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Archive
            |--------------------------------------------------------------------------
            |
            | Records are archived instead of permanently deleted.
            |
            */

            $table->boolean('is_archived')
                ->default(false)
                ->index();

            $table->timestamp('archived_at')
                ->nullable();

            $table->foreignId('archived_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | Useful Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'report_type',
                'status',
            ]);

            $table->index([
                'report_type',
                'category',
            ]);

            $table->index([
                'incident_date',
            ]);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports_complaints');
    }
};
