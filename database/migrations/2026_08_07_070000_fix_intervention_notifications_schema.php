<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('intervention_notifications', function (Blueprint $table) {
            // Renommer depannage_id en intervention_id
            if (Schema::hasColumn('intervention_notifications', 'depannage_id')) {
                $table->renameColumn('depannage_id', 'intervention_id');
            }
            
            // Ajouter statut si n'existe pas
            if (!Schema::hasColumn('intervention_notifications', 'statut')) {
                $table->string('statut')->default('non_lu')->after('message');
            }
            
            // Renommer 'lu' en 'read_at' si existe encore
            if (Schema::hasColumn('intervention_notifications', 'lu')) {
                $table->dropColumn('lu');
            }
            
            // Renommer date_creation en created_at et date_lecture en updated_at
            if (Schema::hasColumn('intervention_notifications', 'date_creation')) {
                $table->renameColumn('date_creation', 'created_at');
            }
            if (Schema::hasColumn('intervention_notifications', 'date_lecture')) {
                $table->renameColumn('date_lecture', 'updated_at');
            }
            
            // Supprimer titre si existe
            if (Schema::hasColumn('intervention_notifications', 'titre')) {
                $table->dropColumn('titre');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intervention_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('intervention_notifications', 'intervention_id')) {
                $table->renameColumn('intervention_id', 'depannage_id');
            }
            
            if (Schema::hasColumn('intervention_notifications', 'statut')) {
                $table->dropColumn('statut');
            }
            
            if (!Schema::hasColumn('intervention_notifications', 'lu')) {
                $table->boolean('lu')->default(false);
            }
            
            if (Schema::hasColumn('intervention_notifications', 'created_at')) {
                $table->renameColumn('created_at', 'date_creation');
            }
            if (Schema::hasColumn('intervention_notifications', 'updated_at')) {
                $table->renameColumn('updated_at', 'date_lecture');
            }
            
            if (!Schema::hasColumn('intervention_notifications', 'titre')) {
                $table->string('titre');
            }
        });
    }
};
