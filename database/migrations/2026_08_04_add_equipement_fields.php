<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            // Vérifier et ajouter les colonnes si elles n'existent pas
            if (!Schema::hasColumn('equipements', 'refrigerant')) {
                $table->string('refrigerant', 50)->nullable()->after('puissance');
            }
            if (!Schema::hasColumn('equipements', 'mois_installation')) {
                $table->integer('mois_installation')->nullable()->after('refrigerant');
            }
            if (!Schema::hasColumn('equipements', 'annee_installation')) {
                $table->integer('annee_installation')->nullable()->after('mois_installation');
            }
            if (!Schema::hasColumn('equipements', 'client_id')) {
                $table->unsignedBigInteger('client_id')->nullable()->after('site_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            $table->dropColumn(['refrigerant', 'mois_installation', 'annee_installation', 'client_id']);
        });
    }
};
