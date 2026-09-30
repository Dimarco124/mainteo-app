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
        Schema::create('intervention_notifications', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id'); // Destinataire
            $table->integer('depannage_id'); // Intervention concernée
            $table->string('type'); // 'demande_validation_superviseur', 'demande_validation_admin', 'intervention_approuvee', 'intervention_rejetee'
            $table->string('titre');
            $table->text('message');
            $table->boolean('lu')->default(false);
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_lecture')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intervention_notifications');
    }
};
