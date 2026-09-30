<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ajouter date_debut_souhaitee à la table demandes
        Schema::table('demandes', function (Blueprint $table) {
            if (!Schema::hasColumn('demandes', 'date_debut_souhaitee')) {
                $table->date('date_debut_souhaitee')->nullable()->after('niveau_urgence');
            }
        });

        // 2. Ajouter date_debut_prevue et date_fin_prevue à la table depannages
        Schema::table('depannages', function (Blueprint $table) {
            if (!Schema::hasColumn('depannages', 'date_debut_prevue')) {
                $table->date('date_debut_prevue')->nullable()->after('date_prevue');
            }
            if (!Schema::hasColumn('depannages', 'date_fin_prevue')) {
                $table->date('date_fin_prevue')->nullable()->after('date_debut_prevue');
            }
        });

        // 3. Initialiser les dates pour les demandes et dépannages existants
        try {
            \Illuminate\Support\Facades\DB::statement("UPDATE demandes SET date_debut_souhaitee = DATE(created_at) WHERE date_debut_souhaitee IS NULL");
            \Illuminate\Support\Facades\DB::statement("UPDATE depannages SET date_debut_prevue = DATE(date_creation) WHERE date_debut_prevue IS NULL AND date_creation IS NOT NULL");
            \Illuminate\Support\Facades\DB::statement("UPDATE depannages SET date_fin_prevue = date_limite_souhaitee WHERE date_fin_prevue IS NULL AND date_limite_souhaitee IS NOT NULL");
        } catch (\Throwable $e) {
            // Ignorer si la table est vide
        }
    }

    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            if (Schema::hasColumn('demandes', 'date_debut_souhaitee')) {
                $table->dropColumn('date_debut_souhaitee');
            }
        });

        Schema::table('depannages', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('depannages', 'date_debut_prevue')) {
                $columnsToDrop[] = 'date_debut_prevue';
            }
            if (Schema::hasColumn('depannages', 'date_fin_prevue')) {
                $columnsToDrop[] = 'date_fin_prevue';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
