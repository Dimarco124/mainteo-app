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
        Schema::table('planifications', function (Blueprint $table) {
            // Vérifier si les colonnes n'existent pas déjà avant de les ajouter
            if (!Schema::hasColumn('planifications', 'demande_id')) {
                $table->bigInteger('demande_id')->unsigned()->nullable()->after('id');
            }
            
            if (!Schema::hasColumn('planifications', 'depannage_id')) {
                $table->bigInteger('depannage_id')->unsigned()->nullable()->after('demande_id');
            }
        });
        
        // Ajouter les index séparément
        Schema::table('planifications', function (Blueprint $table) {
            if (!Schema::hasColumn('planifications', 'demande_id')) {
                $table->index('demande_id');
            }
            if (!Schema::hasColumn('planifications', 'depannage_id')) {
                $table->index('depannage_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planifications', function (Blueprint $table) {
            $table->dropIndex(['demande_id']);
            $table->dropIndex(['depannage_id']);
            $table->dropColumn(['demande_id', 'depannage_id']);
        });
    }
};
