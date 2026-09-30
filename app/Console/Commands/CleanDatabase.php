<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CleanDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Supprimer toutes les données sauf le compte admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Nettoyage de la base de données...');
        
        try {
            // Récupérer l'admin avant de tout supprimer
            $admin = User::where('type_utilisateur', 'admin')->first();
            
            if (!$admin) {
                $this->error('❌ Aucun compte admin trouvé!');
                return 1;
            }

            $this->info("✅ Admin trouvé: {$admin->email}");

            // Désactiver les contraintes de clés étrangères
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Supprimer les données dans l'ordre correct (en respectant les dépendances)
            $this->info('🗑️  Suppression des notifications...');
            DB::table('intervention_notifications')->delete();

            $this->info('🗑️  Suppression des planifications...');
            DB::table('planifications')->delete();

            $this->info('🗑️  Suppression des dépannages/interventions...');
            DB::table('depannages')->delete();

            $this->info('🗑️  Suppression des demandes...');
            DB::table('demandes')->delete();

            $this->info('🗑️  Suppression des équipements...');
            DB::table('equipements')->delete();

            $this->info('🗑️  Suppression des sites...');
            DB::table('sites')->delete();

            $this->info('🗑️  Suppression des bases...');
            DB::table('bases')->delete();

            $this->info('🗑️  Suppression des clients/entreprises...');
            DB::table('clients')->delete();

            $this->info('🗑️  Suppression des affectations...');
            DB::table('assignments')->delete();

            $this->info('🗑️  Suppression de la table pivot demandeur_site...');
            DB::table('demandeur_site')->delete();

            $this->info('🗑️  Suppression de la table pivot equipe_user...');
            DB::table('equipe_user')->delete();

            $this->info('🗑️  Suppression des équipes...');
            DB::table('equipes')->delete();

            $this->info('🗑️  Suppression de tous les utilisateurs sauf admin...');
            User::where('type_utilisateur', '!=', 'admin')->delete();

            // Réactiver les contraintes de clés étrangères
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('');
            $this->info('✨ ================================================');
            $this->info('✅ Base de données nettoyée avec succès!');
            $this->info('👤 Compte admin conservé: ' . $admin->email);
            $this->info('✨ ================================================');
            $this->info('');

            return 0;

        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->error('❌ Erreur lors du nettoyage: ' . $e->getMessage());
            return 1;
        }
    }
}
