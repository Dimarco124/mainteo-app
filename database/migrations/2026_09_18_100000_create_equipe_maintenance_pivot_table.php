<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Créer la table pivot pour relation many-to-many entre maintenances et équipes
     */
    public function up(): void
    {
        if (!Schema::hasTable('equipe_maintenance')) {
            Schema::create('equipe_maintenance', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('maintenance_id');
                $table->unsignedBigInteger('equipe_id');
                $table->enum('role', ['principale', 'support', 'backup'])->default('support');
                $table->timestamp('date_affectation')->useCurrent();
                $table->timestamps();
                
                // Foreign keys avec cascade
                $table->foreign('maintenance_id')->references('id')->on('maintenances')->onDelete('cascade');
                $table->foreign('equipe_id')->references('id')->on('equipes')->onDelete('cascade');
                
                // Index pour les requêtes
                $table->index('maintenance_id');
                $table->index('equipe_id');
                $table->index('role');
                
                // Empêcher les doublons (une équipe ne peut être affectée qu'une fois à une maintenance)
                $table->unique(['maintenance_id', 'equipe_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('equipe_maintenance');
    }
};
