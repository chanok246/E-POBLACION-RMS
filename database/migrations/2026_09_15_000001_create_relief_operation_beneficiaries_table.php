<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('relief_operation_beneficiaries')) {
            Schema::create('relief_operation_beneficiaries', function (Blueprint $table) {
                $table->id();

                $table->foreignId('operation_id')
                    ->constrained('relief_assistance_operations')
                    ->cascadeOnDelete();

                $table->foreignId('household_id')
                    ->constrained('households')
                    ->cascadeOnDelete();

                $table->string('beneficiary_status')
                    ->default('field_verified');

                $table->string('field_reference')->nullable();
                $table->text('verification_notes')->nullable();

                $table->foreignId('validated_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('validated_at')->nullable();

                $table->string('assistance_status')
                    ->default('awaiting_assistance');

                $table->unsignedInteger('allocated_quantity')
                    ->default(0);

                $table->text('allocation_notes')->nullable();

                $table->foreignId('allocated_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('allocated_at')->nullable();
                $table->timestamp('distributed_at')->nullable();
                $table->timestamp('claimed_at')->nullable();

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId('updated_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();
            });
        }

        // Inspect existing indexes before adding anything.
        $existingIndexes = array_map(
            fn ($row) => $row->Key_name,
            DB::select('SHOW INDEX FROM relief_operation_beneficiaries')
        );

        Schema::table('relief_operation_beneficiaries', function (Blueprint $table) use ($existingIndexes) {
            if (!in_array('rob_operation_status_idx', $existingIndexes, true)) {
                $table->index(
                    ['operation_id', 'beneficiary_status'],
                    'rob_operation_status_idx'
                );
            }

            if (!in_array('rob_operation_assistance_idx', $existingIndexes, true)) {
                $table->index(
                    ['operation_id', 'assistance_status'],
                    'rob_operation_assistance_idx'
                );
            }

            if (!in_array('rob_household_idx', $existingIndexes, true)) {
                $table->index('household_id', 'rob_household_idx');
            }

            if (!in_array('rob_operation_household_uq', $existingIndexes, true)) {
                $table->unique(
                    ['operation_id', 'household_id'],
                    'rob_operation_household_uq'
                );
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relief_operation_beneficiaries');
    }
};
