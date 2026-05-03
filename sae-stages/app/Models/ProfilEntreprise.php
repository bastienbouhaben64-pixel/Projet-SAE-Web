<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilEntreprise extends Model
{
    protected $table = 'profils_entreprises';

    protected $fillable = ['user_id', 'raison_sociale', 'siret', 'adresse', 'secteur', 'site_web', 'is_validated'];
    protected $casts = ['is_validated' => 'boolean'];

    public function user(): BelongsTo { return $this->belongsTo(Utilisateur::class, 'user_id'); }
}
