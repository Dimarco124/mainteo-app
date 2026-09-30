<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Étape 1: Convertir la colonne en VARCHAR temporairement
        DB::statement("ALTER TABLE depannages MODIFY COLUMN type_intervention VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
        
        // Étape 2: Corriger les valeurs corrompues
        DB::statement("UPDATE depannages SET type_intervention = 'Dépannage' WHERE type_intervention LIKE 'D%pannage' OR type_intervention LIKE 'D??pannage'");
        
        // Étape 3: Reconvertir en ENUM avec les bonnes valeurs
        DB::statement("ALTER TABLE depannages MODIFY COLUMN type_intervention ENUM('Dépannage', 'Maintenance', 'Installation') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'ancien ENUM (avec encodage corrompu)
        DB::statement("ALTER TABLE depannages MODIFY COLUMN type_intervention ENUM('D??pannage', 'Maintenance', 'Installation') NULL");
    }
};
