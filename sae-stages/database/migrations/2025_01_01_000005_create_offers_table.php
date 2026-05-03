<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('offres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('utilisateurs')->cascadeOnDelete();
            $table->string('titre');
            $table->text('description');
            $table->string('lieu');
            $table->unsignedSmallInteger('duree_semaines');
            $table->date('date_debut')->nullable();
            $table->string('remuneration')->nullable();
            $table->string('domaine')->nullable();
            $table->foreignId('formation_id')->nullable()->constrained('formations')->nullOnDelete();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();

            $table->index(['status', 'domaine']);
            $table->index('lieu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offres');
    }
};
