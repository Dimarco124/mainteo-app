<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('equipes:sync', function () {
    $techniciens = \App\Models\User::whereNotNull('equipe_id')
        ->whereIn('type_utilisateur', ['technicien', 'chef technicien'])
        ->get();

    $synced = 0;
    foreach ($techniciens as $tech) {
        $exists = \Illuminate\Support\Facades\DB::table('equipe_user')
            ->where('equipe_id', $tech->equipe_id)
            ->where('user_id', $tech->id)
            ->exists();

        if (!$exists) {
            $isChef = \App\Models\Equipe::where('id', $tech->equipe_id)
                ->where('chef_equipe', $tech->id)
                ->exists();
            $role = $isChef ? 'chef' : 'membre';

            \Illuminate\Support\Facades\DB::table('equipe_user')->insert([
                'equipe_id'   => $tech->equipe_id,
                'user_id'     => $tech->id,
                'role'        => $role,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $synced++;
        }
    }

    $this->info("Synchronisation réussie : {$synced} technicien(s) rattaché(s) à leur équipe.");
})->purpose('Synchroniser la table equipe_user avec les techniciens ayant equipe_id');
