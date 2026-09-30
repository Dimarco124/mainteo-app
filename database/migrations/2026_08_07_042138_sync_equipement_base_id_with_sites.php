<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Synchroniser equipement.base_id avec site.base_id
     * 
     * La colonne equipement.base_id était incorrectement configurée dans une ancienne migration.
     * Cette migration corrige toutes les incohérences en synchronisant avec la vraie hiérarchie :
     * CLIENT → BASE → SITE → ÉQUIPEMENT
     */
    public function up(): void
    {
        // Synchroniser equipement.base_id pour qu'il corresponde à site.base_id
        DB::statement("
            UPDATE equipements e
            INNER JOIN sites s ON e.site_id = s.id
            SET e.base_id = s.base_id
            WHERE e.site_id IS NOT NULL
        ");
        
        echo "✓ base_id synchronisé pour tous les équipements avec un site\n";
    }

    /**
     * Pas de rollback nécessaire - cette migration corrige des données incohérentes
     */
    public function down(): void
    {
        // Pas de rollback - on ne peut pas "dé-corriger" les données
    }
};
