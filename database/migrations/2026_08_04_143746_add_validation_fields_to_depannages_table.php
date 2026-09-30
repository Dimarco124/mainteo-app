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
        Schema::table('depannages', function (Blueprint $table) {
            // Qui a créé l'intervention
            $table->enum('created_by_role', ['admin', 'superviseur'])->nullable()->after('type_intervention');
            
            // Statut de validation
            $table->enum('statut_validation_superviseur', ['en attente', 'approuvé', 'rejeté'])->default('en attente')->after('statut');
            $table->text('message_superviseur')->nullable()->after('statut_validation_superviseur');
            $table->timestamp('date_reponse_superviseur')->nullable()->after('message_superviseur');
            
            $table->enum('statut_validation_admin', ['en attente', 'approuvé', 'rejeté'])->default('en attente')->after('date_reponse_superviseur');
            $table->text('message_admin')->nullable()->after('statut_validation_admin');
            $table->timestamp('date_reponse_admin')->nullable()->after('message_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('depannages', function (Blueprint $table) {
            $table->dropColumn([
                'created_by_role',
                'statut_validation_superviseur',
                'message_superviseur',
                'date_reponse_superviseur',
                'statut_validation_admin',
                'message_admin',
                'date_reponse_admin',
            ]);
        });
    }
};
