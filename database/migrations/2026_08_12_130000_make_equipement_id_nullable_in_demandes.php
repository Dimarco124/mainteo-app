<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier directement avec SQL brut pour éviter les problèmes de clés étrangères
        DB::statement('ALTER TABLE demandes MODIFY COLUMN equipement_id INT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre NOT NULL (attention : cela échouera s'il y a des NULL)
        DB::statement('ALTER TABLE demandes MODIFY COLUMN equipement_id INT UNSIGNED NOT NULL');
    }
};
