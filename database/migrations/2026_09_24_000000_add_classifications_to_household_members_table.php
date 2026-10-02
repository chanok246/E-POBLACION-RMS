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
        Schema::table('household_members', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | MEMBER CLASSIFICATIONS
            |--------------------------------------------------------------------------
            |
            | These classifications belong to the individual household member,
            | not to the entire household.
            |
            | A member may have more than one classification.
            |
            */

            $table->boolean('is_pwd')
                ->default(false)
                ->after('relationship');

            $table->boolean('is_solo_parent')
                ->default(false)
                ->after('is_pwd');

            $table->boolean('is_high_school_student')
                ->default(false)
                ->after('is_solo_parent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('household_members', function (Blueprint $table) {

            $table->dropColumn([
                'is_pwd',
                'is_solo_parent',
                'is_high_school_student',
            ]);
        });
    }
};