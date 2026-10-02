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
        Schema::create('residents', function (Blueprint $table) {

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | PERSONAL INFORMATION
            |--------------------------------------------------------------------------
            */

            $table->string('first_name');

            $table->string('middle_name')
                ->nullable();

            $table->string('last_name');

            $table->string('suffix')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | BASIC INFORMATION
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            | Hindi tayo magse-save ng "age".
            |
            | Birth date lang ang permanent data.
            | Ang age ay automatic na kakalkulahin ng system.
            |
            */

            $table->date('birth_date')
                ->nullable();

            $table->enum('sex', [
                'Male',
                'Female'
            ])
                ->nullable();

            /*
            | Civil status belongs to the individual resident,
            | not to the household.
            |
            | Possible values will be controlled by the application:
            |
            | Single
            | Married
            | Widowed
            | Divorced
            | Separated
            | Live-in / Cohabiting
            | Annulled
            | Not Applicable
            | Prefer not to say
            |
            */
            $table->string('civil_status')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | CONTACT INFORMATION
            |--------------------------------------------------------------------------
            |
            | Contact number and email are optional.
            |
            | If a resident has no contact number:
            |     NULL / blank
            |
            | If a resident has no email:
            |     NULL / blank
            |
            | The application layer will handle valid email values
            | and the allowed "N/A" value.
            |
            */

            $table->string('contact_number')
                ->nullable();

            $table->string('email')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | ADDRESS
            |--------------------------------------------------------------------------
            |
            | The resident address is required because every resident
            | record must have an address.
            |
            | For household registration:
            |     Household Address = source of truth.
            |
            | The application will automatically assign the household
            | address to the head and household members.
            |
            */

            $table->text('address');


            /*
            |--------------------------------------------------------------------------
            | RESIDENCY INFORMATION
            |--------------------------------------------------------------------------
            |
            | We do not store an exact residency start date.
            |
            | Instead:
            |
            | residency_type
            |     Describes the resident's residency classification.
            |
            | residency_start_year
            |     Stores the year the resident started residing in
            |     the barangay.
            |
            | residency_year_estimated
            |     Indicates whether the year is an estimate.
            |
            | Example:
            |
            |     Start Year: 2005
            |     Estimated: Yes
            |
            | This is more practical for house-to-house data collection
            | because residents may not remember the exact date.
            |
            */

            $table->string('residency_type')
                ->nullable();

            $table->unsignedSmallInteger('residency_start_year')
                ->nullable();

            $table->boolean('residency_year_estimated')
                ->default(false);


            /*
            |--------------------------------------------------------------------------
            | PRIMARY OCCUPATION / STATUS
            |--------------------------------------------------------------------------
            |
            | The application will provide predefined choices such as:
            |
            |     Student
            |     Employed
            |     Self-Employed
            |     Business Owner
            |     Farmer
            |     Fisherman
            |     Homemaker
            |     Unemployed
            |     Retired
            |     Senior Citizen
            |     Child / Not Yet School-Age
            |     Infant
            |     Other
            |
            | If "Other" is selected, the application will allow
            | the encoder to specify the occupation/status.
            |
            | We keep this as a string so the system remains flexible.
            |
            */

            $table->string('occupation')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | RESIDENT RECORD STATUS
            |--------------------------------------------------------------------------
            |
            | Active:
            |     Current resident record.
            |
            | Archived:
            |     Historical record.
            |
            | Example:
            |     Lumipat na sa ibang lugar ang resident.
            |
            | Hindi natin dine-delete ang resident record para mayroon
            | pa ring historical record sa Record Management System.
            |
            */

            $table->enum('status', [
                'Active',
                'Archived'
            ])
                ->default('Active');


            /*
            |--------------------------------------------------------------------------
            | TIMESTAMPS
            |--------------------------------------------------------------------------
            */

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};