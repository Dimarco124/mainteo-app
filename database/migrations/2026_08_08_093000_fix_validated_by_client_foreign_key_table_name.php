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
        try {
            DB::statement("ALTER TABLE depannages DROP FOREIGN KEY depannages_validated_by_client_user_id_foreign");
        } catch (\Throwable $e) {
            // Ignorer si la contrainte n'existait pas encore
        }

        try {
            Schema::table('depannages', function (Blueprint $table) {
                $table->foreign('validated_by_client_user_id')
                      ->references('id')
                      ->on('utilisateurs')
                      ->onDelete('set null');
            });
        } catch (\Throwable $e) {
            // Ignorer si la contrainte existe déjà
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('depannages', function (Blueprint $table) {
            // Supprimer la contrainte correcte
            $table->dropForeign(['validated_by_client_user_id']);
            
            // Remettre l'ancienne contrainte (même si elle était incorrecte)
            $table->foreign('validated_by_client_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};
