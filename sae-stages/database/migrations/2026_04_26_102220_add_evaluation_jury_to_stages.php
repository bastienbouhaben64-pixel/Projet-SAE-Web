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
        Schema::table('stages', function (Blueprint $table) {
            $table->decimal('jury_note', 4, 2)->nullable()->after('jury_comment');
            $table->json('jury_grille')->nullable()->after('jury_note');
        });
    }

    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->dropColumn(['jury_note', 'jury_grille']);
        });
    }
};
