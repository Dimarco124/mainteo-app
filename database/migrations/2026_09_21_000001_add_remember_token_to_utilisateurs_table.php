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
        if (Schema::hasTable('utilisateurs') && !Schema::hasColumn('utilisateurs', 'remember_token')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->rememberToken()->after('mot_de_passe');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('utilisateurs') && Schema::hasColumn('utilisateurs', 'remember_token')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->dropRememberToken();
            });
        }
    }
};
