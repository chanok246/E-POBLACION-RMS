<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('relief_vouchers')) {
            throw new RuntimeException(
                'Cannot add voucher user foreign keys: relief_vouchers table is missing.'
            );
        }

        if (! Schema::hasTable('users')) {
            throw new RuntimeException(
                'Cannot add voucher user foreign keys: users table is missing.'
            );
        }

        foreach (['qr_verified_by', 'released_by'] as $column) {
            if (! Schema::hasColumn('relief_vouchers', $column)) {
                throw new RuntimeException(
                    "Cannot add foreign key: relief_vouchers.{$column} is missing."
                );
            }

            $existingForeignKey = DB::selectOne(
                "SELECT CONSTRAINT_NAME
                 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'relief_vouchers'
                   AND COLUMN_NAME = ?
                   AND REFERENCED_TABLE_NAME = 'users'
                   AND REFERENCED_COLUMN_NAME = 'id'
                 LIMIT 1",
                [$column]
            );

            if ($existingForeignKey) {
                continue;
            }

            $orphanCount = DB::table('relief_vouchers as rv')
                ->leftJoin('users as u', 'u.id', '=', "rv.{$column}")
                ->whereNotNull("rv.{$column}")
                ->whereNull('u.id')
                ->count();

            if ($orphanCount > 0) {
                throw new RuntimeException(
                    "Cannot add foreign key for {$column}: "
                    . "{$orphanCount} orphaned record(s) found."
                );
            }

            Schema::table(
                'relief_vouchers',
                function (Blueprint $table) use ($column) {
                    $table->foreign($column)
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();
                }
            );
        }
    }

    public function down(): void
    {
        throw new LogicException(
            'This corrective migration is not automatically reversible. '
            . 'Review foreign key dependencies before manual rollback.'
        );
    }
};
