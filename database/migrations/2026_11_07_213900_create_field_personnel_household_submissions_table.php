<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $tableName = 'field_personnel_household_submissions';

    public function up(): void
    {
        /*
         * The previous migration attempt may have created the table
         * before MySQL rejected Laravel's automatically generated
         * foreign-key names. Repair that existing table safely.
         */
        if (Schema::hasTable($this->tableName)) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->unique(
                    'submission_reference',
                    'fpshs_reference_unique'
                );

                $table->foreign(
                    'field_personnel_account_id',
                    'fpshs_personnel_fk'
                )
                    ->references('id')
                    ->on('field_personnel_accounts')
                    ->restrictOnDelete();

                $table->foreign(
                    'reviewed_by',
                    'fpshs_reviewer_fk'
                )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table->foreign(
                    'approved_household_id',
                    'fpshs_household_fk'
                )
                    ->references('id')
                    ->on('households')
                    ->nullOnDelete();

                $table->index(
                    ['status', 'submitted_at'],
                    'fpshs_status_submitted_idx'
                );

                $table->index(
                    ['field_personnel_account_id', 'submitted_at'],
                    'fpshs_personnel_submitted_idx'
                );
            });

            return;
        }

        /*
         * Fresh installation: create the complete table with
         * explicitly named constraints and indexes.
         */
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();

            $table->string('submission_reference', 40);

            $table->unsignedBigInteger('field_personnel_account_id');

            $table->string('submitted_by_name');
            $table->string('submitted_by_position')->nullable();

            $table->longText('payload');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('gps_accuracy_meters', 8, 2)->nullable();
            $table->timestamp('gps_captured_at')->nullable();

            $table->string('status', 30)->default('pending');
            $table->timestamp('submitted_at')->useCurrent();

            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->text('rejection_reason')->nullable();

            $table->unsignedBigInteger('approved_household_id')->nullable();

            $table->timestamps();

            $table->unique(
                'submission_reference',
                'fpshs_reference_unique'
            );

            $table->foreign(
                'field_personnel_account_id',
                'fpshs_personnel_fk'
            )
                ->references('id')
                ->on('field_personnel_accounts')
                ->restrictOnDelete();

            $table->foreign(
                'reviewed_by',
                'fpshs_reviewer_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign(
                'approved_household_id',
                'fpshs_household_fk'
            )
                ->references('id')
                ->on('households')
                ->nullOnDelete();

            $table->index(
                ['status', 'submitted_at'],
                'fpshs_status_submitted_idx'
            );

            $table->index(
                ['field_personnel_account_id', 'submitted_at'],
                'fpshs_personnel_submitted_idx'
            );
        });
    }

    public function down(): void
    {
        throw new \LogicException(
            'Rollback is disabled for Field Personnel household submissions to prevent accidental data loss.'
        );
    }
};
