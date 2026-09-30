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
        // Mettre à jour l'ENUM pour inclure uniquement les nouveaux rôles (sans legacy)
        DB::statement("ALTER TABLE depannages MODIFY COLUMN created_by_role ENUM('admin', 'superviseur_client', 'superviseur_soutarah', 'technicien', 'demandeur') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'ancien ENUM
        DB::statement("ALTER TABLE depannages MODIFY COLUMN created_by_role ENUM('admin', 'superviseur') NULL");
    }
};
