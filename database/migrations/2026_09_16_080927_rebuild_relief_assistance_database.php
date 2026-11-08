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
        */

        if (! Schema::hasTable('relief_vouchers')) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | REMOVE OLD FOREIGN KEYS
        |--------------------------------------------------------------------------
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
                Schema::hasColumn('relief_vouchers', $column)
            ) {
                try {
                    Schema::table(
                        'relief_vouchers',
                        function (Blueprint $table) use ($column) {
                            $table->dropForeign([$column]);
                        }
                    );
                } catch (\Throwable $exception) {
                    // Continue if the legacy foreign key does not exist.
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | REMOVE OBSOLETE VOUCHER COLUMNS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | Keep purok_name and requirements_verified.
        | Both are still used by the current application.
        |
        */

        $oldColumns = [
            'disaster_assessment_id',
            'program_id',
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
                Schema::hasColumn('relief_vouchers', $column)
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
        | BENEFICIARY REFERENCE
        |--------------------------------------------------------------------------
        */

        if (
            ! Schema::hasColumn(
                'relief_vouchers',
                'beneficiary_id'
            )
        ) {
            if (
                ! Schema::hasColumn(
                    'relief_vouchers',
                    'operation_id'
                )
            ) {
                throw new \RuntimeException(
                    'Cannot add beneficiary_id: operation_id is missing.'
                );
            }

            if (
                ! Schema::hasTable(
                    'relief_operation_beneficiaries'
                )
            ) {
                throw new \RuntimeException(
                    'Cannot add beneficiary_id: relief_operation_beneficiaries table is missing.'
                );
            }

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
        | ALLOCATED QUANTITY
        |--------------------------------------------------------------------------
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
        | RELEASE / DISTRIBUTION FIELDS
        |--------------------------------------------------------------------------
        */

        if (
            ! Schema::hasColumn(
                'relief_vouchers',
                'release_date'
            )
        ) {
            if (
                ! Schema::hasColumn(
                    'relief_vouchers',
                    'distribution_schedule'
                )
            ) {
                throw new \RuntimeException(
                    'Cannot add release_date: distribution_schedule is missing.'
                );
            }

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
                    $table->string('release_location', 255)
                        ->nullable()
                        ->after('release_time');
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | QR / DISTRIBUTION USER REFERENCES
        |--------------------------------------------------------------------------
        */

        if (
            ! Schema::hasColumn(
                'relief_vouchers',
                'qr_verified_by'
            )
        ) {
            if (
                ! Schema::hasColumn(
                    'relief_vouchers',
                    'qr_verified_at'
                )
            ) {
                throw new \RuntimeException(
                    'Cannot add qr_verified_by: qr_verified_at is missing.'
                );
            }

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
            if (
                ! Schema::hasColumn(
                    'relief_vouchers',
                    'released_at'
                )
            ) {
                throw new \RuntimeException(
                    'Cannot add released_by: released_at is missing.'
                );
            }

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
        | VOUCHER STATUS
        |--------------------------------------------------------------------------
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
        | BENEFICIARY INDEX
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
            // Index may already exist.
        }
    }

    /**
     * Reverse the migrations.
     *
     * This migration is not fully reversible because its up()
     * operation removes legacy columns and changes the status ENUM.
     */
    public function down(): void
    {
        throw new \LogicException(
            'This migration cannot be safely rolled back automatically. '
            . 'Restore the database from a verified backup instead.'
        );
    }
};
