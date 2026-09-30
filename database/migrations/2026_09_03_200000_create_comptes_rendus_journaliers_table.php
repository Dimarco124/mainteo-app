<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ajouter les colonnes de suivi d'équipements sur la table maintenances
        Schema::table('maintenances', function (Blueprint $table) {
            if (!Schema::hasColumn('maintenances', 'nombre_equipements_prevus')) {
                $table->integer('nombre_equipements_prevus')->default(0);
            }
            if (!Schema::hasColumn('maintenances', 'nombre_equipements_traites')) {
                $table->integer('nombre_equipements_traites')->default(0);
            }
        });

        // 2. Créer la table des comptes rendus journaliers d'équipe
        if (!Schema::hasTable('comptes_rendus_journaliers')) {
            Schema::create('comptes_rendus_journaliers', function (Blueprint $table) {
                $table->id();
                
                $table->unsignedBigInteger('maintenance_id')->index();
                $table->unsignedBigInteger('site_id')->nullable()->index();
                $table->unsignedBigInteger('equipe_id')->nullable()->index();
                $table->unsignedBigInteger('responsable_id')->nullable()->index(); // Chef d'équipe ou responsable
                
                $table->date('date_rapport');
                $table->string('noms_intervenants')->nullable(); // Ex: N'guessan, Moussa et Yaya
                
                $table->text('activites_realisees');
                $table->integer('nombre_equipements_traites')->default(0);
                
                $table->text('anomalies_constatees')->nullable();
                $table->text('difficultes_rencontrees')->nullable();
                $table->text('materiel_utilise')->nullable();
                $table->text('travaux_non_termines')->nullable();
                $table->string('heure_fin')->nullable(); // Ex: 17h30
                $table->string('nom_responsable')->nullable(); // Ex: Mr N'guessan
                
                $table->unsignedBigInteger('created_by_user_id')->index();
                $table->timestamps();

                $table->index(['maintenance_id', 'date_rapport']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('comptes_rendus_journaliers');

        Schema::table('maintenances', function (Blueprint $table) {
            if (Schema::hasColumn('maintenances', 'nombre_equipements_prevus')) {
                $table->dropColumn('nombre_equipements_prevus');
            }
            if (Schema::hasColumn('maintenances', 'nombre_equipements_traites')) {
                $table->dropColumn('nombre_equipements_traites');
            }
        });
    }
};
