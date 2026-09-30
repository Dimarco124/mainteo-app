<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rendre site_id nullable pour supporter les emplacements directs (Client → Emplacement)
     * sans passer par un site
     */
    public function up(): void
    {
        Schema::table('zones_sites', function (Blueprint $table) {
            // Supprimer la contrainte de clé étrangère existante
            $table->dropForeign(['site_id']);
            
            // Modifier la colonne pour être nullable
            $table->integer('site_id')->nullable()->change();
            
            // Recréer la contrainte de clé étrangère
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    /**
     * Reverser la migration
     */
    public function down(): void
    {
        Schema::table('zones_sites', function (Blueprint $table) {
            // Supprimer la contrainte de clé étrangère
            $table->dropForeign(['site_id']);
            
            // Remettre la colonne comme NOT NULL
            $table->integer('site_id')->nullable(false)->change();
            
            // Recréer la contrainte de clé étrangère
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }
};
