<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RemarqueStage extends Model
{
    protected $table = 'remarques_stage';

    protected $fillable = ['stage_id', 'author_id', 'author_role', 'contenu', 'scope'];

    public function stage(): BelongsTo { return $this->belongsTo(Stage::class); }
    public function author(): BelongsTo { return $this->belongsTo(Utilisateur::class, 'author_id'); }
}
