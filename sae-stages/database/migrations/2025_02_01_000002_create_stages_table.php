<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained('candidatures')->nullOnDelete();
            $table->foreignId('offer_id')->constrained('offres')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('utilisateurs')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('utilisateurs')->cascadeOnDelete();
            $table->foreignId('tutor_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->date('date_debut');
            $table->date('date_fin');
            // workflow: brouillon -> convention -> en_cours -> termine -> valide
            $table->enum('status', ['brouillon', 'convention', 'en_cours', 'termine', 'valide'])->default('brouillon');
            $table->text('jury_comment')->nullable();
            $table->foreignId('jury_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('stages'); }
};
