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
        Schema::create('equipe_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('equipe_id'); // int(11) pour correspondre à equipes.id
            $table->unsignedBigInteger('user_id'); // bigint pour correspondre à utilisateurs.id
            $table->string('role', 20)->default('membre'); // 'chef', 'membre'
            $table->timestamps();
            
            // Index pour performance
            $table->index('equipe_id');
            $table->index('user_id');
            
            // Index unique pour éviter les doublons
            $table->unique(['equipe_id', 'user_id'], 'equipe_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipe_user');
    }
};
