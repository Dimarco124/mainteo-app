<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Supprimer la contrainte de clé étrangère existante si elle existe
        try {
            Schema::table('depannages', function (Blueprint $table) {
                $table->dropForeign(['validated_by_client_user_id']);
            });
        } catch (\Exception $e) {
            // Ignorer si la contrainte n'existe pas
        }

        // Nettoyer les valeurs invalides (mettre NULL si l'utilisateur n'existe pas)
        DB::statement("
            UPDATE depannages 
            SET validated_by_client_user_id = NULL 
            WHERE validated_by_client_user_id IS NOT NULL 
            AND validated_by_client_user_id NOT IN (SELECT id FROM users)
        ");

        // Recréer la contrainte de clé étrangère correctement
        Schema::table('depannages', function (Blueprint $table) {
            $table->foreign('validated_by_client_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('depannages', function (Blueprint $table) {
            $table->dropForeign(['validated_by_client_user_id']);
        });
    }
};
