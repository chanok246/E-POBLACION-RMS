
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
        Schema::create('personnel', function (Blueprint $table) {
            $table->id();

            // Optional connection to an existing Captain or Secretary account.
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->nullOnDelete();

            // Manually assigned by the Captain or Secretary.
            $table->string('personnel_id', 50)->unique();

            // Personnel information.
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('suffix', 20)->nullable();
            $table->string('position', 100);

            // Six-digit PIN stored as a secure hash.
            $table->string('pin_hash')->nullable();
            $table->timestamp('pin_updated_at')->nullable();

            // Secure QR token hash. Never store the raw QR token here.
            $table->string('qr_token_hash', 64)->nullable()->unique();
            $table->timestamp('qr_issued_at')->nullable();
            $table->timestamp('qr_revoked_at')->nullable();

            // Personnel account status.
            $table->string('status', 20)->default('active')->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel');
    }
};
