<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('remarques_stage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('utilisateurs')->cascadeOnDelete();
            $table->string('author_role'); // role at time of writing
            $table->text('contenu');
            $table->enum('scope', ['general', 'rapport'])->default('general');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('remarques_stage'); }
};
