<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodeOtp extends Model
{
    protected $table = 'codes_otp';

    protected $fillable = ['user_id', 'code_hash', 'expires_at', 'used_at', 'ip'];
    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(Utilisateur::class, 'user_id'); }

    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }
}
