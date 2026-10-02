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
        Schema::table('reports_complaints', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | Witnesses
            |--------------------------------------------------------------------------
            |
            | Stores manually entered witness name(s) for subpoena/summons.
            |
            | Multiple witnesses may be entered, preferably one name per line.
            | Witnesses are intentionally stored as manual names and are not
            | linked to the residents table.
            |
            */

            $table->text('witnesses')
                ->nullable()
                ->after('summons_printed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports_complaints', function (Blueprint $table) {
            $table->dropColumn('witnesses');
        });
    }
};