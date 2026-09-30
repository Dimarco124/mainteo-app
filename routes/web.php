<?php

use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BaseSiteController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\DepannageController;
use App\Http\Controllers\EquipementController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\FicheFroidController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\TechnicienController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ZoneSiteController;
use App\Http\Middleware\EnsureUserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ─── Page publique ────────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
});

// ─── Authentification ─────────────────────────────────────────────────────────
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle.login')->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ══════════════════════════════════════════════════════════════════════════════
// 📱 ROUTES MOBILE PWA
// ══════════════════════════════════════════════════════════════════════════════
// ─── Zone protégée (auth obligatoire) ────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // ══════════════════════════════════════════════════════════════════════════════
    // 📱 ROUTES MOBILE PWA
    // ══════════════════════════════════════════════════════════════════════════════
    Route::prefix('mobile')->name('mobile.')->group(function () {
        Route::get('/', [MobileController::class, 'index'])->name('index');

        Route::prefix('technicien')->name('technicien.')->middleware([EnsureUserRole::class . ':technicien,chef technicien'])->group(function () {
            Route::get('/dashboard',              [MobileController::class, 'technicienDashboard'])->name('dashboard');
            Route::get('/interventions',          [MobileController::class, 'technicienInterventions'])->name('interventions');
            Route::get('/interventions/{id}',     [MobileController::class, 'technicienInterventionDetails'])->name('interventions.details');
            Route::get('/planning',               [MobileController::class, 'technicienPlanning'])->name('planning');
            Route::get('/profil',                 [MobileController::class, 'technicienProfil'])->name('profil');
            Route::get('/statistiques',           [MobileController::class, 'technicienStatistiques'])->name('statistiques');
            Route::get('/comptes-rendus',         [MobileController::class, 'technicienComptesRendus'])->name('comptes-rendus');
            Route::get('/parametres',            [MobileController::class, 'technicienParametres'])->name('parametres');

            // Maintenances Mobile
            Route::get('/maintenances/{id}',                    [MobileController::class, 'technicienMaintenanceDetails'])->name('maintenances.details');
            Route::post('/maintenances/{maintenance}/demarrer', [MaintenanceController::class, 'demarrer'])->name('maintenances.demarrer');
            Route::get('/maintenances/{id}/rapport',            [MobileController::class, 'technicienMaintenanceRapport'])->name('maintenances.rapport');
            Route::post('/maintenances/{maintenance}/terminer', [MaintenanceController::class, 'terminer'])->name('maintenances.terminer');
        });
    });

    // ── Route centrale : redirige vers le bon dashboard selon le rôle ─────────
    Route::get('/dashboard', function () {
        $role = Auth::user()->type_utilisateur;
        return match (true) {
            $role === 'admin'                                        => redirect()->route('admin.dashboard'),
            $role === 'superviseur_client'                           => redirect()->route('superviseur.dashboard'),
            $role === 'superviseur_soutarah'                         => redirect()->route('superviseur_soutarah.dashboard'),
            $role === 'demandeur'                                    => redirect()->route('demandeur.dashboard'),
            in_array($role, ['technicien', 'chef technicien'])       => redirect()->route('technicien.dashboard'),
            default                                                  => redirect()->route('login')->with('error', 'Type de compte non reconnu.'),
        };
    })->name('dashboard');

    // ── 1. Tableaux de bord ────────────────────────────────────────────────────
    Route::get('/admin/dashboard',                [DashboardController::class, 'admin'])->name('admin.dashboard');
    Route::get('/superviseur/dashboard',          [DashboardController::class, 'superviseur'])->name('superviseur.dashboard');
    Route::get('/superviseur-soutarah/dashboard', [DashboardController::class, 'superviseurSoutarah'])->name('superviseur_soutarah.dashboard');
    Route::get('/demandeur/dashboard',            [DashboardController::class, 'demandeur'])->name('demandeur.dashboard');
    Route::get('/technicien/dashboard',           [DashboardController::class, 'technicien'])->name('technicien.dashboard');
    Route::get('/client/dashboard',               [DashboardController::class, 'client'])->name('client.dashboard');

    // ── Profil & Paramètres ── Tous les utilisateurs ──────────────────────────
    Route::get('/profile',                [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile',                [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password',       [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/settings',               [ProfileController::class, 'settings'])->name('settings.index');
    Route::post('/profile/theme',         [ProfileController::class, 'updateTheme'])->name('profile.theme');

    // ── 2. Interventions ── Tous les rôles (filtrées par contrôleur) ──────────
    Route::resource('depannages', DepannageController::class);

    // ═══════════════════════════════════════════════════════════════
    // PHASE 2 : NOUVEL ONGLET OPÉRATIONS / AFFECTATIONS
    // Sépare les "Demandes prêtes à dépêcher" des "Opérations déjà créées"
    // ═══════════════════════════════════════════════════════════════
    Route::middleware([EnsureUserRole::class . ':admin,superviseur_soutarah,superviseur_client'])->group(function () {
        Route::get('operations', [DepannageController::class, 'operationsIndex'])->name('operations.index');
        Route::get('operations/creer-depuis-demande/{demande}', [DepannageController::class, 'createOperationFromDemande'])->name('operations.createFromDemande');
        Route::post('operations/store-from-demande', [DepannageController::class, 'storeOperationFromDemande'])->name('operations.storeFromDemande');
    });

    Route::patch('depannages/{id}/assign', [DepannageController::class, 'assignTechnician'])->name('depannages.assign');
    Route::patch('depannages/{id}/status', [DepannageController::class, 'updateStatus'])->name('depannages.updateStatus');
    Route::patch('depannages/{id}/ri-soutarah', [DepannageController::class, 'updateRiSoutarah'])->name('depannages.updateRiSoutarah');

    // Page spécifique techniciens
    Route::get('technicien/interventions', [DepannageController::class, 'technicienInterventions'])
        ->middleware([EnsureUserRole::class . ':technicien,chef technicien'])
        ->name('technicien.interventions');

    // Routes de validation (Admin only - no legacy superviseur validation)
    Route::post('depannages/{id}/approve-admin', [DepannageController::class, 'approveAdmin'])->name('depannages.approveAdmin');
    Route::post('depannages/{id}/reject-admin', [DepannageController::class, 'rejectAdmin'])->name('depannages.rejectAdmin');

    // FEATURE 3: Confirmation de réception par le technicien
    Route::post('depannages/{id}/confirm-reception', [DepannageController::class, 'confirmReception'])->name('depannages.confirmReception');

    // ═══════════════════════════════════════════════════════════════
    // WORKFLOW DE CLÔTURE D'INTERVENTION
    // ═══════════════════════════════════════════════════════════════
    
    // Technicien soumet son rapport (2 photos)
    Route::post('depannages/{id}/soumettre-rapport', [DepannageController::class, 'soumettreRapport'])
        ->middleware([EnsureUserRole::class . ':technicien,chef technicien'])
        ->name('depannages.soumettreRapport');
    
    // NOUVELLE: Page formulaire rapport complet unifié (Texte + Photos)
    Route::get('depannages/{id}/rapport', [DepannageController::class, 'showRapportForm'])
        ->name('depannages.rapportForm');
    
    // NOUVELLE: Soumettre le rapport complet
    Route::post('depannages/{id}/rapport-complet', [DepannageController::class, 'soumettreRapportComplet'])
        ->name('depannages.soumettreRapportComplet');
    
    // Superviseur Soutarah/Admin transmet le rapport au superviseur client
    Route::post('depannages/{id}/transmettre-rapport-client', [DepannageController::class, 'transmettreRapportClient'])
        ->middleware([EnsureUserRole::class . ':admin,superviseur_soutarah'])
        ->name('depannages.transmettreRapportClient');

    // Superviseur Soutarah/Admin rejette le rapport du technicien (demande de correction)
    Route::post('depannages/{id}/rejeter-rapport-technicien', [DepannageController::class, 'rejeterRapportTechnicien'])
        ->middleware([EnsureUserRole::class . ':admin,superviseur_soutarah'])
        ->name('depannages.rejeterRapportTechnicien');
    
    // Superviseur Client valide et clôture l'intervention
    Route::post('depannages/{id}/cloturer', [DepannageController::class, 'cloturerIntervention'])
        ->middleware([EnsureUserRole::class . ':superviseur_client'])
        ->name('depannages.cloturer');

    // Superviseur Client refuse / rejette le rapport
    Route::post('depannages/{id}/rejeter-rapport-client', [DepannageController::class, 'rejeterRapportClient'])
        ->middleware([EnsureUserRole::class . ':superviseur_client,admin'])
        ->name('depannages.rejeterRapportClient');

    // FEATURE 4: Page dédiée aux comptes-rendus
    Route::get('comptes-rendus', [DepannageController::class, 'rapportsIndex'])
        ->middleware([EnsureUserRole::class . ':admin,superviseur,superviseur_soutarah,superviseur_client,technicien,chef technicien'])
        ->name('comptes-rendus.index');

    // ── 2B. DEMANDES D'INTERVENTION ── Nouveau workflow ──────────────────────
    Route::resource('demandes', DemandeController::class);
    
    // Routes séparées pour demandes standards et VIP (Admin, Superviseur Soutarah, Technicien uniquement)
    Route::get('/demandes-standards', [DemandeController::class, 'indexStandard'])->name('demandes.standard');
    Route::get('/demandes-vip', [DemandeController::class, 'indexVip'])->name('demandes.vip');

    // ── 2C. GESTION DES DEMANDEURS ── Superviseur Client uniquement ──────────
    Route::middleware([EnsureUserRole::class . ':superviseur_client'])->group(function () {
        Route::resource('demandeurs', \App\Http\Controllers\DemandeurController::class);
    });
    Route::post('demandes/{demande}/validate-client', [DemandeController::class, 'validateByClient'])->name('demandes.validateClient');
    Route::post('demandes/{demande}/reject-client', [DemandeController::class, 'rejectByClient'])->name('demandes.rejectClient');
    Route::post('demandes/{demande}/validate-soutarah', [DemandeController::class, 'validateBySoutarah'])->name('demandes.validateSoutarah');
    Route::post('demandes/{demande}/reject-soutarah', [DemandeController::class, 'rejectBySoutarah'])->name('demandes.rejectSoutarah');
    Route::post('demandes/{demande}/validate-installation', [DemandeController::class, 'validateInstallation'])->name('demandes.validateInstallation');
    Route::post('demandes/{demande}/reject-installation', [DemandeController::class, 'rejectInstallation'])->name('demandes.rejectInstallation');

    // PHASE 1: Appel téléphonique manuel (pas dans l'application)
    // Route::post('demandes/{demande}/enregistrer-appel', [DemandeController::class, 'enregistrerAppel'])->name('demandes.enregistrerAppel');

    // PHASE 5: Confirmation et validation finale
    Route::post('demandes/{demande}/confirm-by-demandeur', [DemandeController::class, 'confirmByDemandeur'])->name('demandes.confirmByDemandeur');
    Route::post('demandes/{demande}/validate-finale', [DemandeController::class, 'validateFinale'])->name('demandes.validateFinale');
    Route::post('demandes/{demande}/reject-finale', [DemandeController::class, 'rejectFinale'])->name('demandes.rejectFinale');

    // ═══════════════════════════════════════════════════════════════
    // WORKFLOW DE CLÔTURE - CÔTÉ DEMANDEUR ET SUPERVISEUR CLIENT
    // ═══════════════════════════════════════════════════════════════
    
    // Demandeur confirme le passage des techniciens et donne son avis
    Route::post('demandes/{demande}/confirmer-passage-techniciens', [DemandeController::class, 'confirmerPassageTechniciens'])
        ->middleware([EnsureUserRole::class . ':demandeur'])
        ->name('demandes.confirmerPassageTechniciens');
    
    // Superviseur Client valide la clôture (après comparaison des rapports)
    Route::post('demandes/{demande}/valider-cloture', [DemandeController::class, 'validerCloture'])
        ->middleware([EnsureUserRole::class . ':superviseur_client'])
        ->name('demandes.validerCloture');
    
    // Superviseur Client signale une non-conformité
    Route::post('demandes/{demande}/signaler-non-conformite', [DemandeController::class, 'signalerNonConformite'])
        ->middleware([EnsureUserRole::class . ':superviseur_client'])
        ->name('demandes.signalerNonConformite');

    // Créer opération technique depuis une demande
    Route::get('depannages/create-from-demande/{demande}', [DepannageController::class, 'createFromDemande'])->name('depannages.createFromDemande');

    // API pour compter dynamiquement les équipements d'un site, d'une base ou d'un client (hiérarchie complète)
    Route::get('/api/equipements/count', function (\Illuminate\Http\Request $request) {
        $zoneId   = $request->input('zone_id');
        $siteId   = $request->input('site_id');
        $baseId   = $request->input('base_id');
        $clientId = $request->input('client_id');

        $query = \App\Models\Equipement::query();

        if ($zoneId) {
            // Comptage par zone/emplacement précis
            $query->where('zone_id', $zoneId);

        } elseif ($siteId) {
            // Comptage par site précis
            $query->where('site_id', $siteId);

        } elseif ($baseId) {
            // Comptage pour une base = équipements directs de la base + équipements de ses sites
            $siteIds = \App\Models\Site::where('base_id', $baseId)->pluck('id');
            $query->where(function ($q) use ($baseId, $siteIds) {
                $q->where('base_id', $baseId)
                  ->orWhereIn('site_id', $siteIds);
            });

        } elseif ($clientId) {
            // Comptage pour un client = équipements directs + via ses bases + via ses sites
            $baseIds = \App\Models\BaseSite::where('client_id', $clientId)->pluck('id');
            $siteIds = \App\Models\Site::where('client_id', $clientId)
                ->orWhereIn('base_id', $baseIds)
                ->pluck('id');

            $query->where(function ($q) use ($clientId, $baseIds, $siteIds) {
                $q->where('client_id', $clientId)
                  ->orWhereIn('base_id', $baseIds)
                  ->orWhereIn('site_id', $siteIds);
            });

        } else {
            return response()->json(['count' => 0]);
        }

        return response()->json(['count' => $query->count()]);
    })->name('api.equipements.count');

    // API pour charger les zones/emplacements d'un site
    Route::get('/api/sites/{site}/zones', [ZoneSiteController::class, 'getZonesBySite'])->name('api.sites.zones');

    // API pour charger les équipements d'un site
    Route::get('/api/sites/{site}/equipements', function ($siteId) {
        return \App\Models\Equipement::where('site_id', $siteId)->get(['id', 'equipement_code', 'equipement_nom', 'zone_id']);
    })->name('api.sites.equipements');

    // API pour charger les équipements du client (entreprise directe sans sites)
    Route::get('/api/client/equipements', function () {
        $user = Auth::user();
        
        // Si l'utilisateur a un client_id (entreprise directe)
        if ($user->client_id) {
            return \App\Models\Equipement::where('client_id', $user->client_id)
                ->get(['id', 'equipement_code', 'equipement_nom', 'zone_id']);
        }
        
        return response()->json([]);
    })->name('api.client.equipements');

    // ── 3. Entreprises, Bases, Sites & Emplacements ── Admin + Superviseur Soutarah ─
    Route::get('entreprises-bases', [ClientController::class, 'indexCombined'])
        ->middleware([EnsureUserRole::class . ':admin,superviseur_soutarah'])
        ->name('clients.combined');

    // ── Clients (CRUD) ── Admin uniquement ─────────────────────────────────────
    Route::middleware([EnsureUserRole::class . ':admin'])->group(function () {
        // Routes classiques clients
        Route::resource('clients', ClientController::class);

        // ── 3B. Import/Export Excel ── Admin uniquement ───────────────────────────
        // Import Clients
        Route::get('imports/clients', [ImportController::class, 'importClientsForm'])->name('imports.clients');
        Route::post('imports/clients', [ImportController::class, 'importClients'])->name('imports.clients.upload');
        Route::get('imports/clients/template', [ImportController::class, 'downloadClientsTemplate'])->name('imports.clients.template');
        
        // Import Bases
        Route::get('imports/bases', [ImportController::class, 'importBasesForm'])->name('imports.bases');
        Route::post('imports/bases', [ImportController::class, 'importBases'])->name('imports.bases.upload');
        Route::get('imports/bases/template', [ImportController::class, 'downloadBasesTemplate'])->name('imports.bases.template');
        
        // Import Sites
        Route::get('imports/sites', [ImportController::class, 'importSitesForm'])->name('imports.sites');
        Route::post('imports/sites', [ImportController::class, 'importSites'])->name('imports.sites.upload');
        Route::get('imports/sites/template', [ImportController::class, 'downloadSitesTemplate'])->name('imports.sites.template');

        // Import Emplacements (Sous-Sites)
        Route::get('imports/emplacements', [ImportController::class, 'importEmplacementsForm'])->name('imports.emplacements');
        Route::post('imports/emplacements', [ImportController::class, 'importEmplacements'])->name('imports.emplacements.upload');
        Route::get('imports/emplacements/template', [ImportController::class, 'downloadEmplacementsTemplate'])->name('imports.emplacements.template');
        
        // Import Équipements
        Route::get('imports/equipements', [ImportController::class, 'importEquipementsForm'])->name('imports.equipements');
        Route::post('imports/equipements', [ImportController::class, 'importEquipements'])->name('imports.equipements.upload');
        Route::get('imports/equipements/template', [ImportController::class, 'downloadEquipementsTemplate'])->name('imports.equipements.template');
        Route::get('imports/equipements/export-existing', [ImportController::class, 'exportExistingEquipements'])->name('imports.equipements.export-existing');
    });

    // ── 4B. Sites ── Lecture seule pour Soutarah, CRUD pour Admin + Superviseur Client ─────────────────────
    // Lecture seule (index + show) : Admin + Sup Client + Sup Soutarah
    Route::middleware([EnsureUserRole::class . ':admin,superviseur_client,superviseur_soutarah'])->group(function () {
        Route::get('sites',         [SiteController::class, 'index'])->name('sites.index');
        Route::get('sites/{site}',  [SiteController::class, 'show'])->name('sites.show');
    });
    // CRUD complet : Admin + Superviseur Client uniquement
    Route::middleware([EnsureUserRole::class . ':admin,superviseur_client'])->group(function () {
        Route::get('sites/create',           [SiteController::class, 'create'])->name('sites.create');
        Route::post('sites',                 [SiteController::class, 'store'])->name('sites.store');
        Route::get('sites/{site}/edit',      [SiteController::class, 'edit'])->name('sites.edit');
        Route::put('sites/{site}',           [SiteController::class, 'update'])->name('sites.update');
        Route::delete('sites/{site}',        [SiteController::class, 'destroy'])->name('sites.destroy');
    });

    Route::middleware([EnsureUserRole::class . ':admin,superviseur_soutarah,superviseur_client,demandeur'])->group(function () {
        Route::resource('zones', ZoneSiteController::class);
    });
    
    // Admin uniquement pour bases
    Route::middleware([EnsureUserRole::class . ':admin'])->group(function () {
        Route::resource('bases-sites', BaseSiteController::class);
    });

    // ── 5. Équipements ── Admin + Superviseur Soutarah + Superviseur Client + Techniciens ───────────────────
    // Routes spéciales pour la création simplifiée et la complétion d'équipements (AVANT le resource)
    Route::post('/equipements/store-simple', [EquipementController::class, 'storeSimple'])
        ->middleware([EnsureUserRole::class . ':superviseur_client'])
        ->name('equipements.storeSimple');
    
    Route::get('/equipements/complete/{demande}', [EquipementController::class, 'completeForm'])
        ->middleware([EnsureUserRole::class . ':admin,superviseur_soutarah'])
        ->name('equipements.completeForm');
    
    Route::post('/equipements/complete/{demande}', [EquipementController::class, 'completeStore'])
        ->middleware([EnsureUserRole::class . ':admin,superviseur_soutarah'])
        ->name('equipements.completeStore');

    // Superviseur Client voit uniquement les équipements de SA base (filtré dans controller)
    // Superviseur Soutarah voit uniquement les équipements de SA base/client assigné(e) (filtré dans controller)
    Route::middleware([EnsureUserRole::class . ':admin,superviseur_soutarah,superviseur_client,technicien,chef technicien'])
        ->resource('equipements', EquipementController::class);

    // API pour récupérer les bases d'un client
    Route::get('/api/clients/{clientId}/bases', [EquipementController::class, 'getBasesByClient'])
        ->middleware([EnsureUserRole::class . ':admin,superviseur_soutarah,superviseur_client'])
        ->name('api.clients.bases');

    // API pour récupérer les sites directs d'un client (sans base)
    Route::get('/api/clients/{clientId}/sites-directs', [EquipementController::class, 'getSitesDirectsByClient'])
        ->middleware([EnsureUserRole::class . ':admin,superviseur_soutarah,superviseur_client'])
        ->name('api.clients.sitesDirects');

    // API pour récupérer les équipements d'un client
    Route::get('/api/clients/{clientId}/equipements', [EquipementController::class, 'getEquipementsByClient'])
        ->middleware([EnsureUserRole::class . ':admin,superviseur_soutarah,superviseur_client'])
        ->name('api.clients.equipements');
    
    // API pour récupérer les sites d'une base
    Route::get('/api/bases/{baseId}/sites', [EquipementController::class, 'getSitesByBase'])
        ->middleware([EnsureUserRole::class . ':admin,superviseur_soutarah,superviseur_client'])
        ->name('api.bases.sites');

    // API pour récupérer les équipements d'une base
    Route::get('/api/bases/{baseId}/equipements', [EquipementController::class, 'getEquipementsByBase'])
        ->middleware([EnsureUserRole::class . ':admin,superviseur_soutarah,superviseur_client'])
        ->name('api.bases.equipements');

    // API pour récupérer tous les sites d'un client (avec ou sans base)
    Route::get('/api/clients/{clientId}/sites', [EquipementController::class, 'getClientSitesAll'])
        ->middleware([EnsureUserRole::class . ':admin,superviseur_soutarah,superviseur_client'])
        ->name('api.clients.sites');

    // ── 6. Utilisateurs ── Admin uniquement ───────────────────────────────────
    Route::middleware([EnsureUserRole::class . ':admin'])
        ->resource('utilisateurs', UserController::class);
    
    // ── 6.1 Assignation de sites aux demandeurs ── Admin + Superviseur Client ──
    Route::middleware([EnsureUserRole::class . ':admin,superviseur_client'])->group(function () {
        Route::get('demandeurs/{demandeur}/sites/edit',   [\App\Http\Controllers\DemandeurSiteController::class, 'edit'])->name('demandeurs.sites.edit');
        Route::put('demandeurs/{demandeur}/sites',        [\App\Http\Controllers\DemandeurSiteController::class, 'update'])->name('demandeurs.sites.update');
        Route::get('api/demandeurs/{demandeur}/sites',    [\App\Http\Controllers\DemandeurSiteController::class, 'getSites'])->name('api.demandeurs.sites');
    });

    // ── 7. Équipes ── Admin + Superviseur Soutarah (lecture seule) ────────────────────────────────────────
    // Admin : CRUD complet
    // Superviseur Soutarah : Lecture seule (index, show) - Les autres méthodes sont protégées dans le controller
    Route::middleware([EnsureUserRole::class . ':admin,superviseur_soutarah'])->group(function () {
        Route::resource('equipes', EquipeController::class);
        Route::post('equipes/{id}/add-member',                       [EquipeController::class, 'addMember'])->name('equipes.addMember');
        Route::delete('equipes/{equipe_id}/remove-member/{user_id}', [EquipeController::class, 'removeMember'])->name('equipes.removeMember');
        Route::patch('equipes/{equipe_id}/set-chef/{user_id}',       [EquipeController::class, 'setChef'])->name('equipes.setChef');
        Route::patch('equipes/{equipe_id}/unset-chef',               [EquipeController::class, 'unsetChef'])->name('equipes.unsetChef');
    });
    
    // Techniciens (Admin uniquement)
    Route::middleware([EnsureUserRole::class . ':admin'])->group(function () {
        Route::resource('techniciens', TechnicienController::class);
    });

    // ── 7B. Assignments Superviseurs Soutarah ── Admin uniquement ─────────────
    Route::middleware([EnsureUserRole::class . ':admin'])->group(function () {
        Route::get('assignments', [AssignmentController::class, 'index'])->name('assignments.index');
        Route::post('assignments', [AssignmentController::class, 'store'])->name('assignments.store');
        Route::put('assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
        Route::delete('assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
    });

    // ── 8. Planning ── Admin + Superviseur Soutarah + Superviseur Client + Techniciens + Demandeur ─────────────
    // Superviseur Client & Demandeur : LECTURE SEULE (voir les planifications de leur périmètre)
    Route::middleware([EnsureUserRole::class . ':admin,superviseur_soutarah,superviseur_client,technicien,chef technicien,demandeur'])->group(function () {
        Route::get('planning',              [PlanningController::class, 'index'])->name('planning.index');
        Route::get('planning/events-json',  [PlanningController::class, 'eventsJson'])->name('planning.events');
        Route::get('planning/export-pdf',   [PlanningController::class, 'exportPdf'])->name('planning.export-pdf');
        Route::get('planning/print',        [PlanningController::class, 'printView'])->name('planning.print');
        // API endpoint pour obtenir le nombre d'équipements sur un site
        Route::get('api/sites/{siteId}/equipment-count', [PlanningController::class, 'getSiteEquipmentCount'])->name('api.sites.equipment-count');
    });
    
    // Actions de création/modification : Admin + Superviseur Soutarah uniquement
    Route::middleware([EnsureUserRole::class . ':admin,superviseur_soutarah'])->group(function () {
        Route::get('planning/create',       [PlanningController::class, 'create'])->name('planning.create');
        Route::post('planning',             [PlanningController::class, 'store'])->name('planning.store');
        Route::patch('planning/{id}/date',  [PlanningController::class, 'updateDate'])->name('planning.updateDate');
        Route::delete('planning/{id}',      [PlanningController::class, 'destroy'])->name('planning.destroy');
    });

    // ── Maintenances ──────────────────────────────────────────────────────────────
    // Pas d'index séparé, tout se passe dans Planning
    Route::middleware([EnsureUserRole::class . ':admin,superviseur_soutarah'])->group(function () {
        Route::get('maintenances/create',               [MaintenanceController::class, 'create'])->name('maintenances.create');
        Route::post('maintenances',                     [MaintenanceController::class, 'store'])->name('maintenances.store');
        Route::get('maintenances/{maintenance}/edit',   [MaintenanceController::class, 'edit'])->name('maintenances.edit');
        Route::put('maintenances/{maintenance}',        [MaintenanceController::class, 'update'])->name('maintenances.update');
        Route::delete('maintenances/{maintenance}',     [MaintenanceController::class, 'destroy'])->name('maintenances.destroy');
        Route::post('maintenances/{maintenance}/equipes', [MaintenanceController::class, 'addEquipe'])->name('maintenances.equipes.add');
        Route::delete('maintenances/{maintenance}/equipes/{equipe}', [MaintenanceController::class, 'removeEquipe'])->name('maintenances.equipes.remove');
    });
    
    // Détail de la maintenance (tous les rôles concernés, y compris demandeur)
    Route::middleware([EnsureUserRole::class . ':admin,superviseur_soutarah,superviseur_client,technicien,chef technicien,demandeur'])->group(function () {
        Route::get('maintenances/{maintenance}',        [MaintenanceController::class, 'show'])->name('maintenances.show');
    });
    
    // Confirmation par superviseur client
    Route::middleware([EnsureUserRole::class . ':superviseur_client'])->group(function () {
        Route::post('maintenances/{maintenance}/confirmer', [MaintenanceController::class, 'confirmerReception'])->name('maintenances.confirmer');
    });
    
    // Actions techniciens
    Route::middleware([EnsureUserRole::class . ':technicien,chef technicien'])->group(function () {
        Route::post('maintenances/{maintenance}/demarrer', [MaintenanceController::class, 'demarrer'])->name('maintenances.demarrer');
        Route::get('maintenances/{maintenance}/rapport',    [MaintenanceController::class, 'rapportForm'])->name('maintenances.rapportForm');
        Route::post('maintenances/{maintenance}/terminer',  [MaintenanceController::class, 'terminer'])->name('maintenances.terminer');
    });

    // Actions Superviseur Soutarah - Validation rapports
    Route::middleware([EnsureUserRole::class . ':admin,superviseur_soutarah'])->group(function () {
        Route::post('maintenances/{maintenance}/transmettre-client', [MaintenanceController::class, 'transmettreRapportClient'])->name('maintenances.transmettreRapportClient');
        Route::post('maintenances/{maintenance}/rejeter-rapport', [MaintenanceController::class, 'rejeterRapportTechnicien'])->name('maintenances.rejeterRapportTechnicien');
    });

    // Actions Superviseur Client - Validation finale
    Route::middleware([EnsureUserRole::class . ':superviseur_client'])->group(function () {
        Route::post('maintenances/{maintenance}/valider-client', [MaintenanceController::class, 'validerParClient'])->name('maintenances.validerParClient');
        Route::post('maintenances/{maintenance}/rejeter-client', [MaintenanceController::class, 'rejeterParClient'])->name('maintenances.rejeterParClient');
    });

    // ── Comptes Rendus Journaliers ── Admin + Sup. Soutarah + Techniciens + Demandeur ─────────
    Route::middleware([EnsureUserRole::class . ':admin,superviseur_soutarah,superviseur_client,technicien,chef technicien,demandeur'])->group(function () {
        Route::get('maintenances/{maintenance}/comptes-rendus/create', [\App\Http\Controllers\CompteRenduJournalierController::class, 'create'])->name('comptes-rendus.create');
        Route::post('maintenances/{maintenance}/comptes-rendus', [\App\Http\Controllers\CompteRenduJournalierController::class, 'store'])->name('comptes-rendus.store');
        Route::get('comptes-rendus/{id}', [\App\Http\Controllers\CompteRenduJournalierController::class, 'show'])->name('comptes-rendus.show');
        Route::delete('comptes-rendus/{id}', [\App\Http\Controllers\CompteRenduJournalierController::class, 'destroy'])->name('comptes-rendus.destroy');
    });

    // ── KPIs Performance Équipes ── Admin + Superviseur Soutarah UNIQUEMENT ────────
    // Le superviseur_client n'a pas accès : données internes Soutarah confidentielles
    Route::middleware([EnsureUserRole::class . ':admin,superviseur_soutarah'])->group(function () {
        Route::get('kpis/equipes', [\App\Http\Controllers\KPIPerformanceController::class, 'index'])->name('kpis.equipes');
    });


    // ── 9. Rapports & Fiches Froid ── Admin + Superviseur Soutarah + Superviseur Client + Techniciens ───────
    // Superviseur Soutarah voit uniquement les rapports de SA base/client assigné(e) (filtré dans controller)
    // Superviseur Client voit uniquement les rapports de SA base (filtré dans controller)
    Route::middleware([EnsureUserRole::class . ':admin,superviseur_soutarah,superviseur_client,technicien,chef technicien'])->group(function () {
        Route::get('rapports', [RapportController::class, 'index'])->name('rapports.index');
        Route::resource('fiches-froid', FicheFroidController::class);
    });

    // ── 10. Notifications ── Accessible à tous les utilisateurs connectés ──
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{id}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('notifications/{id}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::get('notifications/check-new', [\App\Http\Controllers\NotificationController::class, 'checkNew'])->name('notifications.checkNew');

    // ── 11. Mainteo IA ── Assistant conversationnel pour tous les rôles ────────
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/',                           [AiAssistantController::class, 'index'])->name('index');
        Route::get('conversations',               [AiAssistantController::class, 'getConversations'])->name('conversations.index');
        Route::get('conversations/{id}',          [AiAssistantController::class, 'getMessages'])->name('conversations.show');
        Route::post('chat',                       [AiAssistantController::class, 'sendMessage'])->name('chat');
        Route::delete('conversations/{id}',       [AiAssistantController::class, 'deleteConversation'])->name('conversations.destroy');
    });
});

