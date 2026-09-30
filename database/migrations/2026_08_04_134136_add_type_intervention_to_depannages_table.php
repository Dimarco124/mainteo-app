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
        Schema::table('depannages', function (Blueprint $table) {
            $table->enum('type_intervention', ['Dépannage', 'Maintenance', 'Installation'])
                  ->default('Dépannage')
                  ->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('depannages', function (Blueprint $table) {
            $table->dropColumn('type_intervention');
        });
    }
};
