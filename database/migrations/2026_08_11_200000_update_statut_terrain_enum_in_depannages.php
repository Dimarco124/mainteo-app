<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Passer temporairement la colonne en VARCHAR pour tout manipuler sans erreur ENUM
        DB::statement("ALTER TABLE depannages MODIFY COLUMN statut VARCHAR(100) DEFAULT 'en attente'");

        // 2. Normaliser toutes les variantes de résolu/résolu vers 'resolu'
        DB::statement("UPDATE depannages SET statut = 'resolu' WHERE statut LIKE '%solu%' OR statut LIKE '%term%'");

        // 3. Securiser : convertir TOUTE valeur inconnue ou ancienne vers 'en attente'
        DB::statement("UPDATE depannages SET statut = 'en attente' WHERE statut NOT IN (
            'en attente',
            'en cours',
            'resolu',
            'en_attente_piece',
            'partiellement_resolu',
            'non_resolu',
            'approuve - en attente assignation',
            'en attente assignation'
        ) OR statut IS NULL");

        // 4. Appliquer le nouvel ENUM propre
        DB::statement("ALTER TABLE depannages MODIFY COLUMN statut ENUM(
            'en attente',
            'en cours',
            'resolu',
            'en_attente_piece',
            'partiellement_resolu',
            'non_resolu',
            'approuve - en attente assignation',
            'en attente assignation'
        ) DEFAULT 'en attente'");

        // 5. Ajouter la colonne details_statut_terrain si elle n'existe pas
        Schema::table('depannages', function (Blueprint $table) {
            if (!Schema::hasColumn('depannages', 'details_statut_terrain')) {
                $table->text('details_statut_terrain')->nullable()->after('statut');
            }
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE depannages MODIFY COLUMN statut VARCHAR(100) DEFAULT 'en attente'");
        DB::statement("ALTER TABLE depannages MODIFY COLUMN statut ENUM('en attente','en cours','resolu') DEFAULT 'en attente'");

        Schema::table('depannages', function (Blueprint $table) {
            if (Schema::hasColumn('depannages', 'details_statut_terrain')) {
                $table->dropColumn('details_statut_terrain');
            }
        });
    }
};
