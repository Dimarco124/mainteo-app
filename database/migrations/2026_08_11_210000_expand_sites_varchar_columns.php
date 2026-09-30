<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sites')) {
            return;
        }

        $targets = [
            'code_site'  => 'VARCHAR(50)',
            'nom_site'   => 'VARCHAR(255)',
            'adresse'    => 'VARCHAR(500)',
            'ville'      => 'VARCHAR(191)',
            'telephone'  => 'VARCHAR(50)',
            'base'       => 'VARCHAR(255)',
        ];

        foreach ($targets as $col => $type) {
            $colExists = false;
            $currentType = null;
            try {
                if (Schema::hasColumn('sites', $col)) {
                    $colExists = true;
                }
            } catch (\Throwable $e) {
                try {
                    $rows = DB::select('SHOW COLUMNS FROM sites LIKE ?', [$col]);
                    if (!empty($rows)) {
                        $colExists = true;
                        $currentType = $rows[0]->Type ?? null;
                    }
                } catch (\Throwable $e2) {
                    $colExists = false;
                }
            }
            if (!$colExists) {
                continue;
            }

            try {
                DB::statement("ALTER TABLE `sites` MODIFY `{$col}` {$type} NULL DEFAULT NULL");
            } catch (\Throwable $e) {
                // Ignore silencieusement : la colonne a déjà une taille suffisante,
                // ou la migration a déjà été appliquée, ou un index/unique empêche
                try {
                    DB::statement("ALTER TABLE `sites` CHANGE COLUMN `{$col}` `{$col}` {$type} NULL DEFAULT NULL");
                } catch (\Throwable $e2) {
                }
            }
        }

        try {
            DB::statement("ALTER TABLE `sites` ROW_FORMAT=DYNAMIC");
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
    }
};
