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
        Schema::create('relief_assistance_operations', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | OPERATION IDENTITY
            |--------------------------------------------------------------------------
            */

            $table->string('operation_code')
                ->unique();

            $table->string('operation_name');

            $table->text('description')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | ASSISTANCE INFORMATION
            |--------------------------------------------------------------------------
            */

            $table->string('assistance_type');

            $table->text('assistance_description')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | OPTIONAL DISASTER REFERENCE
            |--------------------------------------------------------------------------
            |
            | Relief Assistance can operate independently.
            | A disaster event may be linked when applicable,
            | but it is NOT required.
            |
            */

            $table->foreignId('disaster_event_id')
                ->nullable()
                ->constrained('disaster_events')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | SOURCE / RECEIPT INFORMATION
            |--------------------------------------------------------------------------
            */

            $table->string('source_name')
                ->nullable();

            $table->string('source_reference')
                ->nullable();

            $table->date('assistance_received_date')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | ASSISTANCE QUANTITY
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('quantity_received')
                ->default(0);

            $table->string('quantity_unit')
                ->default('units');

            $table->unsignedInteger('quantity_allocated')
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | ALLOCATION INFORMATION
            |--------------------------------------------------------------------------
            */

            $table->string('allocation_basis')
                ->nullable();

            $table->text('allocation_notes')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | DISTRIBUTION SCHEDULE
            |--------------------------------------------------------------------------
            */

            $table->date('distribution_date')
                ->nullable();

            $table->time('distribution_time')
                ->nullable();

            $table->string('distribution_location')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | OPERATION STATUS
            |--------------------------------------------------------------------------
            |
            | draft
            | verification
            | ready
            | allocated
            | distribution
            | completed
            | cancelled
            |
            */

            $table->string('status')
                ->default('draft');

            /*
            |--------------------------------------------------------------------------
            | AUDIT USERS
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index('status');
            $table->index('assistance_type');
            $table->index('distribution_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relief_assistance_operations');
    }
};
