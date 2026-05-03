<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trace extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'action', 'payload', 'ip', 'user_agent', 'created_at'];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(Utilisateur::class, 'user_id'); }
}
