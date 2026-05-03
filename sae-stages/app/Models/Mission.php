<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mission extends Model
{
    public const STATUS_TODO = 'todo';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';
    public const STATUSES = [self::STATUS_TODO, self::STATUS_IN_PROGRESS, self::STATUS_DONE];

    protected $fillable = ['stage_id', 'titre', 'description', 'due_date', 'status'];
    protected $casts = ['due_date' => 'date'];

    public function stage(): BelongsTo { return $this->belongsTo(Stage::class); }
}
