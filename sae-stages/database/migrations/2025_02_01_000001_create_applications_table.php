<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('candidatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offres')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('utilisateurs')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'withdrawn'])->default('pending');
            $table->text('decision_comment')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->unique(['offer_id', 'student_id']);
            $table->index('status');
        });
    }

    public function down(): void { Schema::dropIfExists('candidatures'); }
};
