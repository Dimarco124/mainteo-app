<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE demandes MODIFY COLUMN statut_validation_finale_client ENUM('en_attente', 'conforme', 'validé', 'non_conforme') NOT NULL DEFAULT 'en_attente'");
        DB::statement("ALTER TABLE depannages MODIFY COLUMN statut_validation_finale_client ENUM('en_attente', 'conforme', 'validé', 'non_conforme') NOT NULL DEFAULT 'en_attente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE demandes MODIFY COLUMN statut_validation_finale_client ENUM('en_attente', 'conforme', 'non_conforme') NOT NULL DEFAULT 'en_attente'");
        DB::statement("ALTER TABLE depannages MODIFY COLUMN statut_validation_finale_client ENUM('en_attente', 'validé', 'non_conforme') NOT NULL DEFAULT 'en_attente'");
    }
};
