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
        Schema::table('demandes', function (Blueprint $table) {
            // Champs temporaires pour les demandes d'installation avant création de l'équipement
            $table->string('temp_equipement_nom')->nullable()->after('description');
            $table->string('temp_equipement_type')->nullable()->after('temp_equipement_nom');
            $table->string('temp_equipement_emplacement')->nullable()->after('temp_equipement_type');
            $table->boolean('equipement_needs_completion')->default(false)->after('temp_equipement_emplacement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropColumn([
                'temp_equipement_nom',
                'temp_equipement_type',
                'temp_equipement_emplacement',
                'equipement_needs_completion',
            ]);
        });
    }
};
