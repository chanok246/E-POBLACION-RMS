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
        Schema::table('relief_vouchers', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Eligibility
            |--------------------------------------------------------------------------
            */

            $table->string('eligibility_basis', 255)
                ->nullable()
                ->after('assistance_type');


            /*
            |--------------------------------------------------------------------------
            | Requirements Verification
            |--------------------------------------------------------------------------
            */

            $table->string('requirements_status', 30)
                ->default('pending')
                ->after('eligibility_basis');

            $table->dateTime('requirements_verified_at')
                ->nullable()
                ->after('requirements_status');

            $table->foreignId('requirements_verified_by')
                ->nullable()
                ->after('requirements_verified_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->text('requirements_notes')
                ->nullable()
                ->after('requirements_verified_by');


            /*
            |--------------------------------------------------------------------------
            | Administrative Verification
            |--------------------------------------------------------------------------
            */

            $table->string('verification_status', 30)
                ->default('pending')
                ->after('requirements_notes');

            $table->text('verification_notes')
                ->nullable()
                ->after('verification_status');


            /*
            |--------------------------------------------------------------------------
            | Distribution Schedule
            |--------------------------------------------------------------------------
            */

            $table->date('release_date')
                ->nullable()
                ->after('verification_notes');

            $table->time('release_time')
                ->nullable()
                ->after('release_date');

            $table->string('release_location', 255)
                ->nullable()
                ->after('release_time');


            /*
            |--------------------------------------------------------------------------
            | QR Verification
            |--------------------------------------------------------------------------
            */

            $table->dateTime('qr_verified_at')
                ->nullable()
                ->after('release_location');

            $table->foreignId('qr_verified_by')
                ->nullable()
                ->after('qr_verified_at')
                ->constrained('users')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Assistance Release
            |--------------------------------------------------------------------------
            */

            $table->dateTime('released_at')
                ->nullable()
                ->after('qr_verified_by');

            $table->foreignId('released_by')
                ->nullable()
                ->after('released_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('relief_vouchers', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Drop Foreign Keys
            |--------------------------------------------------------------------------
            */

            $table->dropForeign([
                'requirements_verified_by',
            ]);

            $table->dropForeign([
                'qr_verified_by',
            ]);

            $table->dropForeign([
                'released_by',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Drop Added Columns
            |--------------------------------------------------------------------------
            */

            $table->dropColumn([
                'eligibility_basis',

                'requirements_status',
                'requirements_verified_at',
                'requirements_verified_by',
                'requirements_notes',

                'verification_status',
                'verification_notes',

                'release_date',
                'release_time',
                'release_location',

                'qr_verified_at',
                'qr_verified_by',

                'released_at',
                'released_by',
            ]);
        });
    }
};
