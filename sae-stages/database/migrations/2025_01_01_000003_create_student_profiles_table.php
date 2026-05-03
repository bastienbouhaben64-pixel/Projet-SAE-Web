<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('profils_etudiants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('utilisateurs')->cascadeOnDelete();
            $table->foreignId('formation_id')->nullable()->constrained('formations')->nullOnDelete();
            $table->string('promo')->nullable();
            $table->string('telephone')->nullable();
            $table->string('cv_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profils_etudiants');
    }
};
