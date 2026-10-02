<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        | RELIEF ASSISTANCE OPERATIONS
        |--------------------------------------------------------------------------
        |
        | Add the unit of measurement for received assistance.
        |
        | Examples:
        | 100 Family Packs
        | 50 Sacks
        | 200 Kits
        |
        */

        if (
            Schema::hasTable('relief_assistance_operations')
            && ! Schema::hasColumn(
                'relief_assistance_operations',
                'quantity_unit'
            )
        ) {
            Schema::table(
                'relief_assistance_operations',
                function (Blueprint $table) {
                    $table->string('quantity_unit')
                        ->nullable()
                        ->after('quantity_received');
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | RELIEF VOUCHERS
        |--------------------------------------------------------------------------
        |
        | The previous voucher table was designed around
        | DisasterAssessment.
        |
        | The new design makes the voucher part of a general
        | Relief Assistance Operation.
        |
        */

        if (! Schema::hasTable('relief_vouchers')) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Remove Old Foreign Keys
        |--------------------------------------------------------------------------
        |
        | These belonged to the previous voucher workflow.
        |
        */

        $oldForeignKeys = [
            'disaster_assessment_id',
            'program_id',
            'requirements_verified_by',
            'qr_verified_by',
            'released_by',
            'processed_by_staff_id',
        ];

        foreach ($oldForeignKeys as $column) {
            if (
                Schema::hasColumn(
                    'relief_vouchers',
                    $column
                )
            ) {
                try {
                    Schema::table(
                        'relief_vouchers',
                        function (Blueprint $table) use ($column) {
                            $table->dropForeign([$column]);
                        }
                    );
                } catch (\Throwable $exception) {
                    /*
                    |--------------------------------------------------------------------------
                    | The foreign key may not exist in an older database
                    | version. The column will still be removed below.
                    |--------------------------------------------------------------------------
                    */
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Remove Old Voucher Columns
        |--------------------------------------------------------------------------
        |
        | These fields belong to the previous DisasterAssessment-based
        | voucher workflow and are no longer used.
        |
        */

        $oldColumns = [
            'disaster_assessment_id',
            'program_id',
            'purok_name',
            'requirements_verified',
            'is_id_verified',
            'is_proof_verified',
            'physical_file_ref',
            'processed_by_staff_id',
            'eligibility_basis',
            'requirements_status',
            'requirements_verified_at',
            'requirements_verified_by',
            'requirements_notes',
            'verification_status',
            'verification_notes',
        ];

        $columnsToDrop = [];

        foreach ($oldColumns as $column) {
            if (
                Schema::hasColumn(
                    'relief_vouchers',
                    $column
                )
            ) {
                $columnsToDrop[] = $column;
            }
        }

        if (! empty($columnsToDrop)) {
            Schema::table(
                'relief_vouchers',
                function (Blueprint $table) use ($columnsToDrop) {
                    $table->dropColumn($columnsToDrop);
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Add Beneficiary Reference
        |--------------------------------------------------------------------------
        |
        | Every voucher belongs to a specific beneficiary record
        | from the current Relief Assistance Operation.
        |
        */

        if (
            ! Schema::hasColumn(
                'relief_vouchers',
                'beneficiary_id'
            )
        ) {
            Schema::table(
                'relief_vouchers',
                function (Blueprint $table) {
                    $table->foreignId('beneficiary_id')
                        ->nullable()
                        ->after('operation_id')
                        ->constrained(
                            'relief_operation_beneficiaries'
                        )
                        ->nullOnDelete();
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Voucher Assistance Quantity
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | Voucher = RV-P1-0001
        | Assistance = Rice
        | Quantity = 1
        |
        */

        if (
            ! Schema::hasColumn(
                'relief_vouchers',
                'allocated_quantity'
            )
        ) {
            Schema::table(
                'relief_vouchers',
                function (Blueprint $table) {
                    $table->unsignedInteger('allocated_quantity')
                        ->nullable()
                        ->after('assistance_type');
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Release / Distribution Fields
        |--------------------------------------------------------------------------
        |
        | Keep these fields because the voucher needs to record
        | where and when the authorized assistance is distributed.
        |
        */

        if (
            ! Schema::hasColumn(
                'relief_vouchers',
                'release_date'
            )
        ) {
            Schema::table(
                'relief_vouchers',
                function (Blueprint $table) {
                    $table->date('release_date')
                        ->nullable()
                        ->after('distribution_schedule');
                }
            );
        }

        if (
            ! Schema::hasColumn(
                'relief_vouchers',
                'release_time'
            )
        ) {
            Schema::table(
                'relief_vouchers',
                function (Blueprint $table) {
                    $table->time('release_time')
                        ->nullable()
                        ->after('release_date');
                }
            );
        }

        if (
            ! Schema::hasColumn(
                'relief_vouchers',
                'release_location'
            )
        ) {
            Schema::table(
                'relief_vouchers',
                function (Blueprint $table) {
                    $table->string('release_location')
                        ->nullable()
                        ->after('release_time');
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | QR / Distribution User References
        |--------------------------------------------------------------------------
        */

        if (
            ! Schema::hasColumn(
                'relief_vouchers',
                'qr_verified_by'
            )
        ) {
            Schema::table(
                'relief_vouchers',
                function (Blueprint $table) {
                    $table->foreignId('qr_verified_by')
                        ->nullable()
                        ->after('qr_verified_at')
                        ->constrained('users')
                        ->nullOnDelete();
                }
            );
        }

        if (
            ! Schema::hasColumn(
                'relief_vouchers',
                'released_by'
            )
        ) {
            Schema::table(
                'relief_vouchers',
                function (Blueprint $table) {
                    $table->foreignId('released_by')
                        ->nullable()
                        ->after('released_at')
                        ->constrained('users')
                        ->nullOnDelete();
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Voucher Status
        |--------------------------------------------------------------------------
        |
        | New workflow:
        |
        | pending
        | generated
        | issued
        | distributed
        | claimed
        | cancelled
        |
        */

        DB::statement(
            "ALTER TABLE relief_vouchers
             MODIFY status ENUM(
                'pending',
                'generated',
                'issued',
                'distributed',
                'claimed',
                'cancelled'
             ) NOT NULL DEFAULT 'pending'"
        );

        /*
        |--------------------------------------------------------------------------
        | Helpful Index
        |--------------------------------------------------------------------------
        */

        try {
            Schema::table(
                'relief_vouchers',
                function (Blueprint $table) {
                    $table->index(
                        'beneficiary_id',
                        'relief_vouchers_beneficiary_id_index'
                    );
                }
            );
        } catch (\Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Index may already exist.
            |--------------------------------------------------------------------------
            */
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Remove Added Columns
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('relief_vouchers')) {
            /*
            | Drop foreign keys first.
            */

            foreach ([
                'beneficiary_id',
                'qr_verified_by',
                'released_by',
            ] as $column) {
                if (
                    Schema::hasColumn(
                        'relief_vouchers',
                        $column
                    )
                ) {
                    try {
                        Schema::table(
                            'relief_vouchers',
                            function (Blueprint $table) use ($column) {
                                $table->dropForeign([$column]);
                            }
                        );
                    } catch (\Throwable $exception) {
                        //
                    }
                }
            }

            $columns = [
                'beneficiary_id',
                'allocated_quantity',
                'release_date',
                'release_time',
                'release_location',
                'qr_verified_by',
                'released_by',
            ];

            $columnsToDrop = [];

            foreach ($columns as $column) {
                if (
                    Schema::hasColumn(
                        'relief_vouchers',
                        $column
                    )
                ) {
                    $columnsToDrop[] = $column;
                }
            }

            if (! empty($columnsToDrop)) {
                Schema::table(
                    'relief_vouchers',
                    function (Blueprint $table) use ($columnsToDrop) {
                        $table->dropColumn($columnsToDrop);
                    }
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Remove Quantity Unit
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable('relief_assistance_operations')
            && Schema::hasColumn(
                'relief_assistance_operations',
                'quantity_unit'
            )
        ) {
            Schema::table(
                'relief_assistance_operations',
                function (Blueprint $table) {
                    $table->dropColumn('quantity_unit');
                }
            );
        }
    }
};