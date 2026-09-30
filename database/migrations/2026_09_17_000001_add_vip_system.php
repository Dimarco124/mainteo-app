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
        // Ajouter le champ est_vip aux zones_sites si absent
        if (Schema::hasTable('zones_sites') && !Schema::hasColumn('zones_sites', 'est_vip')) {
            Schema::table('zones_sites', function (Blueprint $table) {
                $table->boolean('est_vip')->default(false)->after('nom_zone');
            });
        }

        // Ajouter le champ est_vip aux demandes si absent
        if (Schema::hasTable('demandes') && !Schema::hasColumn('demandes', 'est_vip')) {
            Schema::table('demandes', function (Blueprint $table) {
                $table->boolean('est_vip')->default(false)->after('statut');
            });
        }

        // Marquer automatiquement les zones VIP existantes
        $vip_keywords = [
            'directeur', 'dg', 'président', 'chef', 'cadre', 
            'manager', 'responsable', 'direction', 'général',
            'pdg', 'dga', 'drh', 'administrateur', 'gouverneur',
            'ministre', 'secrétaire général', 'coordonnateur'
        ];

        $zones = DB::table('zones_sites')->get();
        
        foreach ($zones as $zone) {
            $texte = strtolower($zone->nom_zone);
            $est_vip = false;
            
            foreach ($vip_keywords as $keyword) {
                if (stripos($texte, $keyword) !== false) {
                    $est_vip = true;
                    break;
                }
            }
            
            if ($est_vip) {
                DB::table('zones_sites')
                    ->where('id', $zone->id)
                    ->update(['est_vip' => true]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zones_sites', function (Blueprint $table) {
            $table->dropColumn('est_vip');
        });

        Schema::table('demandes', function (Blueprint $table) {
            $table->dropColumn('est_vip');
        });
    }
};
