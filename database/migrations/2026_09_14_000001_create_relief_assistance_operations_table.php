
<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The relief_assistance_operations table is already created by:
     *
     * 2026_09_04_085007_update_relief_vouchers_status_enum.php
     *
     * This migration is retained for migration-history compatibility.
     * Do not create or drop the table again.
     */
    public function up(): void
    {
        // Intentionally empty.
    }

    /**
     * The original table belongs to the September 4 migration.
     * This duplicate migration must not drop it.
     */
    public function down(): void
    {
        // Intentionally empty.
    }
};
