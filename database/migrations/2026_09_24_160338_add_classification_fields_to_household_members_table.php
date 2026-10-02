<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('household_members', 'is_pwd')) {
            Schema::table('household_members', function (Blueprint $table) {
                $table->boolean('is_pwd')
                    ->default(false)
                    ->after('relationship');
            });
        }

        if (!Schema::hasColumn('household_members', 'is_solo_parent')) {
            Schema::table('household_members', function (Blueprint $table) {
                $table->boolean('is_solo_parent')
                    ->default(false)
                    ->after('is_pwd');
            });
        }

        if (!Schema::hasColumn('household_members', 'is_high_school')) {
            Schema::table('household_members', function (Blueprint $table) {
                $table->boolean('is_high_school')
                    ->default(false)
                    ->after('is_solo_parent');
            });
        }
    }

    public function down(): void
    {
        $columns = [];

        if (Schema::hasColumn('household_members', 'is_high_school')) {
            $columns[] = 'is_high_school';
        }

        if (Schema::hasColumn('household_members', 'is_solo_parent')) {
            $columns[] = 'is_solo_parent';
        }

        if (Schema::hasColumn('household_members', 'is_pwd')) {
            $columns[] = 'is_pwd';
        }

        if (!empty($columns)) {
            Schema::table('household_members', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};