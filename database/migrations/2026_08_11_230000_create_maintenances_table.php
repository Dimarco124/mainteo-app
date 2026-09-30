<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('maintenances')) {
            Schema::create('maintenances', function (Blueprint $table) {
                $table->id();
                
                // Informations de base
                $table->string('numero_maintenance')->unique();
                $table->enum('type_maintenance', ['préventive', 'corrective programmée'])->default('préventive');
                
                // Localisation
                $table->unsignedBigInteger('client_id');
                $table->unsignedBigInteger('base_id')->nullable();
                $table->unsignedBigInteger('site_id')->nullable();
                $table->unsignedBigInteger('equipement_id');
                
                // Description
                $table->text('description');
                $table->text('taches_prevues')->nullable(); // Liste des tâches à effectuer
                $table->text('pieces_prevues')->nullable(); // Pièces nécessaires
                
                // Planification
                $table->dateTime('date_debut_prevue');
                $table->dateTime('date_fin_prevue')->nullable();
                $table->integer('duree_estimee_heures')->nullable(); // Durée estimée en heures
                
                // Affectation
                $table->unsignedBigInteger('equipe_id')->nullable();
                $table->unsignedBigInteger('technicien_id')->nullable();
                
                // Statut et suivi
                $table->enum('statut', [
                    'planifiée',
                    'confirmée_client',
                    'en_cours',
                    'terminée',
                    'annulée'
                ])->default('planifiée');
                
                // Confirmation client
                $table->timestamp('date_confirmation_client')->nullable();
                $table->unsignedBigInteger('confirme_par_user_id')->nullable();
                
                // Exécution
                $table->timestamp('date_debut_reelle')->nullable();
                $table->timestamp('date_fin_reelle')->nullable();
                $table->text('rapport_technicien')->nullable();
                $table->text('pieces_utilisees')->nullable();
                
                // Création
                $table->unsignedBigInteger('created_by_user_id');
                $table->enum('created_by_role', ['admin', 'superviseur_soutarah']);
                
                $table->timestamps();
                
                // Index et foreign keys
                $table->foreign('client_id')->references('id')->on('clients')->onDelete('restrict');
                $table->foreign('base_id')->references('id')->on('bases')->onDelete('set null');
                $table->foreign('site_id')->references('id')->on('sites')->onDelete('set null');
                $table->foreign('equipement_id')->references('id')->on('equipements')->onDelete('restrict');
                $table->foreign('equipe_id')->references('id')->on('equipes')->onDelete('set null');
                $table->foreign('technicien_id')->references('id')->on('utilisateurs')->onDelete('set null');
                $table->foreign('created_by_user_id')->references('id')->on('utilisateurs')->onDelete('restrict');
                $table->foreign('confirme_par_user_id')->references('id')->on('utilisateurs')->onDelete('set null');
                
                $table->index('statut');
                $table->index(['date_debut_prevue', 'date_fin_prevue']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
