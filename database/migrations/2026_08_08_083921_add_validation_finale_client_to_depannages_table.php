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
            // Validation finale par le Superviseur Client
            $table->enum('statut_validation_finale_client', ['en_attente', 'validé', 'non_conforme'])->default('en_attente')->after('statut_rapport_technicien');
            $table->text('commentaire_validation_client')->nullable()->after('statut_validation_finale_client');
            $table->timestamp('date_validation_finale_client')->nullable()->after('commentaire_validation_client');
            $table->unsignedBigInteger('validated_by_client_user_id')->nullable()->after('date_validation_finale_client');
            
            // Clé étrangère - CORRIGÉE: référence 'utilisateurs' au lieu de 'users'
            $table->foreign('validated_by_client_user_id')->references('id')->on('utilisateurs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('depannages', function (Blueprint $table) {
            $table->dropForeign(['validated_by_client_user_id']);
            $table->dropColumn([
                'statut_validation_finale_client',
                'commentaire_validation_client',
                'date_validation_finale_client',
                'validated_by_client_user_id',
            ]);
        });
    }
};
