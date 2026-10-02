<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PersonnelAccountSeeder extends Seeder
{
    /**
     * Create the default barangay personnel accounts.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Barangay Captain
        |--------------------------------------------------------------------------
        */

        User::updateOrCreate(
            [
                'email' => 'captain@poblacionbani.gov.ph',
            ],
            [
                'name' => 'Barangay Captain',
                'password' => Hash::make('Captain@123'),
                'role' => User::ROLE_CAPTAIN,
                'status' => User::STATUS_ACTIVE,
                'document_processor' => true,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Barangay Secretary
        |--------------------------------------------------------------------------
        */

        User::updateOrCreate(
            [
                'email' => 'secretary@poblacionbani.gov.ph',
            ],
            [
                'name' => 'Barangay Secretary',
                'password' => Hash::make('Secretary@123'),
                'role' => User::ROLE_SECRETARY,
                'status' => User::STATUS_ACTIVE,
                'document_processor' => true,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Barangay Treasurer
        |--------------------------------------------------------------------------
        */

        User::updateOrCreate(
            [
                'email' => 'treasurer@poblacionbani.gov.ph',
            ],
            [
                'name' => 'Barangay Treasurer',
                'password' => Hash::make('Treasurer@123'),
                'role' => User::ROLE_TREASURER,
                'status' => User::STATUS_ACTIVE,
                'document_processor' => true,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Barangay Staff
        |--------------------------------------------------------------------------
        */

        User::updateOrCreate(
            [
                'email' => 'staff@poblacionbani.gov.ph',
            ],
            [
                'name' => 'Barangay Staff',
                'password' => Hash::make('Staff@123'),
                'role' => User::ROLE_STAFF,
                'status' => User::STATUS_ACTIVE,
                'document_processor' => true,
            ]
        );
    }
}
