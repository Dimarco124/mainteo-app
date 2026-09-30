<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Changer le type de puissance de FLOAT vers VARCHAR pour accepter du texte comme "7.5 kW"
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE equipements MODIFY puissance VARCHAR(50) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE equipements MODIFY puissance FLOAT NULL');
    }
};
