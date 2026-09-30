<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intervention_notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('maintenance_id')->nullable()->after('demande_id');
            // Pas de foreign key pour éviter les problèmes de type
        });
    }

    public function down(): void
    {
        Schema::table('intervention_notifications', function (Blueprint $table) {
            $table->dropColumn('maintenance_id');
        });
    }
};
