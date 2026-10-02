<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relief_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->onDelete('cascade');
            $table->foreignId('disaster_assessment_id')->constrained()->onDelete('cascade');
            $table->string('voucher_code')->unique(); // QR Code Payload
            $table->string('purok_name');
            $table->dateTime('distribution_schedule');
            $table->boolean('requirements_verified')->default(false);
            $table->string('assistance_type')->default('Goods and Financial');
            $table->enum('status', ['pending', 'verified', 'claimed'])->default('pending');
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relief_vouchers');
    }
};