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
        /*
        |--------------------------------------------------------------------------
        | ADD FINAL DISASTER ASSESSMENT FIELDS
        |--------------------------------------------------------------------------
        */

        Schema::table('disaster_assessments', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Assessment Identification
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('disaster_assessments', 'assessment_code')) {
                $table->string('assessment_code', 100)
                    ->nullable()
                    ->unique()
                    ->after('household_id');
            }

            if (!Schema::hasColumn('disaster_assessments', 'assessment_date')) {
                $table->date('assessment_date')
                    ->nullable()
                    ->after('assessment_code');
            }


            /*
            |--------------------------------------------------------------------------
            | Assessor
            |--------------------------------------------------------------------------
            |
            | The original migration already contains assessed_by.
            | We keep that field and do not recreate it.
            |
            */


            /*
            |--------------------------------------------------------------------------
            | Damage Information
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('disaster_assessments', 'damage_type')) {
                $table->string('damage_type', 100)
                    ->nullable()
                    ->after('damage_level');
            }

            if (!Schema::hasColumn('disaster_assessments', 'damage_description')) {
                $table->text('damage_description')
                    ->nullable()
                    ->after('damage_type');
            }


            /*
            |--------------------------------------------------------------------------
            | HOUSEHOLD IMPACT
            |--------------------------------------------------------------------------
            |
            | Required by DisasterAssessmentController.
            |
            */

            if (!Schema::hasColumn('disaster_assessments', 'household_affected')) {
                $table->boolean('household_affected')
                    ->default(false)
                    ->after('damage_description');
            }


            /*
            |--------------------------------------------------------------------------
            | PROPERTY DAMAGE
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('disaster_assessments', 'property_damaged')) {
                $table->boolean('property_damaged')
                    ->default(false)
                    ->after('household_affected');
            }

            if (!Schema::hasColumn('disaster_assessments', 'property_damage_description')) {
                $table->text('property_damage_description')
                    ->nullable()
                    ->after('property_damaged');
            }

            if (!Schema::hasColumn('disaster_assessments', 'estimated_damage_cost')) {
                $table->decimal('estimated_damage_cost', 15, 2)
                    ->nullable()
                    ->after('property_damage_description');
            }


            /*
            |--------------------------------------------------------------------------
            | ASSISTANCE INFORMATION
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('disaster_assessments', 'assistance_needed')) {
                $table->boolean('assistance_needed')
                    ->default(false)
                    ->after('estimated_damage_cost');
            }

            if (!Schema::hasColumn('disaster_assessments', 'assistance_description')) {
                $table->text('assistance_description')
                    ->nullable()
                    ->after('assistance_needed');
            }


            /*
            |--------------------------------------------------------------------------
            | VERIFICATION NOTES
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('disaster_assessments', 'verification_notes')) {
                $table->text('verification_notes')
                    ->nullable()
                    ->after('verified_at');
            }


            /*
            |--------------------------------------------------------------------------
            | VOUCHER ELIGIBILITY REASON
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('disaster_assessments', 'voucher_eligibility_reason')) {
                $table->text('voucher_eligibility_reason')
                    ->nullable()
                    ->after('voucher_eligible');
            }


            /*
            |--------------------------------------------------------------------------
            | LOCATION
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('disaster_assessments', 'purok')) {
                $table->string('purok', 100)
                    ->nullable()
                    ->after('voucher_eligibility_reason');
            }

            if (!Schema::hasColumn('disaster_assessments', 'barangay')) {
                $table->string('barangay', 150)
                    ->nullable()
                    ->after('purok');
            }

            if (!Schema::hasColumn('disaster_assessments', 'municipality')) {
                $table->string('municipality', 150)
                    ->nullable()
                    ->after('barangay');
            }

            if (!Schema::hasColumn('disaster_assessments', 'province')) {
                $table->string('province', 150)
                    ->nullable()
                    ->after('municipality');
            }


            /*
            |--------------------------------------------------------------------------
            | GPS COORDINATES
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('disaster_assessments', 'latitude')) {
                $table->decimal('latitude', 10, 7)
                    ->nullable()
                    ->after('province');
            }

            if (!Schema::hasColumn('disaster_assessments', 'longitude')) {
                $table->decimal('longitude', 10, 7)
                    ->nullable()
                    ->after('latitude');
            }


            /*
            |--------------------------------------------------------------------------
            | ASSESSMENT STATUS
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('disaster_assessments', 'status')) {
                $table->enum('status', [
                    'Active',
                    'Archived',
                ])
                    ->default('Active')
                    ->after('longitude');
            }
        });


        /*
        |--------------------------------------------------------------------------
        | REMOVE OBSOLETE FIELDS
        |--------------------------------------------------------------------------
        |
        | These fields belonged to the previous assessment design.
        |
        */

        Schema::table('disaster_assessments', function (Blueprint $table) {

            $columnsToDrop = [
                'estimated_damage',
                'house_damaged',
                'house_uninhabitable',
                'livelihood_affected',
                'livelihood_details',
                'has_pwd',
                'has_senior_citizen',
                'has_children',
                'has_dependent_member',
                'evacuated',
                'evacuation_location',
                'evidence_photo',
                'potential_eligibility',
                'eligibility_reason',
                'verification_remarks',
            ];

            foreach ($columnsToDrop as $column) {

                if (Schema::hasColumn(
                    'disaster_assessments',
                    $column
                )) {
                    $table->dropColumn($column);
                }
            }
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | REMOVE FINAL FLOW FIELDS
        |--------------------------------------------------------------------------
        */

        Schema::table('disaster_assessments', function (Blueprint $table) {

            $columnsToDrop = [
                'assessment_code',
                'assessment_date',
                'damage_type',
                'damage_description',
                'household_affected',
                'property_damaged',
                'property_damage_description',
                'estimated_damage_cost',
                'assistance_needed',
                'assistance_description',
                'verification_notes',
                'voucher_eligibility_reason',
                'purok',
                'barangay',
                'municipality',
                'province',
                'latitude',
                'longitude',
                'status',
            ];

            foreach ($columnsToDrop as $column) {

                if (Schema::hasColumn(
                    'disaster_assessments',
                    $column
                )) {
                    $table->dropColumn($column);
                }
            }
        });


        /*
        |--------------------------------------------------------------------------
        | RESTORE PREVIOUS FIELDS
        |--------------------------------------------------------------------------
        */

        Schema::table('disaster_assessments', function (Blueprint $table) {

            if (!Schema::hasColumn(
                'disaster_assessments',
                'estimated_damage'
            )) {
                $table->decimal(
                    'estimated_damage',
                    15,
                    2
                )->nullable();
            }

            if (!Schema::hasColumn(
                'disaster_assessments',
                'house_damaged'
            )) {
                $table->boolean(
                    'house_damaged'
                )->default(false);
            }

            if (!Schema::hasColumn(
                'disaster_assessments',
                'house_uninhabitable'
            )) {
                $table->boolean(
                    'house_uninhabitable'
                )->default(false);
            }

            if (!Schema::hasColumn(
                'disaster_assessments',
                'livelihood_affected'
            )) {
                $table->boolean(
                    'livelihood_affected'
                )->default(false);
            }

            if (!Schema::hasColumn(
                'disaster_assessments',
                'livelihood_details'
            )) {
                $table->text(
                    'livelihood_details'
                )->nullable();
            }

            if (!Schema::hasColumn(
                'disaster_assessments',
                'has_pwd'
            )) {
                $table->boolean(
                    'has_pwd'
                )->default(false);
            }

            if (!Schema::hasColumn(
                'disaster_assessments',
                'has_senior_citizen'
            )) {
                $table->boolean(
                    'has_senior_citizen'
                )->default(false);
            }

            if (!Schema::hasColumn(
                'disaster_assessments',
                'has_children'
            )) {
                $table->boolean(
                    'has_children'
                )->default(false);
            }

            if (!Schema::hasColumn(
                'disaster_assessments',
                'has_dependent_member'
            )) {
                $table->boolean(
                    'has_dependent_member'
                )->default(false);
            }

            if (!Schema::hasColumn(
                'disaster_assessments',
                'evacuated'
            )) {
                $table->boolean(
                    'evacuated'
                )->default(false);
            }

            if (!Schema::hasColumn(
                'disaster_assessments',
                'evacuation_location'
            )) {
                $table->string(
                    'evacuation_location'
                )->nullable();
            }

            if (!Schema::hasColumn(
                'disaster_assessments',
                'evidence_photo'
            )) {
                $table->string(
                    'evidence_photo'
                )->nullable();
            }

            if (!Schema::hasColumn(
                'disaster_assessments',
                'potential_eligibility'
            )) {
                $table->enum(
                    'potential_eligibility',
                    [
                        'Low',
                        'Moderate',
                        'High',
                    ]
                )->default('Low');
            }

            if (!Schema::hasColumn(
                'disaster_assessments',
                'eligibility_reason'
            )) {
                $table->text(
                    'eligibility_reason'
                )->nullable();
            }

            if (!Schema::hasColumn(
                'disaster_assessments',
                'verification_remarks'
            )) {
                $table->text(
                    'verification_remarks'
                )->nullable();
            }
        });
    }
};