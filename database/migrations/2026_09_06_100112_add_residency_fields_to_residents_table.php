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
        Schema::table('residents', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | Residency Information
            |--------------------------------------------------------------------------
            |
            | Original Resident:
            |     Resident who is originally/currently established in the
            |     barangay without the migrant residency requirement.
            |
            | Migrant Resident:
            |     Resident who moved into the barangay and must complete
            |     six months of residency before being considered eligible
            |     under the system's residency rule.
            |
            */

            $table->enum('residency_type', [
                'Original Resident',
                'Migrant Resident',
            ])
                ->default('Original Resident')
                ->after('occupation');

            $table->date('residency_start_date')
                ->nullable()
                ->after('residency_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropColumn([
                'residency_type',
                'residency_start_date',
            ]);
        });
    }
};
