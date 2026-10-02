<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stores which notification-bell items a user has cleared.
     *
     * IMPORTANT: this table only remembers "hide this item from this user's
     * bell". It never references, changes, or deletes a Reminder record.
     */
    public function up(): void
    {
        Schema::create('notification_clears', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
            | Examples:
            |   bell:reminder:12:1790000000   (reminder id + scheduled time)
            |   bell:op:relief:5:2026-09-29   (operational item + date)
            |
            | The scheduled time is part of the reminder key, so editing a
            | reminder's date/time makes it appear in the bell again.
            */
            $table->string('notification_key', 191);

            $table->dateTime('cleared_at');

            $table->unique(['user_id', 'notification_key']);
            $table->index('cleared_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_clears');
    }
};
