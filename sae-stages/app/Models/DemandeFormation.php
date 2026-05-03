<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandeFormation extends Model
{
    protected $table = 'demandes_formation';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = ['user_id', 'intitule', 'justification', 'status', 'admin_comment', 'handled_by', 'handled_at'];
    protected $casts = ['handled_at' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(Utilisateur::class, 'user_id'); }
    public function handler(): BelongsTo { return $this->belongsTo(Utilisateur::class, 'handled_by'); }
}
