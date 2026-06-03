<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'default_public_task_assignee_id',
    ];

    public function defaultPublicTaskAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'default_public_task_assignee_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
