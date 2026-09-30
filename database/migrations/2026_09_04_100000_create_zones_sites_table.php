<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Assurer que la table clients utilise le moteur InnoDB
        if (Schema::hasTable('clients')) {
            DB::statement("ALTER TABLE `clients` ENGINE=InnoDB");
        }

        // 1. Table zones_sites
        if (!Schema::hasTable('zones_sites')) {
            Schema::create('zones_sites', function (Blueprint $table) {
                $table->integer('id', true); // INT(11) AUTO_INCREMENT
                $table->integer('site_id');
                $table->integer('base_id')->nullable();
                $table->integer('client_id')->nullable();
                $table->string('nom_zone');
                $table->string('code_zone')->nullable();
                $table->text('observations')->nullable();
                $table->timestamps();

                $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
                $table->foreign('base_id')->references('id')->on('bases')->onDelete('cascade');
                $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            });
        }

        // 2. Colonne zone_id sur utilisateurs
        if (Schema::hasTable('utilisateurs') && !Schema::hasColumn('utilisateurs', 'zone_id')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->integer('zone_id')->nullable();
                $table->foreign('zone_id')->references('id')->on('zones_sites')->onDelete('set null');
            });
        }

        // 3. Colonne zone_id sur equipements
        if (Schema::hasTable('equipements') && !Schema::hasColumn('equipements', 'zone_id')) {
            Schema::table('equipements', function (Blueprint $table) {
                $table->integer('zone_id')->nullable();
                $table->foreign('zone_id')->references('id')->on('zones_sites')->onDelete('set null');
            });
        }

        // 4. Colonne zone_id sur demandes
        if (Schema::hasTable('demandes') && !Schema::hasColumn('demandes', 'zone_id')) {
            Schema::table('demandes', function (Blueprint $table) {
                $table->integer('zone_id')->nullable();
                $table->foreign('zone_id')->references('id')->on('zones_sites')->onDelete('set null');
            });
        }

        // 5. Colonne zone_id sur maintenances
        if (Schema::hasTable('maintenances') && !Schema::hasColumn('maintenances', 'zone_id')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->integer('zone_id')->nullable();
                $table->foreign('zone_id')->references('id')->on('zones_sites')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('maintenances') && Schema::hasColumn('maintenances', 'zone_id')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->dropForeign(['zone_id']);
                $table->dropColumn('zone_id');
            });
        }

        if (Schema::hasTable('demandes') && Schema::hasColumn('demandes', 'zone_id')) {
            Schema::table('demandes', function (Blueprint $table) {
                $table->dropForeign(['zone_id']);
                $table->dropColumn('zone_id');
            });
        }

        if (Schema::hasTable('equipements') && Schema::hasColumn('equipements', 'zone_id')) {
            Schema::table('equipements', function (Blueprint $table) {
                $table->dropForeign(['zone_id']);
                $table->dropColumn('zone_id');
            });
        }

        if (Schema::hasTable('utilisateurs') && Schema::hasColumn('utilisateurs', 'zone_id')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->dropForeign(['zone_id']);
                $table->dropColumn('zone_id');
            });
        }

        Schema::dropIfExists('zones_sites');
    }
};
