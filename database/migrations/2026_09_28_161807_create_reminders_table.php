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
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Reminder Owner / Creator
            |--------------------------------------------------------------------------
            |
            | Records which authenticated barangay user created the reminder.
            |
            */
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Basic Reminder Information
            |--------------------------------------------------------------------------
            */
            $table->string('title');

            $table->enum('type', [
                'Meeting',
                'Hearing',
                'Operation',
                'Event',
                'Barangay Activity',
                'Deadline',
                'Inspection',
                'Follow-up',
                'General Reminder',
                'Other',
            ])->default('General Reminder');

            $table->text('notes')->nullable();

            $table->string('location')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Schedule
            |--------------------------------------------------------------------------
            |
            | Stores the exact date and time of the scheduled activity.
            |
            */
            $table->dateTime('scheduled_at');

            /*
            |--------------------------------------------------------------------------
            | Priority
            |--------------------------------------------------------------------------
            */
            $table->enum('priority', [
                'normal',
                'important',
                'urgent',
            ])->default('normal');

            /*
            |--------------------------------------------------------------------------
            | Reminder Timing
            |--------------------------------------------------------------------------
            |
            | Stores multiple reminder offsets as JSON.
            |
            | Example:
            |
            | [
            |     "3_days",
            |     "1_day",
            |     "2_hours",
            |     "at_time"
            | ]
            |
            */
            $table->json('reminder_offsets')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Reminder Status
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'scheduled',
                'completed',
                'cancelled',
            ])->default('scheduled');

            /*
            |--------------------------------------------------------------------------
            | Completion Information
            |--------------------------------------------------------------------------
            */
            $table->foreignId('completed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->dateTime('completed_at')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */
            $table->index('scheduled_at');
            $table->index('status');
            $table->index('type');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};