<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('maintenances') && !Schema::hasColumn('maintenances', 'equipements_par_jour')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->integer('equipements_par_jour')->default(8)->after('nombre_equipements_prevus');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('maintenances') && Schema::hasColumn('maintenances', 'equipements_par_jour')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->dropColumn('equipements_par_jour');
            });
        }
    }
};
