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
        Schema::table('relief_assistance_operations', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | MASTERLIST FILTER SETTINGS
            |--------------------------------------------------------------------------
            |
            | These fields store the exact filters used when preparing the
            | operation's masterlist.
            |
            | Example:
            | beneficiary_filter = students
            | purok_filter       = all
            |
            | This allows the encoder to reopen the saved operation later
            | and automatically see the same filtered beneficiary pool.
            |
            */

            $table->string('beneficiary_filter')
                ->nullable()
                ->after('assistance_description');

            $table->string('purok_filter')
                ->nullable()
                ->after('beneficiary_filter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('relief_assistance_operations', function (Blueprint $table) {
            $table->dropColumn([
                'beneficiary_filter',
                'purok_filter',
            ]);
        });
    }
};
