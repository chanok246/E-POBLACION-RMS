<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A notification-bell item that one user has cleared.
 *
 * Clearing only hides the item from that user's bell. It never touches
 * the underlying Reminder (or any other record).
 */
class NotificationClear extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'notification_key',
        'cleared_at',
    ];

    protected function casts(): array
    {
        return [
            'cleared_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
