<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentStage extends Model
{
    protected $table = 'documents_stage';

    public const TYPES = ['rapport', 'resume', 'fiche_eval', 'autre'];

    protected $fillable = ['stage_id', 'type', 'titre', 'file_path', 'mime', 'size', 'uploaded_by'];

    public function stage(): BelongsTo { return $this->belongsTo(Stage::class); }
    public function uploader(): BelongsTo { return $this->belongsTo(Utilisateur::class, 'uploaded_by'); }
}
