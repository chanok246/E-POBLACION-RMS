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
        Schema::create('disaster_assessments', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Primary Key
            |--------------------------------------------------------------------------
            */

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Disaster Event
            |--------------------------------------------------------------------------
            |
            | Every assessment must belong to a specific disaster event.
            |
            */

            $table->foreignId('disaster_event_id')
                ->constrained('disaster_events')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Household
            |--------------------------------------------------------------------------
            |
            | The household being assessed during the house-to-house inspection.
            |
            */

            $table->foreignId('household_id')
                ->constrained('households')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Assessment Information
            |--------------------------------------------------------------------------
            */

            $table->date('assessment_date');

            $table->foreignId('assessor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | House Damage
            |--------------------------------------------------------------------------
            */

            $table->enum('damage_level', [
                'none',
                'minor',
                'moderate',
                'severe',
                'destroyed',
            ])->default('none');

            $table->enum('house_livability', [
                'livable',
                'partially_livable',
                'unlivable',
            ])->default('livable');


            /*
            |--------------------------------------------------------------------------
            | Estimated Loss / Livelihood
            |--------------------------------------------------------------------------
            */

            $table->decimal('estimated_property_loss', 12, 2)
                ->default(0.00);

            $table->text('livelihood_impact')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Household Impact
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('affected_members')
                ->default(0);

            $table->boolean('evacuated')
                ->default(false);

            $table->string('evacuation_center')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Interview / Field Notes
            |--------------------------------------------------------------------------
            */

            $table->text('interview_notes')
                ->nullable();

            $table->text('remarks')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Verification
            |--------------------------------------------------------------------------
            |
            | Pending  = assessment has not yet been verified
            | Verified = assessment has been approved by verifier
            | Rejected = assessment was rejected
            |
            */

            $table->enum('verification_status', [
                'Pending',
                'Verified',
                'Rejected',
            ])->default('Pending');

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Relief Voucher Eligibility
            |--------------------------------------------------------------------------
            |
            | This is intentionally separate from verification.
            |
            | A household can be assessed and verified without automatically
            | receiving a relief voucher.
            |
            */

            $table->boolean('voucher_eligible')
                ->default(false);

            $table->text('eligibility_reason')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'disaster_event_id',
                'household_id',
            ]);

            $table->index('verification_status');

            $table->index('voucher_eligible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disaster_assessments');
    }
};
