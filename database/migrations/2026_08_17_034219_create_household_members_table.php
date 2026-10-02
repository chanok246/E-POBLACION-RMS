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
        Schema::create('household_members', function (Blueprint $table) {

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | HOUSEHOLD
            |--------------------------------------------------------------------------
            |
            | Sinasabi nito kung saang household kabilang ang resident.
            |
            */

            $table->foreignId('household_id')
                ->constrained('households')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | RESIDENT
            |--------------------------------------------------------------------------
            |
            | Existing resident record ang ginagamit.
            |
            | Hindi tayo gumagawa ng bagong resident kapag nag-add
            | ng household member.
            |
            */

            $table->foreignId('resident_id')
                ->constrained('residents')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | RELATIONSHIP
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | Head
            | Spouse
            | Child
            | Parent
            | Sibling
            | Grandparent
            | Grandchild
            | Other Relative
            | Non-Relative
            |
            */

            $table->string('relationship')
                ->nullable();


            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | PREVENT DUPLICATE MEMBERS
            |--------------------------------------------------------------------------
            |
            | Hindi pwedeng dalawang beses magkaroon ng parehong resident
            | sa parehong household.
            |
            */

            $table->unique([
                'household_id',
                'resident_id'
            ]);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('household_members');
    }
};
