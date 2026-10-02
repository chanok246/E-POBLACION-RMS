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
        | Make sure residency_type exists
        |--------------------------------------------------------------------------
        |
        | The previous residency migration is already marked as "Ran",
        | but the actual database schema is missing some of its columns.
        |
        | We therefore check first before adding anything.
        |
        */
        if (!Schema::hasColumn('residents', 'residency_type')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->enum('residency_type', [
                    'Original Resident',
                    'Migrant Resident',
                ])
                    ->default('Original Resident')
                    ->after('occupation');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Add residency_start_year
        |--------------------------------------------------------------------------
        |
        | We no longer require an exact residency date.
        | The system stores the year instead.
        |
        */
        if (!Schema::hasColumn('residents', 'residency_start_year')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->unsignedSmallInteger('residency_start_year')
                    ->nullable()
                    ->after('residency_type');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Add residency_year_estimated
        |--------------------------------------------------------------------------
        |
        | TRUE  = the residency year is estimated
        | FALSE = the residency year is known/recorded
        |
        */
        if (!Schema::hasColumn('residents', 'residency_year_estimated')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->boolean('residency_year_estimated')
                    ->default(false)
                    ->after('residency_start_year');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Preserve old residency_start_date data if it exists
        |--------------------------------------------------------------------------
        |
        | If the old date column exists, convert its date into a year
        | before removing the obsolete column.
        |
        */
        if (Schema::hasColumn('residents', 'residency_start_date')) {
            DB::statement("
                UPDATE residents
                SET residency_start_year = YEAR(residency_start_date)
                WHERE residency_start_date IS NOT NULL
                  AND residency_start_year IS NULL
            ");

            /*
            | The old value was an exact date, so the converted year
            | is not considered an estimated year.
            */
            DB::statement("
                UPDATE residents
                SET residency_year_estimated = 0
                WHERE residency_start_date IS NOT NULL
            ");

            Schema::table('residents', function (Blueprint $table) {
                $table->dropColumn('residency_start_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Remove only the new residency-year fields.
        |--------------------------------------------------------------------------
        |
        | residency_type is intentionally retained because it may already
        | have existed before this corrective migration was created.
        |
        */
        $columnsToDrop = [];

        if (Schema::hasColumn('residents', 'residency_start_year')) {
            $columnsToDrop[] = 'residency_start_year';
        }

        if (Schema::hasColumn('residents', 'residency_year_estimated')) {
            $columnsToDrop[] = 'residency_year_estimated';
        }

        if (!empty($columnsToDrop)) {
            Schema::table('residents', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }
};