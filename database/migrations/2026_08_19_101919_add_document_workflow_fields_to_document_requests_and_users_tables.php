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
        | DOCUMENT-PROCESSOR AUTHORITY
        |--------------------------------------------------------------------------
        |
        | Captain can assign this permission to Secretary, Treasurer, or Staff.
        | Authorized users can verify, approve, print, and release requests.
        |
        */

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('document_processor')
                ->default(false);
        });

        /*
        |--------------------------------------------------------------------------
        | DOCUMENT WORKFLOW + AUDIT TRAIL
        |--------------------------------------------------------------------------
        */

        Schema::table('document_requests', function (Blueprint $table) {
            $table->string('workflow_stage', 40)
                ->default('requested');

            $table->foreignId('inspection_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('inspection_at')
                ->nullable();

            $table->foreignId('id_verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('id_verified_at')
                ->nullable();

            $table->foreignId('eligible_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('eligible_at')
                ->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')
                ->nullable();

            $table->foreignId('printed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('printed_at')
                ->nullable();

            $table->unsignedInteger('print_count')
                ->default(0);

            $table->foreignId('signed_sealed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('signed_sealed_at')
                ->nullable();

            $table->foreignId('released_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('decline_reason', 255)
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropForeign([
                'inspection_by',
                'id_verified_by',
                'eligible_by',
                'approved_by',
                'printed_by',
                'signed_sealed_by',
                'released_by',
            ]);

            $table->dropColumn([
                'workflow_stage',
                'inspection_by',
                'inspection_at',
                'id_verified_by',
                'id_verified_at',
                'eligible_by',
                'eligible_at',
                'approved_by',
                'approved_at',
                'printed_by',
                'printed_at',
                'print_count',
                'signed_sealed_by',
                'signed_sealed_at',
                'released_by',
                'decline_reason',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('document_processor');
        });
    }
};
