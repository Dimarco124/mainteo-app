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
        if (Schema::hasTable('sites') && Schema::hasColumn('sites', 'base')) {
            try {
                DB::statement("ALTER TABLE `sites` MODIFY `base` VARCHAR(255) NULL DEFAULT NULL");
            } catch (\Throwable $e) {
                // Ignorer si la colonne n'existe pas ou est déjà nullable
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
