<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\BaseSite;
use App\Models\Client;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * Liste des sites
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Site::with(['baseSite.client', 'client', 'equipements']);

        // Si Superviseur Client : afficher uniquement les sites de SA base OU son client
        if ($user->isSuperviseurClient()) {
            if ($user->base_id) {
                // Structure avec base
                $query->where('base_id', $user->base_id);
                $base = BaseSite::with('client')->find($user->base_id);
            } elseif ($user->client_id) {
                // Structure directe (client → site)
                $query->where('client_id', $user->client_id)->whereNull('base_id');
                $client = Client::find($user->client_id);
            } else {
                // Pas de base ni de client : rediriger
                return redirect()->route('dashboard')
                    ->with('info', 'Configuration utilisateur incomplète.');
            }
            
            // Compter les équipements par site
            $query->withCount('equipements');
            
            // Recherche
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nom_site', 'like', "%{$search}%")
                      ->orWhere('code_site', 'like', "%{$search}%")
                      ->orWhere('adresse', 'like', "%{$search}%")
                      ->orWhere('ville', 'like', "%{$search}%");
                });
            }
            
            $sites = $query->orderBy('nom_site')->paginate(15);
            
            return view('sites.index_superviseur', compact('sites'))->with([
                'base' => $base ?? null,
                'client' => $client ?? null,
            ]);
        }

        // Si Superviseur Soutarah : afficher les sites de SA base assignée (lecture seule)
        if ($user->type_utilisateur === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if (!$assignment) {
                return redirect()->route('dashboard')
                    ->with('info', 'Vous n\'avez pas encore de base assignée.');
            }
            if ($assignment->base_id) {
                $query->where('base_id', $assignment->base_id);
                $base = BaseSite::with('client')->find($assignment->base_id);
            } elseif ($assignment->client_id) {
                // Assigné à un client (sans base précise) → tous ses sites
                $clientId = $assignment->client_id;
                $baseIds = \App\Models\BaseSite::where('client_id', $clientId)->pluck('id');
                $query->where(function($q) use ($clientId, $baseIds) {
                    $q->where('client_id', $clientId)
                      ->orWhereIn('base_id', $baseIds);
                });
                $client = \App\Models\Client::find($clientId);
            } else {
                return redirect()->route('dashboard')
                    ->with('info', 'Configuration de l\'affectation incomplète.');
            }

            $query->withCount('equipements');

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nom_site', 'like', "%{$search}%")
                      ->orWhere('code_site', 'like', "%{$search}%")
                      ->orWhere('ville', 'like', "%{$search}%");
                });
            }

            $sites = $query->orderBy('nom_site')->paginate(15);

            return view('sites.index_superviseur', compact('sites'))->with([
                'base'           => $base ?? null,
                'client'         => $client ?? null,
                'readOnly'       => true, // Lecture seule pour Soutarah
            ]);
        }

        
        // Admin : vue complète (comportement existant)
        // Filtrer par base si spécifié
        if ($request->has('base_id') && $request->base_id) {
            $query->where('base_id', $request->base_id);
        }
        
        // Filtrer par client si spécifié
        if ($request->has('client_id') && $request->client_id) {
            $query->where(function($q) use ($request) {
                // Sites avec client_id direct (sans base)
                $q->where('client_id', $request->client_id)
                  // OU sites via une base appartenant à ce client
                  ->orWhereHas('baseSite', function($subQ) use ($request) {
                      $subQ->where('client_id', $request->client_id);
                  });
            });
        }

        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom_site', 'like', "%{$search}%")
                  ->orWhere('code_site', 'like', "%{$search}%")
                  ->orWhere('ville', 'like', "%{$search}%");
            });
        }

        $sites = $query->orderBy('nom_site')->paginate(15);
        $bases = BaseSite::with('client')->orderBy('nom_base')->get();
        $clients = Client::orderBy('nom')->get();

        return view('sites.index', compact('sites', 'bases', 'clients'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $user = auth()->user();
        
        // Superviseur Client : créer uniquement dans SA base OU son client
        if ($user->isSuperviseurClient()) {
            // Si le superviseur a un client_id direct (sans base)
            if ($user->client_id && !$user->base_id) {
                $client = Client::findOrFail($user->client_id);
                
                // Générer le prochain code site pour ce client
                $lastSite = Site::where('client_id', $user->client_id)->orderBy('id', 'desc')->first();
                $nextNumber = $lastSite ? intval(substr($lastSite->code_site, -4)) + 1 : 1;
                $nextCode = strtoupper(str_replace(' ', '', substr($client->nom, 0, 4))) . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                
                return view('sites.create', compact('client', 'nextCode'));
            }
            
            // Sinon, structure normale avec base
            if ($user->base_id) {
                $base = BaseSite::with('client')->findOrFail($user->base_id);
                
                // Générer le prochain code site pour cette base
                $lastSite = Site::where('base_id', $user->base_id)->orderBy('id', 'desc')->first();
                $nextNumber = $lastSite ? intval(substr($lastSite->code_site, -4)) + 1 : 1;
                $nextCode = strtoupper(str_replace(' ', '', substr($base->nom_base, 0, 4))) . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                
                return view('sites.create', compact('base', 'nextCode'));
            }
            
            // Si ni base ni client, rediriger
            return redirect()->route('dashboard')
                ->with('error', 'Configuration utilisateur incomplète.');
        }
        
        // Admin : comportement existant + support 2 structures
        $bases = BaseSite::with('client')->orderBy('nom_base')->get();
        $clients = Client::orderBy('nom')->get();
        
        // Générer le prochain code site
        $lastSite = Site::orderBy('id', 'desc')->first();
        $nextNumber = $lastSite ? intval(substr($lastSite->code_site, 4)) + 1 : 1;
        $nextCode = 'SITE' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return view('sites.create', compact('bases', 'clients', 'nextCode'));
    }

    /**
     * Enregistrer un nouveau site
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $sanitizeStr = function ($value, $maxLen = null) {
            if ($value === null) return null;
            $v = trim((string)$value);
            if ($v === '') return null;
            if ($maxLen !== null && mb_strlen($v, 'UTF-8') > $maxLen) {
                $v = mb_substr($v, 0, $maxLen, 'UTF-8');
            }
            return $v;
        };

        $normalized = [
            'nom_site'     => $sanitizeStr($request->input('nom_site'),     255),
            'code_site'    => $sanitizeStr($request->input('code_site'),    50),
            'adresse'      => $sanitizeStr($request->input('adresse'),      500),
            'ville'        => $sanitizeStr($request->input('ville'),        191),
            'telephone'    => $sanitizeStr($request->input('telephone'),    50),
            'observations' => $sanitizeStr($request->input('observations'), 3000),
        ];
        if ($normalized['code_site'] !== null) {
            $normalized['code_site'] = preg_replace('/[^A-Za-z0-9_-]/', '', $normalized['code_site']);
            if ($normalized['code_site'] === '') $normalized['code_site'] = null;
        }
        $request->merge($normalized);

        // Déterminer le client_id pour la validation d'unicité
        $clientIdForValidation = null;
        
        if ($request->has('base_id') && $request->base_id) {
            $base = BaseSite::find($request->base_id);
            if ($base) {
                $clientIdForValidation = $base->client_id;
            }
        } elseif ($request->has('client_id') && $request->client_id) {
            $clientIdForValidation = $request->client_id;
        }
        
        // Validation de base avec unicité du code_site PAR CLIENT
        $validationRules = [
            'nom_site' => 'required|string|max:255',
            'code_site' => [
                'required',
                'string',
                'max:50',
                // Unicité par client (pas globale)
                function ($attribute, $value, $fail) use ($clientIdForValidation) {
                    if ($clientIdForValidation) {
                        $exists = Site::where('code_site', $value)
                            ->where('client_id', $clientIdForValidation)
                            ->exists();
                        if ($exists) {
                            $fail('Ce code site existe déjà pour ce client.');
                        }
                    }
                },
            ],
            'adresse' => 'nullable|string|max:500',
            'ville' => 'nullable|string|max:191',
            'telephone' => 'nullable|string|max:50',
            'observations' => 'nullable|string|max:3000',
        ];

        $validated = $request->validate($validationRules);

        // Validation structure : base_id OU client_id (XOR)
        $structureValidation = $request->validate([
            'base_id' => 'nullable|exists:bases,id',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        // Au moins un des deux doit être rempli
        if (empty($structureValidation['base_id']) && empty($structureValidation['client_id'])) {
            return back()->withErrors(['structure' => 'Vous devez choisir soit une base, soit un client direct.'])->withInput();
        }

        // Les deux ne peuvent pas être remplis en même temps
        if (!empty($structureValidation['base_id']) && !empty($structureValidation['client_id'])) {
            return back()->withErrors(['structure' => 'Un site ne peut pas avoir à la fois une base et un client direct.'])->withInput();
        }

        // Merge des données
        $validated = array_merge($validated, $structureValidation);

        // Si base_id fourni, récupérer automatiquement le client_id
        if (!empty($validated['base_id'])) {
            $base = BaseSite::findOrFail($validated['base_id']);
            $validated['client_id'] = $base->client_id;
        }

        // Superviseur Client : vérifier les permissions
        if ($user->isSuperviseurClient()) {
            if ($user->base_id) {
                if ($validated['base_id'] != $user->base_id) {
                    abort(403, 'Vous ne pouvez créer des sites que dans votre base.');
                }
            } elseif ($user->client_id) {
                if ($validated['client_id'] != $user->client_id) {
                    abort(403, 'Vous ne pouvez créer des sites que pour votre entreprise.');
                }
            }
        }

        try {
            Site::create($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            $msg = $e->getMessage();
            $friendly = null;
            if (stripos($msg, 'too long for column') !== false || stripos($msg, 'right truncated') !== false) {
                $friendly = "Un champ saisi dépasse la taille maximale autorisée par la base. Vérifiez les longueurs des champs, notamment code_site, nom_site, adresse, téléphone.";
            } elseif (stripos($msg, 'Duplicate entry') !== false) {
                if (stripos($msg, 'code_site') !== false) {
                    $friendly = "Ce Code Site (code_site) existe déjà — veuillez en choisir un autre.";
                } else {
                    $friendly = "Une contrainte d'unicité a échoué (entrée dupliquée).";
                }
            }
            return back()
                ->withErrors(['bdd' => $friendly ?? ('Erreur base de données : ' . $msg)])
                ->withInput();
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['bdd' => 'Erreur inattendue : ' . $e->getMessage()])
                ->withInput();
        }

        // Redirection selon le rôle
        if ($user->isSuperviseurClient()) {
            return redirect()->route('sites.index')
                ->with('success', 'Site créé avec succès.');
        }

        return redirect()->route('clients.combined', ['view' => 'sites'])
            ->with('success', 'Site créé avec succès.');
    }

    /**
     * Afficher un site
     */
    public function show(Site $site)
    {
        $user = auth()->user();
        
        // Superviseur Client : vérifier que c'est un site de SA base OU son client
        if ($user->isSuperviseurClient()) {
            $hasAccess = false;
            
            if ($user->base_id && $site->base_id == $user->base_id) {
                $hasAccess = true;
            } elseif ($user->client_id && $site->client_id == $user->client_id && !$site->base_id) {
                $hasAccess = true;
            }
            
            if (!$hasAccess) {
                abort(403, 'Accès non autorisé à ce site.');
            }
        }
        
        // Charger toutes les relations nécessaires
        $site->load(['baseSite.client', 'client', 'equipements', 'demandeurs']);
        
        return view('sites.show', compact('site'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Site $site)
    {
        $user = auth()->user();
        
        // Superviseur Client : vérifier que c'est un site de SA base OU son client
        if ($user->isSuperviseurClient()) {
            $hasAccess = false;
            
            if ($user->base_id && $site->base_id == $user->base_id) {
                $hasAccess = true;
                $base = $site->baseSite;
            } elseif ($user->client_id && $site->client_id == $user->client_id && !$site->base_id) {
                $hasAccess = true;
                $client = $site->client;
            }
            
            if (!$hasAccess) {
                abort(403, 'Accès non autorisé à ce site.');
            }
            
            return view('sites.edit', compact('site'))->with([
                'base' => $base ?? null,
                'client' => $client ?? null,
            ]);
        }
        
        // Admin : comportement existant
        $bases = BaseSite::with('client')->orderBy('nom_base')->get();
        $clients = Client::orderBy('nom')->get();
        
        return view('sites.edit', compact('site', 'bases', 'clients'));
    }

    /**
     * Mettre à jour un site
     */
    public function update(Request $request, Site $site)
    {
        $user = auth()->user();
        
        // Superviseur Client : vérifier que c'est un site de SA base OU son client
        if ($user->isSuperviseurClient()) {
            $hasAccess = false;
            
            if ($user->base_id && $site->base_id == $user->base_id) {
                $hasAccess = true;
            } elseif ($user->client_id && $site->client_id == $user->client_id && !$site->base_id) {
                $hasAccess = true;
            }
            
            if (!$hasAccess) {
                abort(403, 'Accès non autorisé à ce site.');
            }
        }
        
        // Déterminer le client_id pour la validation d'unicité
        $clientIdForValidation = null;
        
        if ($request->has('base_id') && $request->base_id) {
            $base = BaseSite::find($request->base_id);
            if ($base) {
                $clientIdForValidation = $base->client_id;
            }
        } elseif ($request->has('client_id') && $request->client_id) {
            $clientIdForValidation = $request->client_id;
        }
        
        // Validation de base avec unicité du code_site PAR CLIENT (sauf pour le site actuel)
        $validationRules = [
            'nom_site' => 'required|string|max:255',
            'code_site' => [
                'required',
                'string',
                'max:50',
                // Unicité par client (pas globale), en excluant le site actuel
                function ($attribute, $value, $fail) use ($clientIdForValidation, $site) {
                    if ($clientIdForValidation) {
                        $exists = Site::where('code_site', $value)
                            ->where('client_id', $clientIdForValidation)
                            ->where('id', '!=', $site->id)
                            ->exists();
                        if ($exists) {
                            $fail('Ce code site existe déjà pour ce client.');
                        }
                    }
                },
            ],
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'observations' => 'nullable|string',
        ];
        
        $validated = $request->validate($validationRules);
        
        // Validation structure : base_id OU client_id
        $structureValidation = $request->validate([
            'base_id' => 'nullable|exists:bases,id',
            'client_id' => 'nullable|exists:clients,id',
        ]);
        
        // Au moins un des deux doit être rempli
        if (empty($structureValidation['base_id']) && empty($structureValidation['client_id'])) {
            return back()->withErrors(['structure' => 'Vous devez choisir soit une base, soit un client direct.'])->withInput();
        }
        
        // Les deux ne peuvent pas être remplis en même temps
        if (!empty($structureValidation['base_id']) && !empty($structureValidation['client_id'])) {
            return back()->withErrors(['structure' => 'Un site ne peut pas avoir à la fois une base et un client direct.'])->withInput();
        }
        
        // Merge des données
        $validated = array_merge($validated, $structureValidation);
        
        // Si base_id fourni, récupérer automatiquement le client_id
        if (!empty($validated['base_id'])) {
            $base = BaseSite::findOrFail($validated['base_id']);
            $validated['client_id'] = $base->client_id;
        }
        
        // Superviseur Client : vérifier que la structure ne change pas de manière non autorisée
        if ($user->isSuperviseurClient()) {
            if ($user->base_id) {
                if ($validated['base_id'] != $user->base_id) {
                    abort(403, 'Vous ne pouvez pas changer la base du site.');
                }
            } elseif ($user->client_id) {
                if ($validated['client_id'] != $user->client_id || !empty($validated['base_id'])) {
                    abort(403, 'Vous ne pouvez pas modifier la structure du site.');
                }
            }
        }

        $site->update($validated);

        // Redirection selon le rôle
        if ($user->isSuperviseurClient()) {
            return redirect()->route('sites.index')
                ->with('success', 'Site modifié avec succès.');
        }

        return redirect()->route('clients.combined', ['view' => 'sites'])
            ->with('success', 'Site modifié avec succès.');
    }

    /**
     * Supprimer un site
     */
    public function destroy(Site $site)
    {
        $user = auth()->user();
        
        // Superviseur Client : vérifier que c'est un site de SA base OU son client
        if ($user->isSuperviseurClient()) {
            $hasAccess = false;
            
            if ($user->base_id && $site->base_id == $user->base_id) {
                $hasAccess = true;
            } elseif ($user->client_id && $site->client_id == $user->client_id && !$site->base_id) {
                $hasAccess = true;
            }
            
            if (!$hasAccess) {
                abort(403, 'Accès non autorisé à ce site.');
            }
        }
        
        // Vérifier s'il y a des équipements
        if ($site->equipements()->count() > 0) {
            $redirectRoute = $user->isSuperviseurClient() ? 'sites.index' : 'clients.combined';
            $redirectParams = $user->isSuperviseurClient() ? [] : ['view' => 'sites'];
            
            return redirect()->route($redirectRoute, $redirectParams)
                ->with('error', 'Impossible de supprimer ce site car il contient des équipements. Supprimez d\'abord les équipements.');
        }

        // Vérifier s'il y a des demandeurs assignés
        $demandeursCount = \App\Models\User::whereHas('sitesAssignes', function($q) use ($site) {
            $q->where('sites.id', $site->id);
        })->count();
        
        if ($demandeursCount > 0) {
            $redirectRoute = $user->isSuperviseurClient() ? 'sites.index' : 'clients.combined';
            $redirectParams = $user->isSuperviseurClient() ? [] : ['view' => 'sites'];
            
            return redirect()->route($redirectRoute, $redirectParams)
                ->with('error', 'Impossible de supprimer ce site car des demandeurs y sont assignés. Modifiez d\'abord les assignations.');
        }

        $site->delete();

        // Redirection selon le rôle
        if ($user->isSuperviseurClient()) {
            return redirect()->route('sites.index')
                ->with('success', 'Site supprimé avec succès !');
        }

        return redirect()->route('clients.combined', ['view' => 'sites'])
            ->with('success', 'Site supprimé avec succès !');
    }
}
