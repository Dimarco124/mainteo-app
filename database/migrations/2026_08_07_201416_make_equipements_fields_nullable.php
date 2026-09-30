<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Rendre nullable plusieurs champs qui ne sont pas toujours fournis lors de la création
     */
    public function up(): void
    {
        // Rendre nullable les champs qui n'ont pas toujours de valeur
        DB::statement('ALTER TABLE equipements MODIFY num_sur_site INT(11) NULL');
        DB::statement('ALTER TABLE equipements MODIFY puissance FLOAT NULL');
        DB::statement('ALTER TABLE equipements MODIFY num_serie VARCHAR(50) NULL');
        DB::statement('ALTER TABLE equipements MODIFY date_acquisition DATE NULL');
        DB::statement("ALTER TABLE equipements MODIFY emplacement ENUM('interne','externe') NULL DEFAULT 'interne'");
        DB::statement("ALTER TABLE equipements MODIFY type_unite ENUM('Exterieure','Interieure','Inconnue','') NULL DEFAULT 'Inconnue'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre NOT NULL (attention: peut échouer si des valeurs NULL existent)
        DB::statement('ALTER TABLE equipements MODIFY num_sur_site INT(11) NOT NULL');
        DB::statement('ALTER TABLE equipements MODIFY puissance FLOAT NOT NULL');
        DB::statement('ALTER TABLE equipements MODIFY num_serie VARCHAR(50) NOT NULL');
        DB::statement('ALTER TABLE equipements MODIFY date_acquisition DATE NOT NULL');
        DB::statement("ALTER TABLE equipements MODIFY emplacement ENUM('interne','externe') NOT NULL DEFAULT 'interne'");
        DB::statement("ALTER TABLE equipements MODIFY type_unite ENUM('Exterieure','Interieure','Inconnue','') NOT NULL");
    }
};
