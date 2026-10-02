<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The household_affected column is already handled by:
     * 2026_08_31_105737_update_disaster_assessments_table_for_final_flow.php
     */
    public function up(): void
    {
        /*
         * No changes are required here.
         *
         * household_affected is created by the final-flow migration.
         */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
         * Nothing to reverse.
         *
         * The household_affected column belongs to the final
         * disaster assessment schema.
         */
    }
};
