<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Formation extends Model
{
    protected $fillable = ['code', 'intitule', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function offers(): HasMany
    {
        return $this->hasMany(Offre::class);
    }

    public function studentProfiles(): HasMany
    {
        return $this->hasMany(ProfilEtudiant::class);
    }
}
