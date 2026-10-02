<?php

namespace App\Models;

use HasFactory, Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentRequest extends Model
{
    public const STAGE_REQUESTED = 'requested';

    public const STAGE_INSPECTION = 'inspection';

    public const STAGE_ID_VERIFIED = 'id_verified';

    public const STAGE_ELIGIBLE = 'eligible';

    public const STAGE_APPROVED = 'approved';

    public const STAGE_PRINTED = 'printed';

    public const STAGE_SIGNED_SEALED = 'signed_sealed';

    public const STAGE_RELEASED = 'released';

    public const STAGE_THIRD_PARTY_DECLINED = 'third_party_declined';

    protected $fillable = [
        'request_code',
        'resident_id',
        'document_type',
        'purpose',
        'status',
        'workflow_stage',
        'remarks',
        'document_details',

        /*
        |--------------------------------------------------------------------------
        | Generated PDF
        |--------------------------------------------------------------------------
        |
        | Stores the private storage path of the personalized PDF generated
        | for this document request.
        |
        | This allows the system to reuse an already-generated PDF instead
        | of sending the same DOCX through Gotenberg repeatedly.
        |
        */
        'generated_pdf_path',

        'requested_at',
        'processed_at',
        'released_at',

        'inspection_by',
        'inspection_at',

        'id_verified_by',
        'id_verified_at',

        'eligible_by',
        'eligible_at',

        'approved_by',
        'approved_at',

        'printed_by',
        'printed_at',
        'print_count',

        'signed_sealed_by',
        'signed_sealed_at',

        'released_by',

        'decline_reason',
    ];

    protected function casts(): array
    {
        return [
            'document_details' => 'array',

            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
            'released_at' => 'datetime',

            'inspection_at' => 'datetime',
            'id_verified_at' => 'datetime',
            'eligible_at' => 'datetime',
            'approved_at' => 'datetime',
            'printed_at' => 'datetime',
            'signed_sealed_at' => 'datetime',

            'print_count' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function resident(): BelongsTo
    {
        return $this->belongsTo(
            Resident::class
        );
    }

    public function inspectionOfficer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'inspection_by'
        );
    }

    public function idVerifiedOfficer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_verified_by'
        );
    }

    public function eligibleOfficer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'eligible_by'
        );
    }

    public function approvedOfficer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function printedOfficer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'printed_by'
        );
    }

    public function signedSealedOfficer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'signed_sealed_by'
        );
    }

    public function releasedOfficer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'released_by'
        );
    }

    public function activities(): HasMany
    {
        return $this->hasMany(
            DocumentRequestActivity::class
        )->latest();
    }

    /*
    |--------------------------------------------------------------------------
    | Workflow
    |--------------------------------------------------------------------------
    */

    public static function workflowSteps(): array
    {
        return [
            self::STAGE_REQUESTED => 'Request Created',

            self::STAGE_INSPECTION => 'Inspection',

            self::STAGE_ID_VERIFIED => 'ID Verified',

            self::STAGE_ELIGIBLE => 'Eligible',

            self::STAGE_APPROVED => 'Approved for Release',

            self::STAGE_PRINTED => 'Printed',

            self::STAGE_SIGNED_SEALED => 'Signed & Sealed',

            self::STAGE_RELEASED => 'Released',
        ];
    }

    public function isThirdPartyDeclined(): bool
    {
        return $this->workflow_stage === self::STAGE_THIRD_PARTY_DECLINED;
    }

    /*
    |--------------------------------------------------------------------------
    | Generated PDF Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether this request already has a generated PDF.
     */
    public function hasGeneratedPdf(): bool
    {
        return ! empty($this->generated_pdf_path);
    }

    /**
     * Determine whether the saved generated PDF still exists.
     */
    public function generatedPdfExists(): bool
    {
        if (! $this->hasGeneratedPdf()) {
            return false;
        }

        return is_file(
            storage_path(
                'app/private/' . ltrim(
                    $this->generated_pdf_path,
                    '/'
                )
            )
        );
    }

    /**
     * Get the absolute filesystem path of the generated PDF.
     */
    public function getGeneratedPdfAbsolutePath(): ?string
    {
        if (! $this->hasGeneratedPdf()) {
            return null;
        }

        return storage_path(
            'app/private/' . ltrim(
                $this->generated_pdf_path,
                '/'
            )
        );
    }
}
