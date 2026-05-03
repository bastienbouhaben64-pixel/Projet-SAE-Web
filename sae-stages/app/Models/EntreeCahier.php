<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntreeCahier extends Model
{
    protected $table = 'entrees_cahier';

    protected $fillable = ['stage_id', 'date', 'titre', 'contenu'];
    protected $casts = ['date' => 'date'];

    public function stage(): BelongsTo { return $this->belongsTo(Stage::class); }
}
