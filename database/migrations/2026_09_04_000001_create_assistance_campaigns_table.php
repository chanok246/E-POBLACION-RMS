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
        Schema::create('assistance_campaigns', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Campaign Identification
            |--------------------------------------------------------------------------
            */

            $table->string('campaign_code')->unique();

            $table->string('name');

            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Assistance Type
            |--------------------------------------------------------------------------
            |
            | Examples:
            | - Disaster Relief
            | - Senior Citizen Pension
            | - Medical Assistance
            | - Educational Assistance
            | - Farmer Subsidy
            | - Cash Assistance
            |
            */

            $table->string('assistance_type');

            /*
            |--------------------------------------------------------------------------
            | Campaign Schedule
            |--------------------------------------------------------------------------
            */

            $table->date('start_date');

            $table->date('end_date')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Assistance Details
            |--------------------------------------------------------------------------
            */

            $table->decimal('assistance_amount', 12, 2)
                ->nullable();

            $table->text('assistance_package')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Campaign Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'draft',
                'active',
                'closed',
                'cancelled',
            ])->default('draft');

            /*
            |--------------------------------------------------------------------------
            | Optional Disaster Connection
            |--------------------------------------------------------------------------
            |
            | A campaign can be used for disaster assistance, but it does
            | not require a disaster event. This is what allows the module
            | to support non-disaster programs such as pensions and subsidies.
            |
            */

            $table->foreignId('disaster_event_id')
                ->nullable()
                ->constrained('disaster_events')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Barangay Information
            |--------------------------------------------------------------------------
            */

            $table->string('barangay')
                ->default('Barangay Poblacion');

            $table->string('municipality')
                ->default('Bani');

            $table->string('province')
                ->default('Pangasinan');

            /*
            |--------------------------------------------------------------------------
            | Audit Information
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
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('status');

            $table->index('assistance_type');

            $table->index('start_date');

            $table->index('end_date');

            $table->index('disaster_event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assistance_campaigns');
    }
};