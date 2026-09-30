<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute UNIQUEMENT les nouvelles colonnes et tables pour le workflow hiérarchique
     */
    public function up(): void
    {
        // 1. Ajouter site_id dans utilisateurs si elle n'existe pas
        if (!Schema::hasColumn('utilisateurs', 'site_id')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->unsignedInteger('site_id')->nullable()->after('base_id'); // Changé en unsignedInteger
            });
            
            // Ajouter foreign key si la table sites existe
            if (Schema::hasTable('sites')) {
                Schema::table('utilisateurs', function (Blueprint $table) {
                    $table->foreign('site_id')->references('id')->on('sites')->onDelete('restrict');
                });
            }
        }
        
        // 2. Migrer les utilisateurs "superviseur" vers "superviseur_client"
        DB::table('utilisateurs')
            ->where('type_utilisateur', 'superviseur')
            ->update(['type_utilisateur' => 'superviseur_client']);
        
        // 3. Créer la table assignments si elle n'existe pas
        if (!Schema::hasTable('assignments')) {
            Schema::create('assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('superviseur_soutarah_id'); // Changé en unsignedInteger
                $table->unsignedInteger('base_id')->nullable(); // Changé en unsignedInteger
                $table->unsignedInteger('client_id')->nullable(); // Changé en unsignedInteger
                $table->timestamps();
                
                $table->foreign('superviseur_soutarah_id')->references('id')->on('utilisateurs')->onDelete('cascade');
                $table->foreign('base_id')->references('id')->on('bases')->onDelete('cascade');
                $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
                
                $table->unique('superviseur_soutarah_id');
            });
        }
        
        // 4. Créer la table demandes si elle n'existe pas
        if (!Schema::hasTable('demandes')) {
            Schema::create('demandes', function (Blueprint $table) {
                $table->id();
                $table->string('numero_demande')->unique();
                
                // Qui a créé la demande
                $table->unsignedInteger('created_by_user_id'); // Changé
                $table->enum('created_by_role', ['demandeur', 'superviseur_client']);
                
                // Localisation
                $table->unsignedInteger('client_id'); // Changé
                $table->unsignedInteger('base_id'); // Changé
                $table->unsignedInteger('site_id'); // Changé
                $table->unsignedInteger('equipement_id'); // Changé
                
                // Détails de la demande
                $table->text('description');
                $table->enum('niveau_urgence', ['faible', 'moyen', 'urgent', 'critique'])->default('moyen');
                $table->date('date_limite_souhaitee')->nullable();
                $table->string('photo_panne')->nullable();
                
                // Workflow status
                $table->enum('statut', [
                    'pending_client_validation',
                    'validated_by_client',
                    'rejected_by_client',
                    'needs_technical_operation',
                    'rejected_by_soutarah',
                    'confirmed_by_demandeur',
                    'closed'
                ])->default('pending_client_validation');
                
                // Validation Superviseur Client
                $table->timestamp('date_validation_client')->nullable();
                $table->unsignedInteger('validated_by_client_user_id')->nullable(); // Changé
                $table->text('message_validation_client')->nullable();
                
                // Validation Admin/Superviseur Soutarah
                $table->timestamp('date_validation_soutarah')->nullable();
                $table->unsignedInteger('validated_by_soutarah_user_id')->nullable(); // Changé
                $table->text('message_validation_soutarah')->nullable();
                
                // Confirmation finale Demandeur
                $table->timestamp('date_confirmation_demandeur')->nullable();
                $table->text('commentaire_demandeur')->nullable();
                
                // Validation finale Superviseur Client
                $table->timestamp('date_validation_finale')->nullable();
                $table->unsignedInteger('validated_finale_by_user_id')->nullable(); // Changé
                $table->text('message_validation_finale')->nullable();
                
                // Lien vers l'opération technique
                $table->unsignedInteger('technical_operation_id')->nullable(); // Changé
                
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('created_by_user_id')->references('id')->on('utilisateurs')->onDelete('restrict');
                $table->foreign('client_id')->references('id')->on('clients')->onDelete('restrict');
                $table->foreign('base_id')->references('id')->on('bases')->onDelete('restrict');
                $table->foreign('site_id')->references('id')->on('sites')->onDelete('restrict');
                $table->foreign('equipement_id')->references('id')->on('equipements')->onDelete('restrict');
                $table->foreign('validated_by_client_user_id')->references('id')->on('utilisateurs')->onDelete('set null');
                $table->foreign('validated_by_soutarah_user_id')->references('id')->on('utilisateurs')->onDelete('set null');
                $table->foreign('validated_finale_by_user_id')->references('id')->on('utilisateurs')->onDelete('set null');
                $table->foreign('technical_operation_id')->references('id')->on('depannages')->onDelete('set null');
            });
        }
        
        // 5. Ajouter les nouveaux champs dans depannages si ils n'existent pas
        Schema::table('depannages', function (Blueprint $table) {
            if (!Schema::hasColumn('depannages', 'demande_id')) {
                $table->unsignedInteger('demande_id')->nullable()->after('id'); // Changé
                $table->foreign('demande_id')->references('id')->on('demandes')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('depannages', 'statut_operation')) {
                $table->enum('statut_operation', [
                    'pending_assignment',
                    'assigned',
                    'in_progress',
                    'completed',
                    'report_submitted',
                    'rework_needed',
                    'closed'
                ])->default('pending_assignment')->after('statut');
            }
            
            if (!Schema::hasColumn('depannages', 'rapport_technique')) {
                $table->text('rapport_technique')->nullable()->after('rapport');
            }
            
            if (!Schema::hasColumn('depannages', 'photos_rapport')) {
                $table->json('photos_rapport')->nullable()->after('rapport_technique');
            }
            
            if (!Schema::hasColumn('depannages', 'date_soumission_rapport')) {
                $table->timestamp('date_soumission_rapport')->nullable()->after('photos_rapport');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer dans l'ordre inverse
        Schema::table('depannages', function (Blueprint $table) {
            if (Schema::hasColumn('depannages', 'demande_id')) {
                $table->dropForeign(['demande_id']);
                $table->dropColumn('demande_id');
            }
            if (Schema::hasColumn('depannages', 'statut_operation')) {
                $table->dropColumn('statut_operation');
            }
            if (Schema::hasColumn('depannages', 'rapport_technique')) {
                $table->dropColumn('rapport_technique');
            }
            if (Schema::hasColumn('depannages', 'photos_rapport')) {
                $table->dropColumn('photos_rapport');
            }
            if (Schema::hasColumn('depannages', 'date_soumission_rapport')) {
                $table->dropColumn('date_soumission_rapport');
            }
        });
        
        Schema::dropIfExists('demandes');
        Schema::dropIfExists('assignments');
        
        Schema::table('utilisateurs', function (Blueprint $table) {
            if (Schema::hasColumn('utilisateurs', 'site_id')) {
                $table->dropForeign(['site_id']);
                $table->dropColumn('site_id');
            }
        });
        
        DB::table('utilisateurs')
            ->where('type_utilisateur', 'superviseur_client')
            ->update(['type_utilisateur' => 'superviseur']);
    }
};
