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
        Schema::create('demandeur_site', function (Blueprint $table) {
            $table->id();
            $table->integer('demandeur_id'); // INT(11) pour correspondre à utilisateurs.id
            $table->integer('site_id'); // INT(11) pour correspondre à sites.id
            $table->timestamps();

            // Foreign keys
            $table->foreign('demandeur_id')->references('id')->on('utilisateurs')->cascadeOnDelete();
            $table->foreign('site_id')->references('id')->on('sites')->cascadeOnDelete();

            // Éviter les doublons
            $table->unique(['demandeur_id', 'site_id']);
        });

        // Migrer les données existantes : si un demandeur a un site_id, créer l'entrée pivot
        DB::statement("
            INSERT INTO demandeur_site (demandeur_id, site_id, created_at, updated_at)
            SELECT id, site_id, NOW(), NOW()
            FROM utilisateurs
            WHERE type_utilisateur = 'demandeur' AND site_id IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandeur_site');
    }
};
