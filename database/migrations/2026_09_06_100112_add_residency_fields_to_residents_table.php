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
         * The original residents migration already creates:
         * - residency_type
         * - residency_start_year
         * - residency_year_estimated
         *
         * The September 13 corrective migration handles the
         * residency-year structure and removes this legacy date.
         *
         * Therefore, do not add residency_type again.
         */

        if (
            Schema::hasTable('residents')
            && !Schema::hasColumn('residents', 'residency_start_date')
        ) {
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
        if (Schema::hasColumn('residents', 'residency_start_date')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->dropColumn('residency_start_date');
            });
        }
    }
};
