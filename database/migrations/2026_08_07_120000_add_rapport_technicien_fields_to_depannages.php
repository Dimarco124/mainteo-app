<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('depannages', function (Blueprint $table) {
            if (!Schema::hasColumn('depannages', 'photo_carnet_rapport')) {
                $table->string('photo_carnet_rapport')->nullable();
            }
            if (!Schema::hasColumn('depannages', 'photo_equipement_apres')) {
                $table->string('photo_equipement_apres')->nullable();
            }
            if (!Schema::hasColumn('depannages', 'date_rapport_technicien')) {
                $table->timestamp('date_rapport_technicien')->nullable();
            }
            if (!Schema::hasColumn('depannages', 'rapport_soumis_par_user_id')) {
                $table->unsignedBigInteger('rapport_soumis_par_user_id')->nullable();
            }
            if (!Schema::hasColumn('depannages', 'statut_rapport_technicien')) {
                $table->enum('statut_rapport_technicien', ['en_attente', 'soumis', 'transmis_client'])->default('en_attente');
            }
            if (!Schema::hasColumn('depannages', 'date_transmission_rapport_client')) {
                $table->timestamp('date_transmission_rapport_client')->nullable();
            }
            if (!Schema::hasColumn('depannages', 'rapport_transmis_par_user_id')) {
                $table->unsignedBigInteger('rapport_transmis_par_user_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('depannages', function (Blueprint $table) {
            $table->dropColumn([
                'photo_carnet_rapport',
                'photo_equipement_apres',
                'date_rapport_technicien',
                'rapport_soumis_par_user_id',
                'statut_rapport_technicien',
                'date_transmission_rapport_client',
                'rapport_transmis_par_user_id',
            ]);
        });
    }
};
