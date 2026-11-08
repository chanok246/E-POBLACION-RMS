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
        | Legacy Residency Date Compatibility
        |--------------------------------------------------------------------------
        |
        | The residents table already contains:
        |
        | - residency_type
        | - residency_start_year
        | - residency_year_estimated
        |
        | These fields are created by the original residents migration
        | and are actively used by the application.
        |
        | Only retain the legacy residency_start_date column temporarily
        | for compatibility with the September 13 corrective migration.
        |
        */

        if (!Schema::hasColumn('residents', 'residency_start_date')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->date('residency_start_date')
                    ->nullable()
                    ->after('residency_type');
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
        | Remove only the legacy date column
        |--------------------------------------------------------------------------
        |
        | Never drop residency_type, residency_start_year, or
        | residency_year_estimated here because those belong to
        | the original residents table structure.
        |
        */

        if (Schema::hasColumn('residents', 'residency_start_date')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->dropColumn('residency_start_date');
            });
        }
    }
};
