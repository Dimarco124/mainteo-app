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
        Schema::table('demandes', function (Blueprint $table) {
            $table->text('appel_notes')->nullable()->after('message_validation_soutarah');
            $table->foreignId('appel_effectue_par_user_id')->nullable()->constrained('users')->nullOnDelete()->after('appel_notes');
            $table->dateTime('appel_date')->nullable()->after('appel_effectue_par_user_id');
            $table->date('date_confirmee_avec_demandeur')->nullable()->after('appel_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropForeign(['appel_effectue_par_user_id']);
            $table->dropColumn([
                'appel_notes',
                'appel_effectue_par_user_id',
                'appel_date',
                'date_confirmee_avec_demandeur',
            ]);
        });
    }
};
