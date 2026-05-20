<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_COMPLETED = 'completed';

    public const PRIORITY_URGENT = 'urgent';

    public const PRIORITY_HIGH = 'high';

    public const PRIORITY_LOW = 'low';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'priority',
        'due_date',
        'status',
        'is_completed',
        'archived_at',
        'assigned_by',
        'assigned_to',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'due_date' => 'date',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_PENDING_REVIEW,
            self::STATUS_COMPLETED,
        ];
    }

    /**
     * @return list<string>
     */
    public static function priorities(): array
    {
        return [
            self::PRIORITY_URGENT,
            self::PRIORITY_HIGH,
            self::PRIORITY_LOW,
        ];
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }
}
