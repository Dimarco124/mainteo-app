<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('utilisateurs') && !Schema::hasColumn('utilisateurs', 'dark_mode')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->boolean('dark_mode')->default(false)->after('mot_de_passe');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('utilisateurs') && Schema::hasColumn('utilisateurs', 'dark_mode')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->dropColumn('dark_mode');
            });
        }
    }
};
