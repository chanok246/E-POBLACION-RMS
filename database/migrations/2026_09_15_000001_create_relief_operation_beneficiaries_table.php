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
        Schema::create('relief_operation_beneficiaries', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | RELATIONSHIPS
            |--------------------------------------------------------------------------
            */

            $table->foreignId('operation_id')
                ->constrained('relief_assistance_operations')
                ->cascadeOnDelete();

            $table->foreignId('household_id')
                ->constrained('households')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | BENEFICIARY VERIFICATION
            |--------------------------------------------------------------------------
            |
            | field_verified
            | encoder_validated
            | confirmed
            | removed
            |
            */

            $table->string('beneficiary_status')
                ->default('field_verified');

            /*
            |--------------------------------------------------------------------------
            | BHW / PAPER REFERENCE
            |--------------------------------------------------------------------------
            */

            $table->string('field_reference')
                ->nullable();

            $table->text('verification_notes')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | ENCODER VALIDATION
            |--------------------------------------------------------------------------
            */

            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('validated_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | ASSISTANCE STATUS
            |--------------------------------------------------------------------------
            |
            | awaiting_assistance
            | allocated
            | voucher_generated
            | distributed
            | claimed
            |
            */

            $table->string('assistance_status')
                ->default('awaiting_assistance');

            /*
            |--------------------------------------------------------------------------
            | CAPTAIN / AUTHORIZED ALLOCATION
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('allocated_quantity')
                ->default(0);

            $table->text('allocation_notes')
                ->nullable();

            $table->foreignId('allocated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('allocated_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | DISTRIBUTION / CLAIM RECORD
            |--------------------------------------------------------------------------
            */

            $table->timestamp('distributed_at')
                ->nullable();

            $table->timestamp('claimed_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | AUDIT USERS
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

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */

            $table->index([
                'operation_id',
                'beneficiary_status',
            ]);

            $table->index([
                'operation_id',
                'assistance_status',
            ]);

            $table->index('household_id');

            /*
            |--------------------------------------------------------------------------
            | DUPLICATE PROTECTION
            |--------------------------------------------------------------------------
            |
            | A household should only appear once in the same
            | Relief Assistance Operation.
            |
            */

            $table->unique([
                'operation_id',
                'household_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relief_operation_beneficiaries');
    }
};
