<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Supprimer complètement la colonne equipement_id
            if (Schema::hasColumn('maintenances', 'equipement_id')) {
                $table->dropColumn('equipement_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Recréer la colonne si on veut revenir en arrière
            $table->unsignedBigInteger('equipement_id')->nullable();
        });
    }
};
