
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
        Schema::create('field_personnel_accounts', function (Blueprint $table) {
            $table->id();

            // Field Personnel identity.
            // Independent from Attendance Personnel registration.
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('suffix', 20)->nullable();
            $table->string('position', 100);

            // Field Collection login credentials.
            $table->string('username', 100)->unique();
            $table->string('login_password_hash');

            // Account access control.
            $table->boolean('account_active')
                ->default(true)
                ->index();

            // Secretary who created the account.
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Credential and login activity timestamps.
            $table->timestamp('password_updated_at')->nullable();
            $table->timestamp('last_login_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_personnel_accounts');
    }
};
