<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Candidature extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_WITHDRAWN = 'withdrawn';

    protected $fillable = [
        'offer_id', 'student_id', 'message', 'status',
        'decision_comment', 'decided_by', 'decided_at',
    ];
    protected $casts = ['decided_at' => 'datetime'];

    public function offer(): BelongsTo { return $this->belongsTo(Offre::class, 'offer_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Utilisateur::class, 'student_id'); }
    public function decider(): BelongsTo { return $this->belongsTo(Utilisateur::class, 'decided_by'); }
    public function stage() { return $this->hasOne(Stage::class, 'application_id'); }
}
