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
        Schema::table('planifications', function (Blueprint $table) {
            // Ajouter colonne technicien_id pour assignation individuelle
            $table->unsignedInteger('technicien_id')->nullable()->after('equipe_id');
            
            // Ajouter index pour améliorer les performances des requêtes filtrées
            $table->index('technicien_id');
            $table->index('equipe_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planifications', function (Blueprint $table) {
            $table->dropIndex(['technicien_id']);
            $table->dropIndex(['equipe_id']);
            $table->dropColumn('technicien_id');
        });
    }
};
