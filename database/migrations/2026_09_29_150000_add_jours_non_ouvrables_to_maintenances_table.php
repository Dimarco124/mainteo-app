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
        if (Schema::hasTable('maintenances')) {
            Schema::table('maintenances', function (Blueprint $table) {
                if (!Schema::hasColumn('maintenances', 'jours_non_ouvrables')) {
                    $table->json('jours_non_ouvrables')->nullable()->after('equipements_par_jour');
                }
                if (!Schema::hasColumn('maintenances', 'jours_ouvrables_count')) {
                    $table->integer('jours_ouvrables_count')->nullable()->after('jours_non_ouvrables');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('maintenances')) {
            Schema::table('maintenances', function (Blueprint $table) {
                if (Schema::hasColumn('maintenances', 'jours_non_ouvrables')) {
                    $table->dropColumn('jours_non_ouvrables');
                }
                if (Schema::hasColumn('maintenances', 'jours_ouvrables_count')) {
                    $table->dropColumn('jours_ouvrables_count');
                }
            });
        }
    }
};
