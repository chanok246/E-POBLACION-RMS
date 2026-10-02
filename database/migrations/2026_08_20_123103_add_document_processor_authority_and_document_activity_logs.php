<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | DOCUMENT-PROCESSOR AUTHORITY RECORD
        |--------------------------------------------------------------------------
        |
        | Records who gave a user permission to process/release documents.
        |
        */

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('document_processor_authorized_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('document_processor_authorized_at')
                ->nullable();
        });

        /*
        |--------------------------------------------------------------------------
        | DOCUMENT ACTIVITY LOG
        |--------------------------------------------------------------------------
        |
        | One document request can have many logged actions.
        |
        */

        Schema::create('document_request_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('document_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('performed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action', 100);

            $table->string('workflow_stage', 40)
                ->nullable();

            $table->text('details')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_request_activities');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['document_processor_authorized_by']);

            $table->dropColumn([
                'document_processor_authorized_by',
                'document_processor_authorized_at',
            ]);
        });
    }
};
