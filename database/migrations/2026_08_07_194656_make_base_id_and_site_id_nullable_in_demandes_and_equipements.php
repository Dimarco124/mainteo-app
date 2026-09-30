<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Rendre base_id et site_id nullable pour permettre des cas où l'entreprise est unique
     * (par exemple un immeuble sans hiérarchie de bases et sites)
     */
    public function up(): void
    {
        // Modifier les colonnes de la table demandes pour les rendre nullable
        DB::statement('ALTER TABLE demandes MODIFY base_id INT(11) UNSIGNED NULL');
        DB::statement('ALTER TABLE demandes MODIFY site_id INT(11) UNSIGNED NULL');
        
        // Modifier la table equipements
        if (Schema::hasColumn('equipements', 'site_id')) {
            // Modifier la colonne pour la rendre nullable
            DB::statement('ALTER TABLE equipements MODIFY site_id INT(11) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre les colonnes NOT NULL
        DB::statement('ALTER TABLE demandes MODIFY base_id INT(11) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE demandes MODIFY site_id INT(11) UNSIGNED NOT NULL');
        
        // Remettre site_id NOT NULL dans equipements
        if (Schema::hasColumn('equipements', 'site_id')) {
            DB::statement('ALTER TABLE equipements MODIFY site_id INT(11) NOT NULL');
        }
    }
};
