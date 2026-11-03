
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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('personnel_id')
                ->constrained('personnel')
                ->restrictOnDelete();

            $table->date('attendance_date');

            $table->dateTime('time_in')->nullable();
            $table->dateTime('time_out')->nullable();

            $table->string('time_in_method', 20)->nullable();
            $table->string('time_out_method', 20)->nullable();

            $table->string('status', 30)->default('present');

            $table->text('remarks')->nullable();

            $table->timestamps();

            // One attendance record per personnel per calendar day.
            $table->unique(
                ['personnel_id', 'attendance_date'],
                'attendance_personnel_date_unique'
            );

            $table->index('attendance_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
