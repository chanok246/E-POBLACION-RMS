<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_requests', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | REQUEST IDENTIFICATION
            |--------------------------------------------------------------------------
            */

            $table->string('request_code')->unique();


            /*
            |--------------------------------------------------------------------------
            | RESIDENT
            |--------------------------------------------------------------------------
            | Every document request belongs to an existing resident.
            |
            | document_requests.resident_id
            |              ↓
            |        residents.id
            */

            $table->foreignId('resident_id')
                ->constrained('residents')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | DOCUMENT INFORMATION
            |--------------------------------------------------------------------------
            */

            $table->string('document_type');

            $table->text('purpose')->nullable();


            /*
            |--------------------------------------------------------------------------
            | REQUEST STATUS
            |--------------------------------------------------------------------------
            |
            | Pending
            | Processing
            | Approved
            | Completed
            | Released
            | Disapproved
            */

            $table->enum('status', [
                'Pending',
                'Processing',
                'Approved',
                'Completed',
                'Released',
                'Disapproved',
            ])->default('Pending');


            /*
            |--------------------------------------------------------------------------
            | REMARKS
            |--------------------------------------------------------------------------
            */

            $table->text('remarks')->nullable();


            /*
            |--------------------------------------------------------------------------
            | REQUEST TIMESTAMPS
            |--------------------------------------------------------------------------
            */

            $table->timestamp('requested_at')->useCurrent();

            $table->timestamp('processed_at')->nullable();

            $table->timestamp('released_at')->nullable();


            /*
            |--------------------------------------------------------------------------
            | CREATED / UPDATED
            |--------------------------------------------------------------------------
            */

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_requests');
    }
};
