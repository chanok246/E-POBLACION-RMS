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
        Schema::create('disaster_events', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Primary Key
            |--------------------------------------------------------------------------
            */

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Disaster / Assistance Information
            |--------------------------------------------------------------------------
            */

            $table->string('event_code', 50)->unique();

            $table->string('name', 150);

            $table->string('type', 100);

            $table->text('description')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Disaster Period
            |--------------------------------------------------------------------------
            */

            $table->date('start_date');

            $table->date('end_date')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            |
            | draft     = still being prepared
            | active    = current disaster/assistance program
            | closed    = assessment and distribution completed
            | cancelled = event/program cancelled
            |
            */

            $table->enum('status', [
                'draft',
                'active',
                'closed',
                'cancelled',
            ])->default('draft');


            /*
            |--------------------------------------------------------------------------
            | Assistance Information
            |--------------------------------------------------------------------------
            */

            $table->string('assistance_type', 100)->nullable();

            $table->decimal('assistance_amount', 12, 2)->nullable();


            /*
            |--------------------------------------------------------------------------
            | Barangay / Location Information
            |--------------------------------------------------------------------------
            */

            $table->string('barangay', 150)->default('Barangay Poblacion');

            $table->string('municipality', 150)->default('Bani');

            $table->string('province', 150)->default('Pangasinan');


            /*
            |--------------------------------------------------------------------------
            | Audit Information
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disaster_events');
    }
};