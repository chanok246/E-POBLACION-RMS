
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add storage for the Captain's one-time recovery credential.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('captain_recovery_credential_hash')
                ->nullable();

            $table->timestamp('captain_recovery_credential_created_at')
                ->nullable();
        });
    }

    /**
     * Remove the Captain recovery credential columns.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'captain_recovery_credential_hash',
                'captain_recovery_credential_created_at',
            ]);
        });
    }
};
