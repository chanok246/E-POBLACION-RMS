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
        Schema::table('assistance_campaigns', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Relief Assistance Operation
            |--------------------------------------------------------------------------
            |
            | These fields allow the existing assistance campaign record to
            | function as a household-based mass relief operation.
            |
            | Examples:
            | - Post-Typhoon Food Pack Distribution
            | - Flood Relief Goods Distribution
            | - Barangay Food Assistance
            | - Agency-Sponsored Relief Distribution
            |
            */

            $table->string('purpose')
                ->nullable()
                ->after('description');

            /*
            |--------------------------------------------------------------------------
            | Assistance Source
            |--------------------------------------------------------------------------
            |
            | The barangay may receive assistance from agencies, organizations,
            | offices, or other authorized sources.
            |
            */

            $table->string('assistance_source')
                ->nullable()
                ->after('assistance_package');

            $table->text('source_reference')
                ->nullable()
                ->after('assistance_source');

            /*
            |--------------------------------------------------------------------------
            | Available Assistance
            |--------------------------------------------------------------------------
            |
            | This represents the assistance actually available for the
            | current distribution operation.
            |
            | This is NOT a barangay internal relief fund.
            |
            */

            $table->decimal('quantity_available', 12, 2)
                ->default(0)
                ->after('assistance_source');

            $table->string('quantity_unit')
                ->nullable()
                ->after('quantity_available');

            /*
            |--------------------------------------------------------------------------
            | Beneficiary / Allocation Tracking
            |--------------------------------------------------------------------------
            |
            | These values are operational counts used to monitor the
            | relationship between verified beneficiaries and available
            | assistance.
            |
            */

            $table->unsignedInteger('verified_beneficiary_count')
                ->default(0)
                ->after('quantity_unit');

            $table->unsignedInteger('allocated_beneficiary_count')
                ->default(0)
                ->after('verified_beneficiary_count');

            $table->unsignedInteger('shortage_count')
                ->default(0)
                ->after('allocated_beneficiary_count');

            /*
            |--------------------------------------------------------------------------
            | Allocation Basis
            |--------------------------------------------------------------------------
            |
            | The system records the authorized basis used by the barangay.
            | It does NOT automatically decide who should receive assistance.
            |
            */

            $table->string('allocation_basis')
                ->nullable()
                ->after('shortage_count');

            $table->text('allocation_notes')
                ->nullable()
                ->after('allocation_basis');

            /*
            |--------------------------------------------------------------------------
            | Operation Status
            |--------------------------------------------------------------------------
            |
            | The existing campaign status remains for the overall
            | operation lifecycle.
            |
            | These additional fields track the relief-distribution stage.
            |
            */

            $table->enum('relief_status', [
                'planning',
                'beneficiaries_verified',
                'assistance_awaiting',
                'assistance_received',
                'allocation_ready',
                'allocated',
                'voucher_generation',
                'distribution',
                'completed',
                'cancelled',
            ])->default('planning')
                ->after('status');

            /*
            |--------------------------------------------------------------------------
            | Important Dates
            |--------------------------------------------------------------------------
            */

            $table->date('assistance_received_date')
                ->nullable()
                ->after('relief_status');

            $table->date('allocation_date')
                ->nullable()
                ->after('assistance_received_date');

            $table->date('distribution_date')
                ->nullable()
                ->after('allocation_date');

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('relief_status');

            $table->index('assistance_source');

            $table->index('assistance_received_date');

            $table->index('allocation_date');

            $table->index('distribution_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assistance_campaigns', function (Blueprint $table) {

            $table->dropIndex([
                'assistance_campaigns_relief_status_index',
            ]);

            $table->dropIndex([
                'assistance_campaigns_assistance_source_index',
            ]);

            $table->dropIndex([
                'assistance_campaigns_assistance_received_date_index',
            ]);

            $table->dropIndex([
                'assistance_campaigns_allocation_date_index',
            ]);

            $table->dropIndex([
                'assistance_campaigns_distribution_date_index',
            ]);

            $table->dropColumn([
                'purpose',
                'assistance_source',
                'source_reference',
                'quantity_available',
                'quantity_unit',
                'verified_beneficiary_count',
                'allocated_beneficiary_count',
                'shortage_count',
                'allocation_basis',
                'allocation_notes',
                'relief_status',
                'assistance_received_date',
                'allocation_date',
                'distribution_date',
            ]);
        });
    }
};