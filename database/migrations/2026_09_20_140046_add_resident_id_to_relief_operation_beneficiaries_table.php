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
        Schema::table(
            'relief_operation_beneficiaries',
            function (Blueprint $table) {
                /*
                 * A beneficiary may now be identified by a specific
                 * resident within the household.
                 *
                 * This allows multiple residents from the same
                 * household to become separate beneficiaries.
                 *
                 * Example:
                 * Household #001 has 3 student members.
                 * The system can select Student A, Student B,
                 * or Student C independently.
                 */
                $table->foreignId('resident_id')
                    ->nullable()
                    ->after('household_id')
                    ->constrained('residents')
                    ->nullOnDelete();

                /*
                 * Prevent the same resident from being encoded
                 * more than once in the same relief operation.
                 */
                $table->unique(
                    [
                        'operation_id',
                        'resident_id',
                    ],
                    'rob_operation_resident_unique'
                );
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(
            'relief_operation_beneficiaries',
            function (Blueprint $table) {
                /*
                 * Remove the unique constraint first.
                 */
                $table->dropUnique(
                    'rob_operation_resident_unique'
                );

                /*
                 * Then remove the foreign key and column.
                 */
                $table->dropForeign([
                    'resident_id',
                ]);

                $table->dropColumn(
                    'resident_id'
                );
            }
        );
    }
};
