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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | WHO
            |--------------------------------------------------------------------------
            |
            | The authenticated user who performed the action.
            | Nullable because some system events may not have an authenticated
            | user associated with them.
            |
            */
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | WHAT
            |--------------------------------------------------------------------------
            */
            $table->string('action', 100);

            /*
            |--------------------------------------------------------------------------
            | WHERE / MODULE
            |--------------------------------------------------------------------------
            |
            | Examples:
            | Residents
            | Households
            | Barangay Documents
            | Reports & Complaints
            | Relief Vouchers
            | User Management
            | Settings
            |
            */
            $table->string('module', 100);

            /*
            |--------------------------------------------------------------------------
            | RECORD REFERENCE
            |--------------------------------------------------------------------------
            |
            | Stores the affected record's ID when applicable.
            | Kept as an unsigned big integer rather than a foreign key because
            | audit logs may refer to different tables.
            |
            */
            $table->unsignedBigInteger('record_id')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | RECORD IDENTIFIER
            |--------------------------------------------------------------------------
            |
            | Optional human-readable identifier such as:
            | RC-2026-0004
            | RV-P1-0001
            | document request code
            |
            */
            $table->string('record_reference', 150)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | OLD / NEW VALUES
            |--------------------------------------------------------------------------
            |
            | JSON allows us to preserve the relevant state before and after
            | an important change.
            |
            */
            $table->json('old_values')
                ->nullable();

            $table->json('new_values')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | REQUEST / SYSTEM CONTEXT
            |--------------------------------------------------------------------------
            */
            $table->string('ip_address', 45)
                ->nullable();

            $table->text('user_agent')
                ->nullable();

            $table->text('description')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | TIMESTAMP
            |--------------------------------------------------------------------------
            |
            | created_at is the important audit timestamp.
            | updated_at is intentionally NOT included.
            |
            | Audit records are intended to be append-only.
            |
            */
            $table->timestamp('created_at')
                ->useCurrent();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */
            $table->index('user_id');
            $table->index('module');
            $table->index('action');
            $table->index('record_id');
            $table->index('record_reference');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
