<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportComplaint extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    protected $table = 'reports_complaints';


    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        // Identification
        'record_code',

        // Classification
        'report_type',
        'category',
        'title',

        // Complainant
        'complainant_id',
        'complainant_name',
        'complainant_address',
        'complainant_contact_number',
        'complainant_barangay',
        'complainant_municipality',
        'complainant_province',

        // Subject / Respondent
        'subject_resident_id',
        'subject_name',
        'subject_address',
        'subject_contact_number',
        'subject_barangay',
        'subject_municipality',
        'subject_province',
        'relationship_to_complainant',

        // Dates
        'date_reported',
        'time_reported',
        'incident_date',
        'incident_time',

        // Location
        'location',
        'purok',
        'barangay',
        'municipality',
        'province',
        'region',

        // Narrative
        'description',

        // Workflow
        'status',
        'is_confidential',
        'assigned_to',
        'remarks',

        // Resolution
        'resolution',
        'resolved_at',
        'closed_at',

        // Summons / Subpoena
        'summons_requested',
        'summons_printed_at',
        'witnesses',

        // Hearing
        'hearing_date',
        'hearing_time',
        'hearing_location',

        // Audit
        'created_by',

        // Archive
        'is_archived',
        'archived_at',
        'archived_by',
    ];


    /*
    |--------------------------------------------------------------------------
    | Attribute Casting
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [

            // Dates
            'date_reported' => 'date',
            'incident_date' => 'date',
            'hearing_date' => 'date',

            // Times
            'time_reported' => 'datetime:H:i',
            'incident_time' => 'datetime:H:i',
            'hearing_time' => 'datetime:H:i',

            // Boolean
            'is_confidential' => 'boolean',
            'summons_requested' => 'boolean',
            'is_archived' => 'boolean',

            // Date / Time
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'summons_printed_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Poblacion resident who filed the report/complaint.
     *
     * Nullable because a complainant may be an outside person.
     */
    public function complainant(): BelongsTo
    {
        return $this->belongsTo(
            Resident::class,
            'complainant_id'
        );
    }


    /**
     * Poblacion resident who is the subject/respondent.
     *
     * Nullable because a respondent may be an outside person.
     */
    public function subjectResident(): BelongsTo
    {
        return $this->belongsTo(
            Resident::class,
            'subject_resident_id'
        );
    }


    /**
     * Barangay official/staff assigned to the record.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }


    /**
     * User who created/encoded the record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    /**
     * User who archived the record.
     */
    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'archived_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Report Type Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether this is a person complaint.
     */
    public function isPersonComplaint(): bool
    {
        return $this->report_type === 'person';
    }


    /**
     * Determine whether this is a community concern.
     */
    public function isCommunityConcern(): bool
    {
        return $this->report_type === 'community';
    }


    /*
    |--------------------------------------------------------------------------
    | Complainant Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether the complainant is linked to a
     * Poblacion resident record.
     */
    public function hasComplainantResident(): bool
    {
        return !is_null($this->complainant_id);
    }


    /**
     * Determine whether the complainant is stored as a
     * manually entered outside person.
     */
    public function hasManualComplainant(): bool
    {
        return is_null($this->complainant_id)
            && filled(
                $this->getRawOriginal('complainant_name')
            );
    }


    /**
     * Get the complainant's complete display name.
     *
     * Uses the resident masterlist when linked.
     * Otherwise uses the manually entered complainant name.
     *
     * Uses getRawOriginal() for complainant_name to avoid
     * recursively calling this accessor.
     */
    public function getComplainantNameAttribute(): string
    {
        if ($this->complainant) {
            return trim(
                collect([
                    $this->complainant->first_name,
                    $this->complainant->middle_name,
                    $this->complainant->last_name,
                    $this->complainant->suffix,
                ])
                ->filter()
                ->implode(' ')
            );
        }

        $manualName = $this->getRawOriginal('complainant_name');

        return filled($manualName)
            ? (string) $manualName
            : 'Unknown Person';
    }


    /*
    |--------------------------------------------------------------------------
    | Subject / Respondent Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether the subject/respondent is linked
     * to a Poblacion resident record.
     */
    public function hasSubjectResident(): bool
    {
        return !is_null($this->subject_resident_id);
    }


    /**
     * Determine whether the subject/respondent is stored
     * as a manually entered outside person.
     *
     * Uses getRawOriginal() to access the actual database
     * column without triggering getSubjectNameAttribute().
     */
    public function hasManualSubject(): bool
    {
        return is_null($this->subject_resident_id)
            && filled(
                $this->getRawOriginal('subject_name')
            );
    }


    /**
     * Get the subject/respondent's complete display name.
     *
     * Uses the resident masterlist when linked.
     * Otherwise uses the manually entered subject name.
     *
     * Uses getRawOriginal() for subject_name to avoid
     * recursively calling getSubjectNameAttribute().
     */
    public function getSubjectResidentNameAttribute(): string
    {
        if ($this->subjectResident) {
            return trim(
                collect([
                    $this->subjectResident->first_name,
                    $this->subjectResident->middle_name,
                    $this->subjectResident->last_name,
                    $this->subjectResident->suffix,
                ])
                ->filter()
                ->implode(' ')
            );
        }

        $manualName = $this->getRawOriginal('subject_name');

        return filled($manualName)
            ? (string) $manualName
            : 'N/A';
    }


    /**
     * Generic respondent/subject display-name alias.
     *
     * Uses the linked Poblacion resident when available.
     * Otherwise uses the manually entered respondent name.
     *
     * Uses getRawOriginal() so the accessor does not call
     * itself recursively.
     */
    public function getSubjectNameAttribute(): string
    {
        if ($this->subjectResident) {
            return trim(
                collect([
                    $this->subjectResident->first_name,
                    $this->subjectResident->middle_name,
                    $this->subjectResident->last_name,
                    $this->subjectResident->suffix,
                ])
                ->filter()
                ->implode(' ')
            );
        }

        $manualName = $this->getRawOriginal('subject_name');

        return filled($manualName)
            ? (string) $manualName
            : 'N/A';
    }


    /*
    |--------------------------------------------------------------------------
    | Subpoena / Summons Helpers
    |--------------------------------------------------------------------------
    |
    | The database currently uses the existing summons_* column names.
    | The application can still display this feature as "Subpoena".
    |
    */

    /**
     * Determine whether a subpoena/summons was requested.
     */
    public function hasSubpoenaRequest(): bool
    {
        return (bool) $this->summons_requested;
    }


    /**
     * Determine whether a subpoena/summons has already been printed.
     */
    public function hasPrintedSubpoena(): bool
    {
        return !is_null($this->summons_printed_at);
    }


    /**
     * Determine whether a hearing has been scheduled.
     */
    public function hasHearingScheduled(): bool
    {
        return !is_null($this->hearing_date);
    }


    /*
    |--------------------------------------------------------------------------
    | Backward-Compatible Summons Helpers
    |--------------------------------------------------------------------------
    */

    public function hasSummonsRequest(): bool
    {
        return $this->hasSubpoenaRequest();
    }


    public function hasPrintedSummons(): bool
    {
        return $this->hasPrintedSubpoena();
    }


    /*
    |--------------------------------------------------------------------------
    | Workflow Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether the record is resolved.
     */
    public function isResolved(): bool
    {
        return $this->status === 'Resolved';
    }


    /**
     * Determine whether the record is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'Closed';
    }


    /**
     * Determine whether the record is disapproved.
     */
    public function isDisapproved(): bool
    {
        return $this->status === 'Disapproved';
    }


    /**
     * Determine whether the record is archived.
     */
    public function isArchived(): bool
    {
        return (bool) $this->is_archived;
    }


    /**
     * Determine whether the record is still active.
     */
    public function isActive(): bool
    {
        return !in_array(
            $this->status,
            [
                'Resolved',
                'Closed',
                'Disapproved',
            ],
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Display Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get a readable report type.
     */
    public function getReportTypeLabelAttribute(): string
    {
        return match ($this->report_type) {

            'person' =>
                'Person Complaint',

            'community' =>
                'Community Concern',

            default =>
                ucfirst((string) $this->report_type),
        };
    }


    /**
     * Get a readable status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {

            'Filed' =>
                'Filed',

            'Under Review' =>
                'Under Review',

            'Awaiting Captain Approval' =>
                'Awaiting Captain Approval',

            'Awaiting Captain Availability' =>
                'Awaiting Captain Availability',

            'Hearing Scheduled' =>
                'Hearing Scheduled',

            'For Further Information' =>
                'For Further Information',

            'Under Resolution' =>
                'Under Resolution',

            'Resolved' =>
                'Resolved',

            'Closed' =>
                'Closed',

            'Disapproved' =>
                'Disapproved',

            default =>
                ucfirst((string) $this->status),
        };
    }
}