<?php

namespace App\Http\Controllers;

use App\Models\BaseSite;
use App\Models\Client;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with(['bases', 'utilisateurs']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('ville', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('nom', 'asc')->paginate(15);
        $totalClients = Client::count();

        return view('clients.index', compact('clients', 'totalClients'));
    }

    /**
     * Page combinée Entreprises & Bases & Sites
     */
    public function indexCombined(Request $request)
    {
        $user = Auth::user();
        $view = $request->input('view', 'entreprises'); // 'entreprises', 'bases', 'sites', ou 'emplacements'
        
        $isSoutarah = $user && $user->type_utilisateur === 'superviseur_soutarah';
        $soutarahAssignment = $isSoutarah ? \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first() : null;

        // Données pour l'onglet Entreprises
        $queryClients = Client::with(['bases', 'utilisateurs']);
        if ($isSoutarah) {
            if ($soutarahAssignment && $soutarahAssignment->base_id) {
                $queryClients->whereHas('bases', fn($b) => $b->where('id', $soutarahAssignment->base_id));
            } elseif ($soutarahAssignment && $soutarahAssignment->client_id) {
                $queryClients->where('id', $soutarahAssignment->client_id);
            } else {
                $queryClients->whereRaw('1 = 0');
            }
        }
        if ($search = $request->input('search')) {
            $queryClients->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('ville', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }
        $clients = $queryClients->orderBy('nom', 'asc')->paginate(15, ['*'], 'clients_page');
        $totalClients = $isSoutarah ? (clone $queryClients)->count() : Client::count();
        
        // Données pour l'onglet Bases
        $queryBases = \App\Models\BaseSite::with('client', 'equipements', 'sites');
        if ($isSoutarah) {
            if ($soutarahAssignment && $soutarahAssignment->base_id) {
                $queryBases->where('id', $soutarahAssignment->base_id);
            } elseif ($soutarahAssignment && $soutarahAssignment->client_id) {
                $queryBases->where('client_id', $soutarahAssignment->client_id);
            } else {
                $queryBases->whereRaw('1 = 0');
            }
        }
        if ($view === 'bases' && ($search = $request->input('search'))) {
            $queryBases->where(function ($q) use ($search) {
                $q->where('nom_base', 'like', "%{$search}%")
                  ->orWhere('code_base', 'like', "%{$search}%")
                  ->orWhereHas('client', function($subQ) use ($search) {
                      $subQ->where('nom', 'like', "%{$search}%");
                  });
            });
        }
        if ($view === 'bases' && ($clientId = $request->input('client_id'))) {
            $queryBases->where('client_id', $clientId);
        }
        $bases = $queryBases->orderBy('nom_base', 'asc')->paginate(15, ['*'], 'bases_page');
        $totalBases = $isSoutarah ? (clone $queryBases)->count() : \App\Models\BaseSite::count();
        
        // Données pour l'onglet Sites
        $querySites = \App\Models\Site::with(['baseSite.client', 'equipements']);
        if ($isSoutarah) {
            if ($soutarahAssignment && $soutarahAssignment->base_id) {
                $querySites->where('base_id', $soutarahAssignment->base_id);
            } elseif ($soutarahAssignment && $soutarahAssignment->client_id) {
                $querySites->where(function($q) use ($soutarahAssignment) {
                    $q->where('client_id', $soutarahAssignment->client_id)
                      ->orWhereHas('baseSite', fn($b) => $b->where('client_id', $soutarahAssignment->client_id));
                });
            } else {
                $querySites->whereRaw('1 = 0');
            }
        }
        if ($view === 'sites' && ($search = $request->input('search'))) {
            $querySites->where(function ($q) use ($search) {
                $q->where('nom_site', 'like', "%{$search}%")
                  ->orWhere('code_site', 'like', "%{$search}%")
                  ->orWhere('ville', 'like', "%{$search}%");
            });
        }
        if ($view === 'sites' && ($baseId = $request->input('base_id'))) {
            $querySites->where('base_id', $baseId);
        }
        if ($view === 'sites' && ($clientId = $request->input('client_id'))) {
            $querySites->where(function ($q) use ($clientId) {
                $q->where('client_id', $clientId)
                  ->orWhereHas('baseSite', function ($subQ) use ($clientId) {
                      $subQ->where('client_id', $clientId);
                  });
            });
        }
        $sites = $querySites->orderBy('nom_site', 'asc')->paginate(15, ['*'], 'sites_page');
        $totalSites = $isSoutarah ? (clone $querySites)->count() : \App\Models\Site::count();
        
        // Données pour l'onglet Emplacements
        $queryEmplacements = \App\Models\ZoneSite::with(['site', 'baseSite', 'client', 'equipements']);
        if ($isSoutarah) {
            if ($soutarahAssignment && $soutarahAssignment->base_id) {
                $queryEmplacements->where(function($q) use ($soutarahAssignment) {
                    $q->where('base_id', $soutarahAssignment->base_id)
                      ->orWhereHas('site', fn($sq) => $sq->where('base_id', $soutarahAssignment->base_id));
                });
            } elseif ($soutarahAssignment && $soutarahAssignment->client_id) {
                $queryEmplacements->where(function($q) use ($soutarahAssignment) {
                    $q->where('client_id', $soutarahAssignment->client_id)
                      ->orWhereHas('site', function($sq) use ($soutarahAssignment) {
                          $sq->where('client_id', $soutarahAssignment->client_id)
                            ->orWhereHas('baseSite', fn($b) => $b->where('client_id', $soutarahAssignment->client_id));
                      });
                });
            } else {
                $queryEmplacements->whereRaw('1 = 0');
            }
        }
        $baseEmplacementScope = clone $queryEmplacements;

        if ($view === 'emplacements' && ($search = $request->input('search'))) {
            $queryEmplacements->where(function ($q) use ($search) {
                $q->where('nom_zone', 'like', "%{$search}%")
                  ->orWhere('code_zone', 'like', "%{$search}%")
                  ->orWhere('observations', 'like', "%{$search}%");
            });
        }
        if ($view === 'emplacements' && ($siteId = $request->input('site_id'))) {
            $queryEmplacements->where('site_id', $siteId);
        }
        if ($view === 'emplacements' && ($baseId = $request->input('base_id'))) {
            $queryEmplacements->where('base_id', $baseId);
        }
        if ($view === 'emplacements' && ($clientId = $request->input('client_id'))) {
            $queryEmplacements->where('client_id', $clientId);
        }
        $emplacements = $queryEmplacements->orderBy('nom_zone', 'asc')->paginate(15, ['*'], 'emplacements_page');
        $totalEmplacements = $isSoutarah ? $baseEmplacementScope->count() : \App\Models\ZoneSite::count();

        // Liste des clients, bases et sites pour les filtres
        if ($isSoutarah) {
            if ($soutarahAssignment && $soutarahAssignment->base_id) {
                $allClients = Client::whereHas('bases', fn($b) => $b->where('id', $soutarahAssignment->base_id))->orderBy('nom')->get();
                $allBases = \App\Models\BaseSite::where('id', $soutarahAssignment->base_id)->orderBy('nom_base')->get();
                $allSites = \App\Models\Site::with('baseSite')->where('base_id', $soutarahAssignment->base_id)->orderBy('nom_site')->get();
            } elseif ($soutarahAssignment && $soutarahAssignment->client_id) {
                $allClients = Client::where('id', $soutarahAssignment->client_id)->orderBy('nom')->get();
                $allBases = \App\Models\BaseSite::where('client_id', $soutarahAssignment->client_id)->orderBy('nom_base')->get();
                $allSites = \App\Models\Site::with('baseSite')->where(function($q) use ($soutarahAssignment) {
                    $q->where('client_id', $soutarahAssignment->client_id)
                      ->orWhereHas('baseSite', fn($b) => $b->where('client_id', $soutarahAssignment->client_id));
                })->orderBy('nom_site')->get();
            } else {
                $allClients = collect();
                $allBases = collect();
                $allSites = collect();
            }
        } else {
            $allClients = Client::orderBy('nom')->get();
            $allBases = \App\Models\BaseSite::orderBy('nom_base')->get();
            $allSites = \App\Models\Site::with('baseSite')->orderBy('nom_site')->get();
        }

        return view('clients.combined', compact(
            'clients', 'totalClients',
            'bases', 'totalBases',
            'sites', 'totalSites',
            'emplacements', 'totalEmplacements',
            'allClients', 'allBases', 'allSites',
            'view'
        ));
    }

    public function show($id)
    {
        $client = Client::with(['bases', 'utilisateurs', 'depannages'])->findOrFail($id);

        return view('clients.show', compact('client'));
    }

    public function create()
    {
        // Générer le prochain code client
        $lastClient = Client::orderBy('id', 'desc')->first();
        
        if ($lastClient && preg_match('/CLI-(\d+)/', $lastClient->code, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            // Si aucun client ou format différent, commencer à CLI-001
            $nextNumber = 1;
        }
        
        $nextCode = 'CLI-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        
        return view('clients.create', compact('nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:clients,code',
            'nom' => 'required|string|max:150',
            'email' => 'nullable|email|max:150',
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'pays' => 'nullable|string|max:100',
        ]);

        $client = Client::create($validated);

        return redirect()->route('clients.combined', ['view' => 'entreprises'])
            ->with('success', 'Entreprise créée avec succès !');
    }

    public function edit($id)
    {
        $client = Client::findOrFail($id);
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:clients,code,' . $id,
            'nom' => 'required|string|max:150',
            'email' => 'nullable|email|max:150',
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'pays' => 'nullable|string|max:100',
        ]);

        $client->update($validated);

        return redirect()->route('clients.combined', ['view' => 'entreprises'])
            ->with('success', 'Entreprise mise à jour !');
    }

    public function destroy($id)
    {
        $client = Client::findOrFail($id);

        DB::transaction(function () use ($client) {
            $baseIds = $client->bases()->pluck('id')->toArray();
            
            $siteQuery = Site::where('client_id', $client->id);
            if (!empty($baseIds)) {
                $siteQuery->orWhereIn('base_id', $baseIds);
            }
            $siteIds = $siteQuery->pluck('id')->toArray();

            // Supprimer les notifications et planifications rattachées
            DB::table('intervention_notifications')->whereIn('demande_id', function ($q) use ($client) {
                $q->select('id')->from('demandes')->where('client_id', $client->id);
            })->delete();

            DB::table('planifications')->whereIn('demande_id', function ($q) use ($client) {
                $q->select('id')->from('demandes')->where('client_id', $client->id);
            })->delete();

            // Supprimer dépannages & demandes
            DB::table('depannages')->where('client_id', $client->id)->delete();
            DB::table('demandes')->where('client_id', $client->id)->delete();

            // Supprimer équipements
            DB::table('equipements')->where(function ($q) use ($client, $siteIds, $baseIds) {
                $q->where('client_id', $client->id);
                if (!empty($siteIds)) {
                    $q->orWhereIn('site_id', $siteIds);
                }
                if (!empty($baseIds)) {
                    $q->orWhereIn('base_id', $baseIds);
                }
            })->delete();

            // Supprimer pivot demandeur_site & sites
            if (!empty($siteIds)) {
                DB::table('demandeur_site')->whereIn('site_id', $siteIds)->delete();
                Site::whereIn('id', $siteIds)->delete();
            }

            // Supprimer bases
            if (!empty($baseIds)) {
                BaseSite::whereIn('id', $baseIds)->delete();
            }

            // Supprimer assignments & utilisateurs
            DB::table('assignments')->where('client_id', $client->id)->delete();
            User::where('client_id', $client->id)->delete();

            // Supprimer l'entreprise
            $client->delete();
        });

        return redirect()->route('clients.combined', ['view' => 'entreprises'])
            ->with('success', "L'entreprise et toutes ses données associées (bases, sites, équipements) ont été supprimées !");
    }
}
