
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the missing student education classification column.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('household_members', 'education_level')) {
            Schema::table('household_members', function (Blueprint $table) {
                $table->string('education_level', 50)->nullable();
            });
        }
    }

    /**
     * Intentionally preserve the column and its data on rollback.
     */
    public function down(): void
    {
        // Do not drop education_level because it may contain saved data.
    }
};
