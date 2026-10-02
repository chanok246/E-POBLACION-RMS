<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Normalize existing status values first
        |--------------------------------------------------------------------------
        |
        | The original disaster_events migration used:
        | draft / active / closed / cancelled
        |
        | The application workflow uses:
        | Draft / Active / Completed / Cancelled
        |
        | Convert existing records before changing the ENUM.
        |
        */

        DB::table('disaster_events')
            ->where('status', 'draft')
            ->update(['status' => 'Draft']);

        DB::table('disaster_events')
            ->where('status', 'active')
            ->update(['status' => 'Active']);

        DB::table('disaster_events')
            ->where('status', 'closed')
            ->update(['status' => 'Completed']);

        DB::table('disaster_events')
            ->where('status', 'cancelled')
            ->update(['status' => 'Cancelled']);

        /*
        |--------------------------------------------------------------------------
        | Change the status ENUM
        |--------------------------------------------------------------------------
        */

        Schema::table('disaster_events', function (Blueprint $table) {
            $table->enum('status', [
                'Draft',
                'Active',
                'Completed',
                'Cancelled',
            ])->default('Draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Convert application values back to the original values
        |--------------------------------------------------------------------------
        */

        DB::table('disaster_events')
            ->where('status', 'Draft')
            ->update(['status' => 'draft']);

        DB::table('disaster_events')
            ->where('status', 'Active')
            ->update(['status' => 'active']);

        DB::table('disaster_events')
            ->where('status', 'Completed')
            ->update(['status' => 'closed']);

        DB::table('disaster_events')
            ->where('status', 'Cancelled')
            ->update(['status' => 'cancelled']);

        Schema::table('disaster_events', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'active',
                'closed',
                'cancelled',
            ])->default('draft')->change();
        });
    }
};
