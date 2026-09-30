<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Augmenter la taille de equipement_numero à 50 caractères
     * pour éviter les erreurs "Data too long"
     */
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE `equipements` MODIFY `equipement_numero` VARCHAR(50) NULL DEFAULT NULL");
            echo "✅ Colonne equipement_numero agrandie à VARCHAR(50)\n";
        } catch (\Throwable $e) {
            echo "⚠️ Erreur lors de la modification : " . $e->getMessage() . "\n";
        }
    }

    /**
     * Remettre l'ancienne taille (ne devrait pas être utilisé)
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `equipements` MODIFY `equipement_numero` VARCHAR(3) NULL DEFAULT NULL");
    }
};
