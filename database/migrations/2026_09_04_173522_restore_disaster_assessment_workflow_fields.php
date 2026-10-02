<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Restore workflow fields required by the current
     * DisasterAssessment model, controller, and views.
     */
    public function up(): void
    {
        Schema::table('disaster_assessments', function (Blueprint $table) {
            if (! Schema::hasColumn('disaster_assessments', 'evacuated')) {
                $table->boolean('evacuated')
                    ->default(false)
                    ->after('affected_members');
            }

            if (! Schema::hasColumn('disaster_assessments', 'eligibility_reason')) {
                $table->text('eligibility_reason')
                    ->nullable()
                    ->after('voucher_eligible');
            }
        });

        /*
         * If the previous final-flow migration stored an eligibility
         * explanation in voucher_eligibility_reason, copy that value
         * into the field currently used by the application.
         */
        if (
            Schema::hasColumn('disaster_assessments', 'voucher_eligibility_reason') &&
            Schema::hasColumn('disaster_assessments', 'eligibility_reason')
        ) {
            \Illuminate\Support\Facades\DB::statement("
                UPDATE disaster_assessments
                SET eligibility_reason = voucher_eligibility_reason
                WHERE eligibility_reason IS NULL
                  AND voucher_eligibility_reason IS NOT NULL
            ");
        }
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('disaster_assessments', function (Blueprint $table) {
            if (Schema::hasColumn('disaster_assessments', 'evacuated')) {
                $table->dropColumn('evacuated');
            }

            if (Schema::hasColumn('disaster_assessments', 'eligibility_reason')) {
                $table->dropColumn('eligibility_reason');
            }
        });
    }
};
