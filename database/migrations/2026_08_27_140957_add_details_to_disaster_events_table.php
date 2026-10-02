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
        /*
         * The disaster_events table already contains the required
         * fields for this feature.
         *
         * No additional columns are needed.
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
         * The existing disaster_events structure is already
         * being used by the BRMS.
         */
    }
};
