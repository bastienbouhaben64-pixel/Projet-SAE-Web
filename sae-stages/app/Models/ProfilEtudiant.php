<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilEtudiant extends Model
{
    protected $table = 'profils_etudiants';

    protected $fillable = ['user_id', 'formation_id', 'promo', 'telephone', 'cv_path'];

    public function user(): BelongsTo { return $this->belongsTo(Utilisateur::class, 'user_id'); }
    public function formation(): BelongsTo { return $this->belongsTo(Formation::class); }
}
