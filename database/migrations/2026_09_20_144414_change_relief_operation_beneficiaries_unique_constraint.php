<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'relief_operation_beneficiaries',
            function (Blueprint $table) {

                /*
                 * Remove the old household-level unique constraint.
                 *
                 * OLD:
                 *
                 * operation_id + household_id
                 *
                 * This prevented multiple residents from the
                 * same household from becoming beneficiaries
                 * in the same relief operation.
                 */
                $table->dropUnique(
                    'relief_operation_beneficiaries_operation_id_household_id_unique'
                );

                /*
                 * Add the new resident-level unique constraint.
                 *
                 * NEW:
                 *
                 * operation_id + resident_id
                 *
                 * This allows multiple residents from the same
                 * household while preventing the same resident
                 * from being encoded twice in the same operation.
                 */
                $table->unique(
                    [
                        'operation_id',
                        'resident_id',
                    ],
                    'relief_operation_beneficiaries_operation_id_resident_id_unique'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'relief_operation_beneficiaries',
            function (Blueprint $table) {

                /*
                 * Remove the resident-level unique constraint.
                 */
                $table->dropUnique(
                    'relief_operation_beneficiaries_operation_id_resident_id_unique'
                );

                /*
                 * Restore the original household-level
                 * unique constraint.
                 */
                $table->unique(
                    [
                        'operation_id',
                        'household_id',
                    ],
                    'relief_operation_beneficiaries_operation_id_household_id_unique'
                );
            }
        );
    }
};
