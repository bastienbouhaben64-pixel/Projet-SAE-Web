<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Convention extends Model
{
    protected $fillable = [
        'stage_id', 'contenu',
        'signed_student_at', 'signed_company_at', 'signed_tutor_at',
        'validated_admin_at', 'validated_admin_by',
    ];
    protected $casts = [
        'signed_student_at' => 'datetime',
        'signed_company_at' => 'datetime',
        'signed_tutor_at' => 'datetime',
        'validated_admin_at' => 'datetime',
    ];

    public function stage(): BelongsTo { return $this->belongsTo(Stage::class); }

    public function isFullySigned(): bool
    {
        return $this->signed_student_at && $this->signed_company_at && $this->signed_tutor_at;
    }

    public function isAdminValidated(): bool
    {
        return $this->validated_admin_at !== null;
    }
}
