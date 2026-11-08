<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnel', function (Blueprint $table) {
            $table->string('username', 100)
                ->nullable()
                ->unique()
                ->after('position');

            $table->string('login_password_hash')
                ->nullable()
                ->after('username');

            $table->boolean('account_active')
                ->default(false)
                ->after('login_password_hash')
                ->index();
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive.
        // Account credentials and records must not be removed
        // automatically during migration rollback.
    }
};