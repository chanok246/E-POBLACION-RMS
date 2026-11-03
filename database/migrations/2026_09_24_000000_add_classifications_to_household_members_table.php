<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add individual household-member classifications.
     *
     * These classifications belong to each household member.
     * A member may have multiple classifications.
     *
     * This migration only ADDS missing columns. It never drops, renames
     * or recreates an existing column, so it is safe on a database that
     * already contains data.
     *
     * Columns handled here:
     *   is_pwd                  PWD
     *   is_solo_parent          Solo Parent
     *   is_high_school_student  legacy High School Student flag
     *
     * Overlap note: is_pwd and is_solo_parent are also added (with the
     * same hasColumn guard) by
     * 2026_09_24_160338_add_classification_fields_to_household_members_table.
     * Whichever runs first creates them; the other skips them.
     */
    public function up(): void
    {
        if (! Schema::hasTable('household_members')) {
            return;
        }

        $columns = [
            'is_pwd',
            'is_solo_parent',
            'is_high_school_student',
        ];

        Schema::table('household_members', function (Blueprint $table) use ($columns) {
            // Only position columns after "relationship" when that column actually exists.
            $previous = Schema::hasColumn('household_members', 'relationship')
                ? 'relationship'
                : null;

            foreach ($columns as $column) {
                if (! Schema::hasColumn('household_members', $column)) {
                    $definition = $table->boolean($column)->default(false);

                    if ($previous !== null) {
                        $definition->after($previous);
                    }
                }

                $previous = $column;
            }
        });
    }

    /**
     * Reverse the migration.
     *
     * Only the column that is unique to this migration is removed.
     * is_pwd and is_solo_parent are shared with the other classification
     * migration (and are used by the application), so they are NOT
     * dropped here. This prevents a rollback of this file from deleting
     * data that the other migration still owns.
     */
    public function down(): void
    {
        if (
            Schema::hasTable('household_members')
            && Schema::hasColumn('household_members', 'is_high_school_student')
        ) {
            Schema::table('household_members', function (Blueprint $table) {
                $table->dropColumn('is_high_school_student');
            });
        }
    }
};