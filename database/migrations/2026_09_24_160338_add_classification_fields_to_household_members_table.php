<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the classification fields for the household classification
     * feature.
     *
     * Every column is created ONLY if it does not exist yet. Nothing is
     * dropped, renamed or recreated, so this is safe on a database that
     * already has data.
     *
     * IMPORTANT: if this migration is already marked "Ran", editing this
     * file does NOT change the existing database. In that case the
     * missing columns must be added with the safe, guarded procedure
     * documented separately (Tinker), not by re-running this file.
     *
     * household_members (individual level):
     *   is_pwd              PWD
     *   is_solo_parent      Solo Parent
     *   is_high_school      legacy High School flag (kept for compatibility)
     *   is_senior_citizen   Senior Citizen
     *   is_student          Student (any education level)
     *   student_level       Elementary | High School | College (nullable)
     *
     * households (household level):
     *   is_low_income         Indigent / Low-Income family
     *   is_4ps_beneficiary    4Ps Beneficiary family
     *
     * The Household Head is NOT a classification column. It comes from
     * households.household_head_id (and relationship = Head).
     */
    public function up(): void
    {
        $this->addMemberColumns();
        $this->addHouseholdColumns();
    }

    /**
     * Remove only the classification field that this migration originally
     * added by itself (is_high_school).
     *
     * The other columns (is_pwd, is_solo_parent, is_senior_citizen,
     * is_student, student_level, is_low_income, is_4ps_beneficiary) are
     * intentionally NOT dropped. They may have been added by another
     * migration or by the safe manual procedure, and may already contain
     * real data. Dropping them on rollback would be destructive.
     */
    public function down(): void
    {
        if (
            Schema::hasTable('household_members')
            && Schema::hasColumn('household_members', 'is_high_school')
        ) {
            Schema::table('household_members', function (Blueprint $table) {
                $table->dropColumn('is_high_school');
            });
        }
    }

    /**
     * Add the missing individual-level columns.
     */
    private function addMemberColumns(): void
    {
        if (! Schema::hasTable('household_members')) {
            return;
        }

        // column => type ("boolean" or "level")
        $columns = [
            'is_pwd' => 'boolean',
            'is_solo_parent' => 'boolean',
            'is_high_school' => 'boolean',
            'is_senior_citizen' => 'boolean',
            'is_student' => 'boolean',
            'student_level' => 'level',
        ];

        Schema::table('household_members', function (Blueprint $table) use ($columns) {
            // Only position columns after "relationship" when that column actually exists.
            $previous = Schema::hasColumn('household_members', 'relationship')
                ? 'relationship'
                : null;

            foreach ($columns as $column => $type) {
                if (! Schema::hasColumn('household_members', $column)) {
                    $definition = $type === 'level'
                        ? $table->string($column, 20)->nullable()
                        : $table->boolean($column)->default(false);

                    if ($previous !== null) {
                        $definition->after($previous);
                    }
                }

                $previous = $column;
            }
        });
    }

    /**
     * Add the missing household-level columns.
     */
    private function addHouseholdColumns(): void
    {
        if (! Schema::hasTable('households')) {
            return;
        }

        $columns = [
            'is_low_income',
            'is_4ps_beneficiary',
        ];

        Schema::table('households', function (Blueprint $table) use ($columns) {
            // Only position columns after "status" when that column actually exists.
            $previous = Schema::hasColumn('households', 'status')
                ? 'status'
                : null;

            foreach ($columns as $column) {
                if (! Schema::hasColumn('households', $column)) {
                    $definition = $table->boolean($column)->default(false);

                    if ($previous !== null) {
                        $definition->after($previous);
                    }
                }

                $previous = $column;
            }
        });
    }
};