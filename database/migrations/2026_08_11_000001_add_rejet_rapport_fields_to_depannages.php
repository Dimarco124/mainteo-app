<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('depannages', function (Blueprint $table) {
            // Ajouter les colonnes de rejet de rapport
            if (!Schema::hasColumn('depannages', 'raison_rejet_rapport')) {
                $table->text('raison_rejet_rapport')->nullable();
            }
            if (!Schema::hasColumn('depannages', 'date_rejet_rapport')) {
                $table->timestamp('date_rejet_rapport')->nullable();
            }
            if (!Schema::hasColumn('depannages', 'rapport_rejete_par_user_id')) {
                $table->unsignedBigInteger('rapport_rejete_par_user_id')->nullable();
            }
        });

        // Modifier l'enum statut_rapport_technicien pour ajouter 'rejete'
        DB::statement("ALTER TABLE depannages MODIFY COLUMN statut_rapport_technicien ENUM('en_attente','soumis','transmis_client','rejete') DEFAULT 'en_attente'");
    }

    public function down(): void
    {
        Schema::table('depannages', function (Blueprint $table) {
            $table->dropColumn(['raison_rejet_rapport', 'date_rejet_rapport', 'rapport_rejete_par_user_id']);
        });

        DB::statement("ALTER TABLE depannages MODIFY COLUMN statut_rapport_technicien ENUM('en_attente','soumis','transmis_client') DEFAULT 'en_attente'");
    }
};
