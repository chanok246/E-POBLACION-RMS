<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_personnel_policy_acceptances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('field_personnel_account_id');

            $table->string('policy_version', 100);

            // DATETIME avoids MariaDB's implicit TIMESTAMP auto-update behavior.
            $table->dateTime('accepted_at');

            $table->timestamps();

            $table->foreign(
                'field_personnel_account_id',
                'fp_policy_account_fk'
            )->references('id')
                ->on('field_personnel_accounts')
                ->restrictOnDelete();

            $table->unique(
                ['field_personnel_account_id', 'policy_version'],
                'fp_policy_acceptance_account_version_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_personnel_policy_acceptances');
    }
};
