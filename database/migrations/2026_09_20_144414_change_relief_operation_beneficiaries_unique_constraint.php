<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = 'relief_operation_beneficiaries';

        if (!Schema::hasTable($tableName)) {
            throw new RuntimeException(
                "Required table {$tableName} does not exist."
            );
        }

        $indexes = DB::select("SHOW INDEX FROM `{$tableName}`");

        $groupedIndexes = [];

        foreach ($indexes as $index) {
            $groupedIndexes[$index->Key_name]['unique'] =
                ((int) $index->Non_unique === 0);

            $groupedIndexes[$index->Key_name]['columns'][
                (int) $index->Seq_in_index
            ] = $index->Column_name;
        }

        $householdUniqueExists = false;
        $residentUniqueExists = false;

        foreach ($groupedIndexes as $name => $index) {
            $columns = array_values($index['columns']);

            if (
                $index['unique']
                && $columns === ['operation_id', 'household_id']
            ) {
                Schema::table($tableName, function (Blueprint $table) use ($name) {
                    $table->dropUnique($name);
                });

                $householdUniqueExists = true;
            }

            if (
                $index['unique']
                && $columns === ['operation_id', 'resident_id']
            ) {
                $residentUniqueExists = true;
            }
        }

        if (!Schema::hasColumn($tableName, 'resident_id')) {
            throw new RuntimeException(
                'Cannot enforce resident-level uniqueness: resident_id column is missing.'
            );
        }

        if (!$residentUniqueExists) {
            $duplicates = DB::table($tableName)
                ->select('operation_id', 'resident_id')
                ->whereNotNull('resident_id')
                ->groupBy('operation_id', 'resident_id')
                ->havingRaw('COUNT(*) > 1')
                ->exists();

            if ($duplicates) {
                throw new RuntimeException(
                    'Duplicate operation_id and resident_id records exist. '
                    . 'Resolve them before adding the unique constraint.'
                );
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->unique(
                    ['operation_id', 'resident_id'],
                    'rob_operation_resident_uq'
                );
            });
        }
    }

    public function down(): void
    {
        throw new LogicException(
            'This migration is intentionally not reversible automatically. '
            . 'Restoring household-level uniqueness may fail if multiple '
            . 'residents from the same household are already beneficiaries.'
        );
    }
};
