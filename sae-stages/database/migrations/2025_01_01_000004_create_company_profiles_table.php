<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('profils_entreprises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('utilisateurs')->cascadeOnDelete();
            $table->string('raison_sociale');
            $table->string('siret')->nullable();
            $table->string('adresse')->nullable();
            $table->string('secteur')->nullable();
            $table->string('site_web')->nullable();
            $table->boolean('is_validated')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profils_entreprises');
    }
};
