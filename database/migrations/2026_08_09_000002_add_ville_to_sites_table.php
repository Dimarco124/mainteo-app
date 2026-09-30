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
        if (Schema::hasTable('sites')) {
            Schema::table('sites', function (Blueprint $table) {
                if (!Schema::hasColumn('sites', 'ville')) {
                    $table->string('ville', 100)->nullable()->after('adresse');
                }
                if (!Schema::hasColumn('sites', 'telephone')) {
                    $table->string('telephone', 50)->nullable()->after('ville');
                }
                if (!Schema::hasColumn('sites', 'observations')) {
                    $table->text('observations')->nullable()->after('telephone');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sites')) {
            Schema::table('sites', function (Blueprint $table) {
                if (Schema::hasColumn('sites', 'ville')) {
                    $table->dropColumn('ville');
                }
                if (Schema::hasColumn('sites', 'telephone')) {
                    $table->dropColumn('telephone');
                }
                if (Schema::hasColumn('sites', 'observations')) {
                    $table->dropColumn('observations');
                }
            });
        }
    }
};
