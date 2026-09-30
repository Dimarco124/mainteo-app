<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrer tous les comptes "superviseur" vers "superviseur_client"
        DB::table('utilisateurs')
            ->where('type_utilisateur', 'superviseur')
            ->update(['type_utilisateur' => 'superviseur_client']);

        // Mettre à jour l'ENUM pour supprimer "superviseur" legacy
        DB::statement("ALTER TABLE utilisateurs MODIFY COLUMN type_utilisateur ENUM('admin', 'superviseur_client', 'superviseur_soutarah', 'demandeur', 'technicien', 'chef technicien') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer l'ENUM avec "superviseur"
        DB::statement("ALTER TABLE utilisateurs MODIFY COLUMN type_utilisateur ENUM('admin', 'superviseur', 'superviseur_client', 'superviseur_soutarah', 'demandeur', 'technicien', 'chef technicien') NOT NULL");
        
        // Revenir de superviseur_client vers superviseur si besoin
        DB::table('utilisateurs')
            ->where('type_utilisateur', 'superviseur_client')
            ->update(['type_utilisateur' => 'superviseur']);
    }
};
