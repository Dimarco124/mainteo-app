<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            $rows = \Illuminate\Support\Facades\DB::select('SHOW COLUMNS FROM depannages LIKE ?', ['ri_soutarah']);
            $hasCol = !empty($rows);
        } catch (\Throwable $e) {
            $hasCol = false;
        }

        if (!$hasCol) {
            Schema::table('depannages', function (Blueprint $table) {
                $table->string('ri_soutarah')->nullable()->after('rapport');
            });
        }
    }

    public function down(): void
    {
        try {
            $rows = \Illuminate\Support\Facades\DB::select('SHOW COLUMNS FROM depannages LIKE ?', ['ri_soutarah']);
            $hasCol = !empty($rows);
        } catch (\Throwable $e) {
            $hasCol = true;
        }

        if ($hasCol) {
            Schema::table('depannages', function (Blueprint $table) {
                $table->dropColumn('ri_soutarah');
            });
        }
    }
};
