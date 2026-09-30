<?php

namespace App\Http\Controllers;

use App\Models\ZoneSite;
use App\Models\Site;
use App\Models\BaseSite;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ZoneSiteController extends Controller
{
    /**
     * Obtenir les sites autorisés pour l'utilisateur connecté
     */
    private function getSitesForUser($user)
    {
        $query = Site::with(['client', 'baseSite']);

        if ($user->isSuperviseurClient()) {
            if ($user->base_id) {
                $query->where('base_id', $user->base_id);
            } elseif ($user->client_id) {
                $query->where(function($q) use ($user) {
                    $q->where('client_id', $user->client_id)
                      ->orWhereHas('baseSite', function($b) use ($user) {
                          $b->where('client_id', $user->client_id);
                      });
                });
            } elseif ($user->site_id) {
                $query->where('id', $user->site_id);
            }
        } elseif ($user->type_utilisateur === 'demandeur') {
            if ($user->site_id) {
                $query->where('id', $user->site_id);
            } else {
                $siteIds = $user->sitesAssignes()->pluck('sites.id')->toArray();
                if (!empty($siteIds)) {
                    $query->whereIn('id', $siteIds);
                } elseif ($user->client_id) {
                    $query->where(function($q) use ($user) {
                        $q->where('client_id', $user->client_id)
                          ->orWhereHas('baseSite', function($b) use ($user) {
                              $b->where('client_id', $user->client_id);
                          });
                    });
                }
            }
        } elseif ($user->type_utilisateur === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $query->where('base_id', $assignment->base_id);
                } elseif ($assignment->client_id) {
                    $query->where(function($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id)
                          ->orWhereHas('baseSite', function($b) use ($assignment) {
                              $b->where('client_id', $assignment->client_id);
                          });
                    });
                }
            }
        }

        return $query->orderBy('nom_site')->get();
    }

    /**
     * Liste des zones / emplacements
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ZoneSite::with(['site', 'baseSite', 'client'])->withCount(['equipements', 'demandeurs']);

        // Filtrer selon le rôle de l'utilisateur
        if ($user->isSuperviseurClient()) {
            if ($user->base_id) {
                $query->where('base_id', $user->base_id);
            } elseif ($user->client_id) {
                $query->where('client_id', $user->client_id);
            } elseif ($user->site_id) {
                $query->where('site_id', $user->site_id);
            }
        } elseif ($user->type_utilisateur === 'demandeur') {
            // Support pour les deux systèmes : site_id unique OU sites assignés (many-to-many)
            if ($user->site_id) {
                // Ancien système : site unique
                $query->where('site_id', $user->site_id);
            } else {
                // Nouveau système : sites assignés via relation many-to-many
                $siteIds = $user->sitesAssignes()->pluck('sites.id')->toArray();
                if (!empty($siteIds)) {
                    $query->whereIn('site_id', $siteIds);
                } else {
                    // Aucun site assigné : aucune zone visible
                    $query->whereRaw('1 = 0');
                }
            }
        } elseif ($user->type_utilisateur === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $query->where('base_id', $assignment->base_id);
                } elseif ($assignment->client_id) {
                    $query->where('client_id', $assignment->client_id);
                }
            }
        }

        // Filtres optionnels
        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }
        if ($request->filled('base_id')) {
            $query->where('base_id', $request->base_id);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom_zone', 'like', "%{$search}%")
                  ->orWhere('code_zone', 'like', "%{$search}%")
                  ->orWhere('observations', 'like', "%{$search}%");
            });
        }

        $zones = $query->orderBy('nom_zone')->paginate(15);
        $sites = $this->getSitesForUser($user);
        $bases = BaseSite::orderBy('nom_base')->get();
        $clients = Client::orderBy('nom')->get();

        return view('zones.index', compact('zones', 'sites', 'bases', 'clients'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $user = Auth::user();
        $sites = $this->getSitesForUser($user);
        $clients = Client::orderBy('nom')->get();
        
        $lastZone = ZoneSite::orderBy('id', 'desc')->first();
        $nextNumber = 1;
        if ($lastZone && $lastZone->code_zone) {
            $num = intval(preg_replace('/[^0-9]/', '', $lastZone->code_zone));
            if ($num > 0) $nextNumber = $num + 1;
        }
        if ($nextNumber === 1 && ZoneSite::count() > 0) {
            $nextNumber = ZoneSite::count() + 1;
        }
        $nextCode = 'EMP' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return view('zones.create', compact('sites', 'clients', 'nextCode'));
    }

    /**
     * Enregistrement d'une zone / emplacement
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $allowedSiteIds = $user->isAdmin() ? Site::pluck('id')->toArray() : $this->getSitesForUser($user)->pluck('id')->toArray();

        $validated = $request->validate([
            'site_id'      => 'required|in:' . implode(',', $allowedSiteIds),
            'nom_zone'     => 'required|string|max:255',
            'code_zone'    => 'nullable|string|max:50',
            'observations' => 'nullable|string',
        ], [
            'site_id.in'         => 'Le site sélectionné n\'appartient pas à votre périmètre.',
            'nom_zone.required'  => 'Le nom de l\'emplacement est obligatoire.',
        ]);

        $site = Site::findOrFail($validated['site_id']);
        
        // Générer automatiquement le code si non fourni
        $codeZone = $validated['code_zone'] ?? \App\Helpers\CodeGenerator::generateEmplacementCode($validated['nom_zone']);

        ZoneSite::create([
            'site_id'      => $site->id,
            'base_id'      => $site->base_id,
            'client_id'    => $site->client_id ?? ($site->baseSite->client_id ?? null),
            'nom_zone'     => $validated['nom_zone'],
            'code_zone'    => strtoupper($codeZone),
            'observations' => $validated['observations'] ?? null,
        ]);

        $redirectRoute = Auth::user()->isAdmin() ? 'clients.combined' : 'zones.index';
        $redirectParams = Auth::user()->isAdmin() ? ['view' => 'emplacements'] : [];

        return redirect()->route($redirectRoute, $redirectParams)->with('success', 'Emplacement créé avec succès !');
    }

    /**
     * Afficher les détails d'une zone / emplacement
     */
    public function show($id)
    {
        $zone = ZoneSite::with(['site.baseSite.client', 'site.client', 'equipements'])->findOrFail($id);
        
        return view('zones.show', compact('zone'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $zone = ZoneSite::findOrFail($id);
        $user = Auth::user();
        $sites = $this->getSitesForUser($user);
        return view('zones.edit', compact('zone', 'sites'));
    }

    /**
     * Mise à jour d'une zone / emplacement
     */
    public function update(Request $request, $id)
    {
        $zone = ZoneSite::findOrFail($id);

        $validated = $request->validate([
            'site_id'      => 'required|exists:sites,id',
            'nom_zone'     => 'required|string|max:255',
            'code_zone'    => 'nullable|string|max:50',
            'observations' => 'nullable|string',
        ], [
            'nom_zone.required'  => 'Le nom de l\'emplacement est obligatoire.',
        ]);

        $site = Site::findOrFail($validated['site_id']);
        
        // Générer automatiquement le code si non fourni ou si le nom a changé
        $codeZone = $validated['code_zone'] ?? \App\Helpers\CodeGenerator::generateEmplacementCode($validated['nom_zone'], $zone->id);

        $zone->update([
            'site_id'      => $site->id,
            'base_id'      => $site->base_id,
            'client_id'    => $site->client_id ?? ($site->baseSite->client_id ?? null),
            'nom_zone'     => $validated['nom_zone'],
            'code_zone'    => strtoupper($codeZone),
            'observations' => $validated['observations'] ?? null,
        ]);

        $redirectRoute = Auth::user()->isAdmin() ? 'clients.combined' : 'zones.index';
        $redirectParams = Auth::user()->isAdmin() ? ['view' => 'emplacements'] : [];

        return redirect()->route($redirectRoute, $redirectParams)->with('success', 'Emplacement mis à jour avec succès !');
    }

    /**
     * Suppression d'une zone
     */
    public function destroy($id)
    {
        $zone = ZoneSite::findOrFail($id);
        $zone->delete();

        return redirect()->route('zones.index')->with('success', 'Zone / Emplacement supprimé(e) avec succès !');
    }

    /**
     * Endpoint API : Obtenir les zones d'un site
     */
    public function getZonesBySite($siteId)
    {
        $zones = ZoneSite::where('site_id', $siteId)
            ->orderBy('nom_zone')
            ->get(['id', 'nom_zone', 'code_zone']);

        return response()->json($zones);
    }
}
