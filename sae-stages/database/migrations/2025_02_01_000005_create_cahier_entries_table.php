<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('entrees_cahier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('titre');
            $table->text('contenu');
            $table->timestamps();
            $table->index(['stage_id', 'date']);
        });
    }

    public function down(): void { Schema::dropIfExists('entrees_cahier'); }
};
