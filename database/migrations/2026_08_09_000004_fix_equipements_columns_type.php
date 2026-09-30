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
        if (Schema::hasTable('equipements')) {
            try {
                DB::statement("ALTER TABLE `equipements` MODIFY `emplacement` VARCHAR(255) NULL DEFAULT NULL");
            } catch (\Throwable $e) {
                // Ignorer si déjà modifié
            }

            try {
                DB::statement("ALTER TABLE `equipements` MODIFY `equipement_numero` VARCHAR(50) NULL DEFAULT NULL");
            } catch (\Throwable $e) {
                // Ignorer si déjà modifié
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
