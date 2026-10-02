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
        Schema::table('relief_vouchers', function (Blueprint $table) {
            $table->dropForeign([
                'disaster_assessment_id',
            ]);
        });

        Schema::table('relief_vouchers', function (Blueprint $table) {
            $table->foreignId('disaster_assessment_id')
                ->nullable()
                ->change();

            $table->foreign('disaster_assessment_id')
                ->references('id')
                ->on('disaster_assessments')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('relief_vouchers', function (Blueprint $table) {
            $table->dropForeign([
                'disaster_assessment_id',
            ]);
        });

        Schema::table('relief_vouchers', function (Blueprint $table) {
            $table->foreignId('disaster_assessment_id')
                ->nullable(false)
                ->change();

            $table->foreign('disaster_assessment_id')
                ->references('id')
                ->on('disaster_assessments')
                ->cascadeOnDelete();
        });
    }
};