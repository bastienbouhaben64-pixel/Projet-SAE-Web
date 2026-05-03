<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('contenu')->nullable();
            $table->timestamp('signed_student_at')->nullable();
            $table->timestamp('signed_company_at')->nullable();
            $table->timestamp('signed_tutor_at')->nullable();
            $table->timestamp('validated_admin_at')->nullable();
            $table->foreignId('validated_admin_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('conventions'); }
};
