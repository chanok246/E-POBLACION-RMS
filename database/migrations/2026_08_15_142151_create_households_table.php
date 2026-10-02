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
        Schema::create('households', function (Blueprint $table) {

            $table->id();

            $table->string('household_number')->unique();

            $table->text('address');

            $table->string('purok')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Map Coordinates
            |--------------------------------------------------------------------------
            */

            $table->decimal('latitude', 10, 7)->nullable();

            $table->decimal('longitude', 10, 7)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Household Head
            |--------------------------------------------------------------------------
            */

            $table->foreignId('household_head_id')
                ->nullable()
                ->constrained('residents')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'Active',
                'Archived',
            ])->default('Active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('households');
    }
};
