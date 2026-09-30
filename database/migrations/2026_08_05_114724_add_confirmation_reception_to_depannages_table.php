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
            $table->enum('confirmation_reception', ['en attente', 'confirmé'])->default('en attente')->after('statut');
            $table->dateTime('date_confirmation_reception')->nullable()->after('confirmation_reception');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('depannages', function (Blueprint $table) {
            $table->dropColumn(['confirmation_reception', 'date_confirmation_reception']);
        });
    }
};
