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
        Schema::table('reports_complaints', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Complainant
            |--------------------------------------------------------------------------
            |
            | complainant_id remains the link to the official Poblacion
            | resident masterlist.
            |
            | It is now nullable because a complainant may be:
            | - a registered Poblacion resident, or
            | - a non-resident / person not found in the resident masterlist.
            |
            */

            $table->dropForeign(['complainant_id']);

            $table->foreignId('complainant_id')
                ->nullable()
                ->change();

            $table->foreign('complainant_id')
                ->references('id')
                ->on('residents')
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Manual Complainant Information
            |--------------------------------------------------------------------------
            |
            | Used only when the complainant is not linked to the residents
            | masterlist.
            |
            */

            $table->string('complainant_name', 255)
                ->nullable()
                ->after('complainant_id');

            $table->string('complainant_address', 500)
                ->nullable()
                ->after('complainant_name');

            $table->string('complainant_contact_number', 50)
                ->nullable()
                ->after('complainant_address');

            $table->string('complainant_barangay', 100)
                ->nullable()
                ->after('complainant_contact_number');

            $table->string('complainant_municipality', 100)
                ->nullable()
                ->after('complainant_barangay');

            $table->string('complainant_province', 100)
                ->nullable()
                ->after('complainant_municipality');


            /*
            |--------------------------------------------------------------------------
            | Manual Subject / Respondent Information
            |--------------------------------------------------------------------------
            |
            | subject_resident_id remains the link to a Poblacion resident.
            |
            | If the respondent is not a Poblacion resident or cannot be found
            | in the resident masterlist, the manual fields below are used.
            |
            */

            $table->string('subject_name', 255)
                ->nullable()
                ->after('subject_resident_id');

            $table->string('subject_address', 500)
                ->nullable()
                ->after('subject_name');

            $table->string('subject_contact_number', 50)
                ->nullable()
                ->after('subject_address');

            $table->string('subject_barangay', 100)
                ->nullable()
                ->after('subject_contact_number');

            $table->string('subject_municipality', 100)
                ->nullable()
                ->after('subject_barangay');

            $table->string('subject_province', 100)
                ->nullable()
                ->after('subject_municipality');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports_complaints', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Remove Manual Complainant Information
            |--------------------------------------------------------------------------
            */

            $table->dropColumn([
                'complainant_name',
                'complainant_address',
                'complainant_contact_number',
                'complainant_barangay',
                'complainant_municipality',
                'complainant_province',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Remove Manual Subject Information
            |--------------------------------------------------------------------------
            */

            $table->dropColumn([
                'subject_name',
                'subject_address',
                'subject_contact_number',
                'subject_barangay',
                'subject_municipality',
                'subject_province',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Restore Complainant Requirement
            |--------------------------------------------------------------------------
            */

            $table->dropForeign(['complainant_id']);

            $table->foreignId('complainant_id')
                ->nullable(false)
                ->change();

            $table->foreign('complainant_id')
                ->references('id')
                ->on('residents')
                ->restrictOnDelete();
        });
    }
};