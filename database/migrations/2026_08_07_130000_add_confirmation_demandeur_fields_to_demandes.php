<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            if (!Schema::hasColumn('demandes', 'techniciens_sont_passes')) {
                $table->boolean('techniciens_sont_passes')->default(false);
            }
            if (!Schema::hasColumn('demandes', 'resultat_intervention')) {
                $table->enum('resultat_intervention', ['satisfaisant', 'non_satisfaisant', 'partiellement_satisfaisant'])->nullable();
            }
            if (!Schema::hasColumn('demandes', 'commentaire_resultat')) {
                $table->text('commentaire_resultat')->nullable();
            }
            if (!Schema::hasColumn('demandes', 'date_confirmation_passage')) {
                $table->timestamp('date_confirmation_passage')->nullable();
            }
            if (!Schema::hasColumn('demandes', 'statut_validation_finale_client')) {
                $table->enum('statut_validation_finale_client', ['en_attente', 'conforme', 'non_conforme'])->default('en_attente');
            }
            if (!Schema::hasColumn('demandes', 'message_non_conformite')) {
                $table->text('message_non_conformite')->nullable();
            }
            if (!Schema::hasColumn('demandes', 'date_cloture_finale')) {
                $table->timestamp('date_cloture_finale')->nullable();
            }
            if (!Schema::hasColumn('demandes', 'cloture_par_user_id')) {
                $table->unsignedBigInteger('cloture_par_user_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropColumn([
                'techniciens_sont_passes',
                'resultat_intervention',
                'commentaire_resultat',
                'date_confirmation_passage',
                'statut_validation_finale_client',
                'message_non_conformite',
                'date_cloture_finale',
                'cloture_par_user_id',
            ]);
        });
    }
};
