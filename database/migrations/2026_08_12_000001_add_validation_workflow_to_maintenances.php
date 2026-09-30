<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter les colonnes de validation si elles n'existent pas
        if (!Schema::hasColumn('maintenances', 'statut_rapport_technicien')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->enum('statut_rapport_technicien', ['en_attente', 'soumis', 'transmis_client', 'rejete'])
                    ->default('en_attente');
            });
        }
        
        if (!Schema::hasColumn('maintenances', 'date_transmission_rapport_client')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->timestamp('date_transmission_rapport_client')->nullable();
            });
        }
        
        if (!Schema::hasColumn('maintenances', 'rapport_transmis_par_user_id')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->unsignedBigInteger('rapport_transmis_par_user_id')->nullable();
            });
        }
        
        if (!Schema::hasColumn('maintenances', 'raison_rejet_rapport')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->text('raison_rejet_rapport')->nullable();
            });
        }
        
        if (!Schema::hasColumn('maintenances', 'date_rejet_rapport')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->timestamp('date_rejet_rapport')->nullable();
            });
        }
        
        if (!Schema::hasColumn('maintenances', 'statut_validation_client')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->enum('statut_validation_client', ['en_attente', 'validé', 'non_conforme'])
                    ->default('en_attente');
            });
        }
        
        if (!Schema::hasColumn('maintenances', 'commentaire_validation_client')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->text('commentaire_validation_client')->nullable();
            });
        }
        
        if (!Schema::hasColumn('maintenances', 'date_validation_client')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->timestamp('date_validation_client')->nullable();
            });
        }
        
        if (!Schema::hasColumn('maintenances', 'validated_by_client_user_id')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->unsignedBigInteger('validated_by_client_user_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn([
                'statut_rapport_technicien',
                'date_transmission_rapport_client',
                'rapport_transmis_par_user_id',
                'raison_rejet_rapport',
                'date_rejet_rapport',
                'statut_validation_client',
                'commentaire_validation_client',
                'date_validation_client',
                'validated_by_client_user_id',
            ]);
        });
    }
};
