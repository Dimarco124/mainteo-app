<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('depannages')) {
            return;
        }

        // Convert the existing depannages table to InnoDB to support foreign keys.
        DB::statement('ALTER TABLE depannages ENGINE = InnoDB');

        Schema::table('depannages', function (Blueprint $table) {
            if (!Schema::hasColumn('depannages', 'demande_id')) {
                $table->unsignedBigInteger('demande_id')->nullable()->after('id');
                $table->foreign('demande_id')->references('id')->on('demandes')->onDelete('set null');
            }

            if (!Schema::hasColumn('depannages', 'statut_operation')) {
                $table->enum('statut_operation', [
                    'pending_assignment',
                    'assigned',
                    'in_progress',
                    'completed',
                    'report_submitted',
                    'rework_needed',
                    'closed',
                ])->default('pending_assignment')->after('statut');
            }

            if (!Schema::hasColumn('depannages', 'rapport_technique')) {
                $table->text('rapport_technique')->nullable()->after('rapport');
            }

            if (!Schema::hasColumn('depannages', 'photos_rapport')) {
                $table->json('photos_rapport')->nullable()->after('rapport_technique');
            }

            if (!Schema::hasColumn('depannages', 'date_soumission_rapport')) {
                $table->timestamp('date_soumission_rapport')->nullable()->after('photos_rapport');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('depannages')) {
            return;
        }

        Schema::table('depannages', function (Blueprint $table) {
            if (Schema::hasColumn('depannages', 'demande_id')) {
                $table->dropForeign(['demande_id']);
                $table->dropColumn('demande_id');
            }
            if (Schema::hasColumn('depannages', 'statut_operation')) {
                $table->dropColumn('statut_operation');
            }
            if (Schema::hasColumn('depannages', 'rapport_technique')) {
                $table->dropColumn('rapport_technique');
            }
            if (Schema::hasColumn('depannages', 'photos_rapport')) {
                $table->dropColumn('photos_rapport');
            }
            if (Schema::hasColumn('depannages', 'date_soumission_rapport')) {
                $table->dropColumn('date_soumission_rapport');
            }
        });
    }
};
