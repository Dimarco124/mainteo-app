<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajouter le numéro de référence externe pour faire le lien avec les demandes manuelles (hors app)
     */
    public function up(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            // Numéro de référence externe (demande faite manuellement hors application)
            // Obligatoire et unique pour traçabilité et recherche
            $table->string('numero_reference_externe', 100)->nullable()->unique()->after('numero_demande');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropUnique(['numero_reference_externe']);
            $table->dropColumn('numero_reference_externe');
        });
    }
};
