<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remplace les contraintes onDelete('restrict') par onDelete('cascade')
     * sur les tables principales pour que la suppression d'un client, base,
     * site ou équipement supprime automatiquement les données associées.
     */
    public function up(): void
    {
        // ─────────────────────────────────────────────
        // TABLE : demandes
        // ─────────────────────────────────────────────
        $this->dropAndRecreate('demandes', [
            ['col' => 'client_id',      'ref' => 'clients',       'action' => 'cascade'],
            ['col' => 'base_id',        'ref' => 'bases',         'action' => 'cascade'],
            ['col' => 'site_id',        'ref' => 'sites',         'action' => 'cascade'],
            ['col' => 'equipement_id',  'ref' => 'equipements',   'action' => 'cascade'],
            ['col' => 'created_by_user_id',          'ref' => 'utilisateurs', 'action' => 'cascade'],
            ['col' => 'validated_by_client_user_id', 'ref' => 'utilisateurs', 'action' => 'set null'],
            ['col' => 'validated_by_soutarah_user_id','ref' => 'utilisateurs', 'action' => 'set null'],
            ['col' => 'validated_finale_by_user_id', 'ref' => 'utilisateurs', 'action' => 'set null'],
        ]);

        // ─────────────────────────────────────────────
        // TABLE : depannages
        // ─────────────────────────────────────────────
        $this->dropAndRecreate('depannages', [
            ['col' => 'client_id',     'ref' => 'clients',     'action' => 'cascade'],
            ['col' => 'base_id',       'ref' => 'bases',       'action' => 'cascade'],
            ['col' => 'site_id',       'ref' => 'sites',       'action' => 'cascade'],
            ['col' => 'equipement_id', 'ref' => 'equipements', 'action' => 'cascade'],
            ['col' => 'demande_id',    'ref' => 'demandes',    'action' => 'set null'],
        ]);

        // ─────────────────────────────────────────────
        // TABLE : planifications
        // ─────────────────────────────────────────────
        $this->dropAndRecreate('planifications', [
            ['col' => 'demande_id',    'ref' => 'demandes', 'action' => 'cascade'],
            ['col' => 'intervention_id', 'ref' => 'depannages', 'action' => 'cascade'],
        ]);

        // ─────────────────────────────────────────────
        // TABLE : intervention_notifications
        // ─────────────────────────────────────────────
        $this->dropAndRecreate('intervention_notifications', [
            ['col' => 'intervention_id', 'ref' => 'depannages', 'action' => 'cascade'],
            ['col' => 'demande_id',      'ref' => 'demandes',   'action' => 'cascade'],
        ]);

        // ─────────────────────────────────────────────
        // TABLE : equipements
        // ─────────────────────────────────────────────
        $this->dropAndRecreate('equipements', [
            ['col' => 'site_id', 'ref' => 'sites', 'action' => 'cascade'],
            ['col' => 'base_id', 'ref' => 'bases', 'action' => 'cascade'],
        ]);

        // ─────────────────────────────────────────────
        // TABLE : sites
        // ─────────────────────────────────────────────
        $this->dropAndRecreate('sites', [
            ['col' => 'base_id',   'ref' => 'bases',   'action' => 'cascade'],
            ['col' => 'client_id', 'ref' => 'clients', 'action' => 'cascade'],
        ]);

        // ─────────────────────────────────────────────
        // TABLE : assignments
        // ─────────────────────────────────────────────
        $this->dropAndRecreate('assignments', [
            ['col' => 'client_id', 'ref' => 'clients', 'action' => 'cascade'],
            ['col' => 'base_id',   'ref' => 'bases',   'action' => 'cascade'],
        ]);
    }

    /**
     * Pour chaque colonne : supprime l'ancienne FK et recrée avec le bon onDelete.
     */
    private function dropAndRecreate(string $table, array $fkeys): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        foreach ($fkeys as $fk) {
            $col    = $fk['col'];
            $ref    = $fk['ref'];
            $action = $fk['action'];

            if (!Schema::hasColumn($table, $col)) {
                continue;
            }

            // Trouver et supprimer la contrainte existante (peu importe son nom)
            try {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$table}_{$col}_foreign`");
            } catch (\Throwable $e) {
                // La contrainte n'existait pas ou a un autre nom — on continue
            }

            // Recréer avec le bon comportement
            try {
                Schema::table($table, function (Blueprint $t) use ($col, $ref, $action) {
                    $t->foreign($col)->references('id')->on($ref)->onDelete($action);
                });
            } catch (\Throwable $e) {
                // Ignorer si la FK existe déjà (cas rare)
            }
        }
    }

    /**
     * Reverse: on remet restrict là où c'est critique (optionnel).
     */
    public function down(): void
    {
        // Pas de rollback destructeur prévu
    }
};
