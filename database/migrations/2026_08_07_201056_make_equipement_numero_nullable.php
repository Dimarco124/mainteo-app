<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Rendre equipement_numero nullable car il n'est pas toujours fourni lors de la création
     */
    public function up(): void
    {
        // Modifier la colonne pour la rendre nullable
        DB::statement('ALTER TABLE equipements MODIFY equipement_numero VARCHAR(3) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre NOT NULL (attention: cela peut échouer si des valeurs NULL existent)
        DB::statement('ALTER TABLE equipements MODIFY equipement_numero VARCHAR(3) NOT NULL');
    }
};
