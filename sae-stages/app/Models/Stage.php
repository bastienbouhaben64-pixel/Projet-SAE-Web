<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Stage extends Model
{
    public const STATUS_BROUILLON = 'brouillon';
    public const STATUS_CONVENTION = 'convention';
    public const STATUS_EN_COURS = 'en_cours';
    public const STATUS_TERMINE = 'termine';
    public const STATUS_VALIDE = 'valide';

    protected $fillable = [
        'application_id', 'offer_id', 'student_id', 'company_id', 'tutor_id',
        'date_debut', 'date_fin', 'status',
        'jury_id', 'jury_comment', 'jury_note', 'jury_grille', 'validated_at', 'archived_at',
    ];
    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'validated_at' => 'datetime',
        'archived_at' => 'datetime',
        'jury_grille' => 'array',
        'jury_note' => 'decimal:2',
    ];

    public function scopeActifs($q) { return $q->whereNull('archived_at'); }
    public function scopeArchives($q) { return $q->whereNotNull('archived_at'); }
    public function isArchived(): bool { return $this->archived_at !== null; }

    /** Critères d'évaluation jury (clé => libellé). Notes /5. */
    public const CRITERES_JURY = [
        'technique' => 'Maîtrise technique',
        'autonomie' => 'Autonomie & initiative',
        'communication' => 'Communication',
        'integration' => 'Intégration en entreprise',
        'qualite_ecrit' => 'Qualité du rapport écrit',
        'soutenance' => 'Qualité de la soutenance',
    ];

    public function application(): BelongsTo { return $this->belongsTo(Candidature::class, 'application_id'); }
    public function offer(): BelongsTo { return $this->belongsTo(Offre::class, 'offer_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Utilisateur::class, 'student_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Utilisateur::class, 'company_id'); }
    public function tutor(): BelongsTo { return $this->belongsTo(Utilisateur::class, 'tutor_id'); }
    public function jury(): BelongsTo { return $this->belongsTo(Utilisateur::class, 'jury_id'); }
    public function convention(): HasOne { return $this->hasOne(Convention::class); }
    public function documents(): HasMany { return $this->hasMany(DocumentStage::class); }
    public function cahierEntries(): HasMany { return $this->hasMany(EntreeCahier::class)->orderByDesc('date'); }
    public function remarks(): HasMany { return $this->hasMany(RemarqueStage::class)->latest(); }
    public function missions(): HasMany { return $this->hasMany(Mission::class); }

    public function progressPercent(): int
    {
        $start = $this->date_debut?->timestamp;
        $end = $this->date_fin?->timestamp;
        if (! $start || ! $end || $end <= $start) return 0;
        $now = now()->timestamp;
        if ($now <= $start) return 0;
        if ($now >= $end) return 100;
        return (int) round(($now - $start) / ($end - $start) * 100);
    }
}
