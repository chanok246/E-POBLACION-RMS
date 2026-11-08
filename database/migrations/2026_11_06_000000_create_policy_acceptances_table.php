<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_acceptances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('policy_version', 50);

            $table->timestamp('accepted_at');

            $table->timestamps();

            $table->unique(
                ['user_id', 'policy_version'],
                'policy_acceptances_user_version_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_acceptances');
    }
};
