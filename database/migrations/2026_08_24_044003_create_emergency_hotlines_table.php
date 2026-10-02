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
        Schema::create('emergency_hotlines', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Primary Key
            |--------------------------------------------------------------------------
            */

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Emergency Agency Information
            |--------------------------------------------------------------------------
            |
            | name
            | Example:
            | Bureau of Fire Protection - Bani
            |
            | category
            | Example:
            | Fire
            |
            */

            $table->string('name', 150);

            $table->string('category', 100)->index();


            /*
            |--------------------------------------------------------------------------
            | Contact Person
            |--------------------------------------------------------------------------
            |
            | Optional because some agencies may only provide an office
            | contact number.
            |
            */

            $table->string('contact_person', 150)->nullable();


            /*
            |--------------------------------------------------------------------------
            | Primary Contact Number
            |--------------------------------------------------------------------------
            */

            $table->string('phone_number', 50);


            /*
            |--------------------------------------------------------------------------
            | Alternative Contact Number
            |--------------------------------------------------------------------------
            |
            | Optional secondary number.
            |
            */

            $table->string('alternate_number', 50)->nullable();


            /*
            |--------------------------------------------------------------------------
            | Agency Address
            |--------------------------------------------------------------------------
            */

            $table->string('address', 255)->nullable();


            /*
            |--------------------------------------------------------------------------
            | Additional Information
            |--------------------------------------------------------------------------
            |
            | Example:
            | "For fire emergencies and rescue assistance."
            |
            */

            $table->text('description')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            |
            | active
            |   The hotline is currently available and should appear
            |   in the operational directory.
            |
            | inactive
            |   The hotline is temporarily unavailable or should not
            |   be used.
            |
            */

            $table->string('status', 20)
                ->default('active')
                ->index();


            /*
            |--------------------------------------------------------------------------
            | Audit Information
            |--------------------------------------------------------------------------
            |
            | These fields identify which authenticated Barangay user
            | created and last updated the hotline.
            |
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


            /*
            |--------------------------------------------------------------------------
            | Composite Index
            |--------------------------------------------------------------------------
            |
            | Useful when filtering the directory by category and status.
            |
            */

            $table->index([
                'category',
                'status',
            ]);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_hotlines');
    }
};
