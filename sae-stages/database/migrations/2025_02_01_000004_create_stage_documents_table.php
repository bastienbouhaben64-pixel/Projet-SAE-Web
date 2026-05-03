<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documents_stage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['rapport', 'resume', 'fiche_eval', 'autre']);
            $table->string('titre');
            $table->string('file_path');
            $table->string('mime')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->foreignId('uploaded_by')->constrained('utilisateurs')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['stage_id', 'type']);
        });
    }

    public function down(): void { Schema::dropIfExists('documents_stage'); }
};
