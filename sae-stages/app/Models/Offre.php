<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offre extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'company_id', 'titre', 'description', 'lieu',
        'duree_semaines', 'date_debut', 'remuneration',
        'domaine', 'formation_id', 'status',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'duree_semaines' => 'integer',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Utilisateur::class, 'company_id'); }
    public function formation(): BelongsTo { return $this->belongsTo(Formation::class); }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeFilter(Builder $q, array $f): Builder
    {
        return $q
            ->when($f['q'] ?? null, fn ($q, $v) =>
                $q->where(fn ($w) => $w->where('titre', 'like', "%$v%")->orWhere('description', 'like', "%$v%"))
            )
            ->when($f['lieu'] ?? null, fn ($q, $v) => $q->where('lieu', 'like', "%$v%"))
            ->when($f['domaine'] ?? null, fn ($q, $v) => $q->where('domaine', 'like', "%$v%"))
            ->when($f['formation_id'] ?? null, fn ($q, $v) => $q->where('formation_id', $v))
            ->when($f['duree_min'] ?? null, fn ($q, $v) => $q->where('duree_semaines', '>=', (int) $v))
            ->when($f['duree_max'] ?? null, fn ($q, $v) => $q->where('duree_semaines', '<=', (int) $v))
            ->when(($f['remunere'] ?? null) === '1', fn ($q) => $q->whereNotNull('remuneration')->where('remuneration', '!=', ''))
            ->when($f['debut_apres'] ?? null, fn ($q, $v) => $q->whereDate('date_debut', '>=', $v));
    }
}
