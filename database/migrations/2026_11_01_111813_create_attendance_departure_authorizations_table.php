
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
        Schema::create('attendance_departure_authorizations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attendance_record_id')
                ->constrained('attendance_records')
                ->restrictOnDelete();

            // Actual departure time, if known or reported.
            $table->dateTime('actual_departure_at')->nullable();

            // Reason for the emergency or early departure.
            $table->text('reason');

            // Only the Barangay Captain may authorize this action.
            $table->foreignId('authorized_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->dateTime('authorized_at');

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index('authorized_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_departure_authorizations');
    }
};
