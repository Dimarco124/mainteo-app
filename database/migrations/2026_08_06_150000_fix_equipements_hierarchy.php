<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Correction de la hiérarchie des équipements
     * CLIENT → BASE → SITE → ÉQUIPEMENT
     * 
     * Actuellement site_id pointe vers bases (confusion!)
     * On ajoute base_id et on garde site_id pour pointer vers sites
     */
    public function up(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            // Ajouter base_id si elle n'existe pas
            if (!Schema::hasColumn('equipements', 'base_id')) {
                $table->integer('base_id')->nullable()->after('client_id');
            }
        });
        
        // Copier les valeurs actuelles de site_id vers base_id
        // (car actuellement site_id = base_id dans l'ancien système)
        \DB::statement('UPDATE equipements SET base_id = site_id WHERE base_id IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipements', function (Blueprint $table) {
            if (Schema::hasColumn('equipements', 'base_id')) {
                $table->dropColumn('base_id');
            }
        });
    }
};
