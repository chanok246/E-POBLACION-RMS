<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('relief_vouchers', function (Blueprint $table) {
            $table->boolean('is_id_verified')
                ->default(false)
                ->after('requirements_verified');

            $table->boolean('is_proof_verified')
                ->default(false)
                ->after('is_id_verified');

            $table->string('physical_file_ref')
                ->nullable()
                ->after('is_proof_verified');

            $table->foreignId('processed_by_staff_id')
                ->nullable()
                ->after('physical_file_ref')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('relief_vouchers', function (Blueprint $table) {
            $table->dropForeign(['processed_by_staff_id']);
            $table->dropColumn([
                'processed_by_staff_id',
                'physical_file_ref',
                'is_proof_verified',
                'is_id_verified',
            ]);
        });
    }
};