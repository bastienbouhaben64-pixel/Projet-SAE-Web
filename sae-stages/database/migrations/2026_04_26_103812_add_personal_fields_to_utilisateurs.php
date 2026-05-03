<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->string('telephone', 30)->nullable()->after('email');
            $table->string('specialites', 255)->nullable()->after('telephone');
            $table->text('bio')->nullable()->after('specialites');
            $table->boolean('disponible')->default(true)->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropColumn(['telephone', 'specialites', 'bio', 'disponible']);
        });
    }
};
