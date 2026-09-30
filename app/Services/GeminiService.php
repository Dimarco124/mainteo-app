<?php

namespace App\Services;

use App\Models\Demande;
use App\Models\Depannage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected ?string $groqKey = '';
    protected ?string $geminiKey = '';
    protected string $groqModel = 'openai/gpt-oss-120b';
    protected string $geminiModel = 'gemini-3.8-flash';

    public function __construct()
    {
        $this->groqKey = (string) (config('services.groq.api_key') ?: env('GROQ_API_KEY') ?: '');
        $this->geminiKey = (string) (config('services.gemini.api_key') ?: env('GEMINI_API_KEY') ?: '');
        
        $model = (string) (config('services.groq.model') ?: env('GROQ_MODEL') ?: 'openai/gpt-oss-120b');
        if (in_array($model, ['allam-2-7b', '', 'null'], true)) {
            $model = 'openai/gpt-oss-120b';
        }
        $this->groqModel = $model;
    }

    /**
     * Génère une réponse IA autonome et intelligente avec contrôle d'accès par rôle (RBAC)
     */
    public function chat(User $user, string $userMessage, array $conversationHistory = []): string
    {
        // 1. Récupération des données métiers autorisées pour cet utilisateur
        $userContext = $this->buildUserContext($user);

        // 2. Construction du prompt système
        $systemInstruction = $this->buildSystemInstruction($user, $userContext);

        // 3. Appel prioritaire via Groq (LLM 120B ultra-intelligent et sans restriction)
        if (!empty($this->groqKey) || str_starts_with($this->geminiKey, 'gsk_')) {
            $key = !empty($this->groqKey) ? $this->groqKey : $this->geminiKey;
            $groqResponse = $this->callGroqApi($key, $systemInstruction, $userMessage, $conversationHistory);
            if ($groqResponse !== null) {
                return $groqResponse;
            }
        }

        // 4. Appel via Google Gemini API (si clé Gemini présente)
        if (!empty($this->geminiKey) && !str_starts_with($this->geminiKey, 'gsk_')) {
            $geminiResponse = $this->callGeminiApi($systemInstruction, $userMessage, $conversationHistory);
            if ($geminiResponse !== null) {
                return $geminiResponse;
            }
        }

        // 5. Mode Fallback interne si aucune connexion réseau n'est possible
        return $this->generateLocalFallbackResponse($user, $userMessage, $context ?? $userContext);
    }

    /**
     * Appel à l'API Groq (OpenAI compatible, avec fallback automatique et gestion de taille)
     */
    protected function callGroqApi(string $apiKey, string $systemPrompt, string $userMessage, array $history): ?string
    {
        $messages = [];
        $messages[] = ['role' => 'system', 'content' => $systemPrompt];

        // On conserve au maximum les 4 derniers échanges, condensés pour préserver le quota de tokens
        $recentHistory = array_slice($history, -4);
        foreach ($recentHistory as $msg) {
            $content = trim((string)($msg['content'] ?? ''));
            if (empty($content)) continue;

            // Tronquer les longs messages d'historique (tableaux précédents, etc.)
            if (mb_strlen($content) > 500) {
                $content = mb_substr($content, 0, 500) . '... [suite tronquée]';
            }

            $role = ($msg['role'] === 'assistant' || $msg['role'] === 'model') ? 'assistant' : 'user';
            $messages[] = [
                'role' => $role,
                'content' => $content
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        // Modèles à tenter dans l'ordre : modèle configuré, puis gpt-oss-20b en secours immédiat
        $modelsToTry = array_unique([$this->groqModel, 'openai/gpt-oss-20b']);

        $lastError = null;
        foreach ($modelsToTry as $currentModel) {
            try {
                $response = Http::withoutVerifying()
                    ->withOptions([
                        'curl' => [
                            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                        ],
                    ])
                    ->withHeaders([
                        'Content-Type'  => 'application/json',
                        'Authorization' => 'Bearer ' . $apiKey,
                    ])
                    ->timeout(20)
                    ->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model'       => $currentModel,
                        'messages'    => $messages,
                        'temperature' => 0.7,
                        'max_tokens'  => 1024,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $content = $data['choices'][0]['message']['content'] ?? null;
                    if (!empty($content)) {
                        return trim($content);
                    }
                } else {
                    $err = $response->json('error.message') ?? $response->body();
                    $lastError = "⚠️ Erreur API Groq (" . $response->status() . ") : " . $err;
                    Log::warning("Groq API error on model $currentModel: " . $response->status() . " - " . $response->body());
                    // Si erreur de taille ou quota, on tente le modèle suivant plus léger
                    continue;
                }
            } catch (\Throwable $e) {
                Log::error("Groq API Exception on $currentModel: " . $e->getMessage());
                $lastError = "⚠️ Erreur de connexion serveur : " . $e->getMessage();
            }
        }

        return $lastError;
    }

    /**
     * Appel à Google Gemini API
     */
    protected function callGeminiApi(string $systemInstruction, string $userMessage, array $history): ?string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->geminiModel}:generateContent?key={$this->geminiKey}";

        $contents = [];
        foreach ($history as $msg) {
            $role = ($msg['role'] === 'assistant' || $msg['role'] === 'model') ? 'model' : 'user';
            $contents[] = [
                'role'  => $role,
                'parts' => [['text' => $msg['content']]],
            ];
        }
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $userMessage]],
        ];

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $systemInstruction]]
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1024,
            ]
        ];

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(20)
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $parts = $data['candidates'][0]['content']['parts'] ?? [];
                if (!empty($parts)) {
                    return trim($parts[0]['text'] ?? '');
                }
            }
        } catch (\Throwable $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Construit les données contextuelles de l'utilisateur
     */
    protected function buildUserContext(User $user): array
    {
        $role = $user->type_utilisateur;
        $context = [
            'user_name' => trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')),
            'role'      => $role,
            'role_label'=> $this->getRoleLabel($role),
            'client_nom'=> null,
            'base_nom'  => null,
            'sites'     => [],
            'equipes'   => [],
        ];

        try {
            if ($user->client) {
                $context['client_nom'] = $user->client->nom;
            }
            if ($user->baseSite) {
                $context['base_nom'] = $user->baseSite->nom_base;
            }
            if ($user->isDemandeur()) {
                $sites = [];
                if ($user->sitesAssignes) {
                    $sites = $user->sitesAssignes->pluck('nom_site')->toArray();
                }
                if ($user->siteAssigne) {
                    $sites[] = $user->siteAssigne->nom_site;
                }
                $context['sites'] = array_values(array_unique(array_filter($sites)));
            }
            if ($user->isTechnicien()) {
                if ($user->equipes) {
                    $context['equipes'] = $user->equipes->pluck('nom_equipe')->toArray();
                } elseif ($user->equipe) {
                    $context['equipes'] = [$user->equipe->nom_equipe];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('buildUserContext error: ' . $e->getMessage());
        }

        return $context;
    }

    /**
     * Construit une représentation textuelle ultra-compacte et cloisonnée selon le périmètre de l'utilisateur.
     * Les données réelles sont strictement filtrées par rôle et périmètre (sécurité & étanchéité).
     */
    protected function buildCompactDatabaseText(User $user): string
    {
        $role = $user->type_utilisateur;
        $isAdmin = $user->isAdmin();
        $isSupSoutarah = $user->isSuperviseurSoutarah();
        $isSupClient = $user->isSuperviseurClient();
        $isDemandeur = $user->isDemandeur();
        $isTech = $user->isTechnicien();

        // Récupérer les identifiants de périmètre de l'utilisateur
        $clientId = $user->client_id;
        $baseId = $user->base_id;
        $siteIds = [];
        if ($isDemandeur) {
            try {
                $siteIds = $user->sitesAssignes ? $user->sitesAssignes->pluck('id')->toArray() : [];
                if ($user->site_id) {
                    $siteIds[] = $user->site_id;
                }
                $siteIds = array_values(array_unique(array_filter($siteIds)));
            } catch (\Throwable $e) {}
        }

        // Pour superviseur Soutarah : bases/clients assignés
        $assignedBaseIds = [];
        $assignedClientIds = [];
        if ($isSupSoutarah) {
            try {
                $assignments = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->get();
                $assignedBaseIds = $assignments->pluck('base_id')->filter()->unique()->toArray();
                $assignedClientIds = $assignments->pluck('client_id')->filter()->unique()->toArray();
            } catch (\Throwable $e) {}
        }

        // Pour technicien : ses équipes
        $userEquipeIds = [];
        if ($isTech) {
            try {
                $userEquipeIds = $user->equipes ? $user->equipes->pluck('id')->toArray() : [];
                if ($user->equipe_id) {
                    $userEquipeIds[] = $user->equipe_id;
                }
                $userEquipeIds = array_values(array_unique(array_filter($userEquipeIds)));
            } catch (\Throwable $e) {}
        }

        $out = "=== DONNÉES DU PÉRIMÈTRE AUTORISÉ (TEMPS RÉEL) ===\n\n";

        // 1. Équipes techniques
        try {
            if ($isAdmin || $isSupSoutarah) {
                // Admin et Sup Soutarah voient toutes les équipes avec leurs membres
                $equipes = \Illuminate\Support\Facades\DB::table('equipes')->get();
                $out .= "ÉQUIPES SOUTARAH :\n";
                if ($equipes->isEmpty()) {
                    $out .= "- Aucune équipe enregistrée.\n";
                } else {
                    foreach ($equipes as $eq) {
                        $membres = \Illuminate\Support\Facades\DB::table('equipe_user')
                            ->join('utilisateurs', 'equipe_user.user_id', '=', 'utilisateurs.id')
                            ->where('equipe_user.equipe_id', $eq->id)
                            ->select(['utilisateurs.nom', 'utilisateurs.prenom', 'equipe_user.role as role_equipe'])
                            ->get();

                        $membresList = [];
                        foreach ($membres as $m) {
                            $roleLabel = ($m->role_equipe === 'chef') ? 'Chef' : 'Membre';
                            $membresList[] = trim(($m->prenom ?? '') . ' ' . ($m->nom ?? '')) . " ($roleLabel)";
                        }
                        $strMembres = !empty($membresList) ? implode(', ', $membresList) : 'Aucun membre affecté';
                        $out .= "- {$eq->nom_equipe} (" . count($membresList) . " pers.) : $strMembres\n";
                    }
                }
                $out .= "\n";
            } elseif ($isTech && !empty($userEquipeIds)) {
                // Le technicien voit son équipe
                $equipes = \Illuminate\Support\Facades\DB::table('equipes')->whereIn('id', $userEquipeIds)->get();
                $out .= "VOTRE ÉQUIPE TECHNIQUE :\n";
                foreach ($equipes as $eq) {
                    $membres = \Illuminate\Support\Facades\DB::table('equipe_user')
                        ->join('utilisateurs', 'equipe_user.user_id', '=', 'utilisateurs.id')
                        ->where('equipe_user.equipe_id', $eq->id)
                        ->select(['utilisateurs.nom', 'utilisateurs.prenom', 'equipe_user.role as role_equipe'])
                        ->get();
                    $membresList = [];
                    foreach ($membres as $m) {
                        $roleLabel = ($m->role_equipe === 'chef') ? 'Chef' : 'Membre';
                        $membresList[] = trim(($m->prenom ?? '') . ' ' . ($m->nom ?? '')) . " ($roleLabel)";
                    }
                    $out .= "- {$eq->nom_equipe} : " . implode(', ', $membresList) . "\n";
                }
                $out .= "\n";
            }
        } catch (\Throwable $e) {}

        // 2. Demandes d'intervention (filtrées par périmètre)
        try {
            $demandesQuery = \Illuminate\Support\Facades\DB::table('demandes')
                ->leftJoin('clients', 'demandes.client_id', '=', 'clients.id')
                ->leftJoin('sites', 'demandes.site_id', '=', 'sites.id')
                ->leftJoin('equipements', 'demandes.equipement_id', '=', 'equipements.id')
                ->select([
                    'demandes.numero_demande',
                    'clients.nom as client',
                    'sites.nom_site as site',
                    'equipements.equipement_nom as equipement',
                    'demandes.description',
                    'demandes.niveau_urgence',
                    'demandes.statut',
                    'demandes.est_vip',
                    'demandes.created_at',
                ])
                ->orderBy('demandes.id', 'desc');

            if ($isSupClient && $clientId) {
                $demandesQuery->where('demandes.client_id', $clientId);
                if ($baseId) {
                    $demandesQuery->where('demandes.base_id', $baseId);
                }
            } elseif ($isDemandeur) {
                $demandesQuery->where(function($q) use ($user, $siteIds) {
                    $q->where('demandes.created_by_user_id', $user->id);
                    if (!empty($siteIds)) {
                        $q->orWhereIn('demandes.site_id', $siteIds);
                    }
                });
            } elseif ($isSupSoutarah && (!empty($assignedBaseIds) || !empty($assignedClientIds))) {
                $demandesQuery->where(function($q) use ($assignedBaseIds, $assignedClientIds) {
                    if (!empty($assignedBaseIds)) {
                        $q->whereIn('demandes.base_id', $assignedBaseIds);
                    }
                    if (!empty($assignedClientIds)) {
                        $q->orWhereIn('demandes.client_id', $assignedClientIds);
                    }
                });
            }

            $demandes = $demandesQuery->take(15)->get();
            $out .= "DEMANDES D'INTERVENTION (RÉCENTES DE VOTRE PÉRIMÈTRE) :\n";
            if ($demandes->isEmpty()) {
                $out .= "- Aucune demande enregistrée dans votre périmètre.\n";
            } else {
                foreach ($demandes as $d) {
                    $vip = $d->est_vip ? 'OUI (VIP)' : 'Standard';
                    $client = $d->client ?? 'N/C';
                    $site = $d->site ?? 'N/C';
                    $eq = $d->equipement ?? 'Non spécifié';
                    $desc = $d->description ? mb_substr($d->description, 0, 60) : 'N/C';
                    $date = $d->created_at ? date('d/m/Y', strtotime((string)$d->created_at)) : 'N/C';
                    $out .= "- {$d->numero_demande} | Client: $client | Site: $site | Équipement: $eq | Desc: $desc | Urgence: {$d->niveau_urgence} | Statut: {$d->statut} | VIP: $vip | Date: $date\n";
                }
            }
            $out .= "\n";
        } catch (\Throwable $e) {}

        // 3. Dépannages & Interventions curatives (filtrés par périmètre)
        try {
            $depannagesQuery = \Illuminate\Support\Facades\DB::table('depannages')
                ->leftJoin('clients', 'depannages.client_id', '=', 'clients.id')
                ->leftJoin('equipements', 'depannages.equipement_id', '=', 'equipements.id')
                ->leftJoin('utilisateurs', 'depannages.technicien_id', '=', 'utilisateurs.id')
                ->leftJoin('equipes', 'depannages.equipe_id', '=', 'equipes.id')
                ->select([
                    'depannages.id',
                    'clients.nom as client',
                    'equipements.equipement_nom as equipement',
                    'utilisateurs.nom as tech_nom',
                    'utilisateurs.prenom as tech_prenom',
                    'equipes.nom_equipe as equipe',
                    'depannages.urgence',
                    'depannages.statut',
                    'depannages.date_prevue',
                ])
                ->orderBy('depannages.id', 'desc');

            if ($isSupClient && $clientId) {
                $depannagesQuery->where('depannages.client_id', $clientId);
            } elseif ($isDemandeur && !empty($siteIds)) {
                $depannagesQuery->whereIn('depannages.site_id', $siteIds);
            } elseif ($isTech) {
                $depannagesQuery->where(function($q) use ($user, $userEquipeIds) {
                    $q->where('depannages.technicien_id', $user->id);
                    if (!empty($userEquipeIds)) {
                        $q->orWhereIn('depannages.equipe_id', $userEquipeIds);
                    }
                });
            } elseif ($isSupSoutarah && (!empty($assignedBaseIds) || !empty($assignedClientIds))) {
                $depannagesQuery->where(function($q) use ($assignedBaseIds, $assignedClientIds) {
                    if (!empty($assignedClientIds)) {
                        $q->whereIn('depannages.client_id', $assignedClientIds);
                    }
                });
            }

            $depannages = $depannagesQuery->take(15)->get();
            $out .= "DÉPANNAGES & INTERVENTIONS CURATIVES :\n";
            if ($depannages->isEmpty()) {
                $out .= "- Aucun dépannage actif dans votre périmètre.\n";
            } else {
                foreach ($depannages as $dep) {
                    $assigne = $dep->equipe ?: trim(($dep->tech_prenom ?? '') . ' ' . ($dep->tech_nom ?? ''));
                    if (empty($assigne)) $assigne = 'Non assigné';
                    $client = $dep->client ?? 'N/C';
                    $eq = $dep->equipement ?? 'N/C';
                    $date = $dep->date_prevue ? date('d/m/Y', strtotime((string)$dep->date_prevue)) : 'N/C';
                    $out .= "- DEP-{$dep->id} | Client: $client | Équipement: $eq | Assigné: $assigne | Urgence: {$dep->urgence} | Statut: {$dep->statut} | Date: $date\n";
                }
            }
            $out .= "\n";
        } catch (\Throwable $e) {}

        // 4. Maintenances préventives (filtrées par périmètre)
        try {
            $maintenancesQuery = \Illuminate\Support\Facades\DB::table('maintenances')
                ->leftJoin('clients', 'maintenances.client_id', '=', 'clients.id')
                ->leftJoin('sites', 'maintenances.site_id', '=', 'sites.id')
                ->select([
                    'maintenances.numero_maintenance',
                    'clients.nom as client',
                    'sites.nom_site as site',
                    'maintenances.statut',
                    'maintenances.nombre_equipements_prevus',
                    'maintenances.nombre_equipements_traites',
                    'maintenances.date_debut_prevue',
                ])
                ->orderBy('maintenances.id', 'desc');

            if ($isSupClient && $clientId) {
                $maintenancesQuery->where('maintenances.client_id', $clientId);
                if ($baseId) {
                    $maintenancesQuery->where('maintenances.base_id', $baseId);
                }
            } elseif ($isDemandeur && !empty($siteIds)) {
                $maintenancesQuery->whereIn('maintenances.site_id', $siteIds);
            } elseif ($isTech && !empty($userEquipeIds)) {
                $maintenancesQuery->whereIn('maintenances.equipe_id', $userEquipeIds);
            }

            $maintenances = $maintenancesQuery->take(10)->get();
            $out .= "MAINTENANCES PRÉVENTIVES DE VOTRE PÉRIMÈTRE :\n";
            if ($maintenances->isEmpty()) {
                $out .= "- Aucune maintenance planifiée dans votre périmètre.\n";
            } else {
                foreach ($maintenances as $m) {
                    $client = $m->client ?? 'N/C';
                    $site = $m->site ?? 'N/C';
                    $pct = $m->nombre_equipements_prevus > 0 ? round(($m->nombre_equipements_traites / $m->nombre_equipements_prevus) * 100) : 0;
                    $date = $m->date_debut_prevue ? date('d/m/Y', strtotime((string)$m->date_debut_prevue)) : 'N/C';
                    $out .= "- {$m->numero_maintenance} | Client: $client | Site: $site | Statut: {$m->statut} | Avancement: $pct% | Début: $date\n";
                }
            }
            $out .= "\n";
        } catch (\Throwable $e) {}

        // 5. Équipements du parc (filtrés par périmètre)
        try {
            $equipementsQuery = \Illuminate\Support\Facades\DB::table('equipements')
                ->leftJoin('clients', 'equipements.client_id', '=', 'clients.id')
                ->leftJoin('sites', 'equipements.site_id', '=', 'sites.id')
                ->select([
                    'equipements.equipement_code',
                    'equipements.equipement_nom',
                    'equipements.marque',
                    'equipements.type',
                    'equipements.emplacement',
                    'clients.nom as client',
                    'sites.nom_site as site',
                    'equipements.etat',
                ]);

            if ($isSupClient && $clientId) {
                $equipementsQuery->where('equipements.client_id', $clientId);
                if ($baseId) {
                    $equipementsQuery->where('equipements.base_id', $baseId);
                }
            } elseif ($isDemandeur && !empty($siteIds)) {
                $equipementsQuery->whereIn('equipements.site_id', $siteIds);
            }

            $equipements = $equipementsQuery->take(15)->get();
            $out .= "ÉQUIPEMENTS RÉPERTORIÉS DANS VOTRE PÉRIMÈTRE :\n";
            if ($equipements->isEmpty()) {
                $out .= "- Aucun équipement spécifique enregistré dans ce périmètre.\n";
            } else {
                foreach ($equipements as $eq) {
                    $code = $eq->equipement_code ?? 'EQ';
                    $out .= "- $code | {$eq->equipement_nom} ({$eq->marque}, {$eq->type}) | Emplacement: {$eq->emplacement} | Site: {$eq->site} | État: {$eq->etat}\n";
                }
            }
            $out .= "\n";
        } catch (\Throwable $e) {}

        // 6. Clients & Sites autorisés
        try {
            if ($isAdmin || $isSupSoutarah) {
                $clients = \App\Models\Client::with('bases.sites')->get();
                $out .= "CLIENTS & SITES ENREGISTRÉS :\n";
                foreach ($clients as $c) {
                    $siteNames = [];
                    foreach ($c->bases ?? [] as $b) {
                        foreach ($b->sites ?? [] as $s) {
                            $siteNames[] = $s->nom_site;
                        }
                    }
                    $strSites = !empty($siteNames) ? implode(', ', array_unique($siteNames)) : 'Siège principal';
                    $out .= "- {$c->nom} (Sites: $strSites)\n";
                }
            } elseif ($isSupClient && $clientId) {
                $client = \App\Models\Client::with('bases.sites')->find($clientId);
                if ($client) {
                    $siteNames = [];
                    foreach ($client->bases ?? [] as $b) {
                        foreach ($b->sites ?? [] as $s) {
                            $siteNames[] = $s->nom_site;
                        }
                    }
                    $strSites = !empty($siteNames) ? implode(', ', array_unique($siteNames)) : 'Sites principaux';
                    $out .= "VOTRE ENTREPRISE : {$client->nom} (Sites : $strSites)\n";
                }
            } elseif ($isDemandeur && !empty($siteIds)) {
                $sites = \App\Models\Site::whereIn('id', $siteIds)->pluck('nom_site')->toArray();
                $out .= "VOS SITES ASSIGNÉS : " . implode(', ', $sites) . "\n";
            }
            $out .= "\n";
        } catch (\Throwable $e) {}

        return $out;
    }

    /**
     * Génère l'instruction système (System Prompt) avec équilibre parfait :
     * 1. Liberté totale & excellence sur les questions techniques, de dépannage et méthodologiques.
     * 2. Cloisonnement strict & étanche sur les données réelles de la plateforme Mainteo.
     * 3. Discernement et courtoisie quand une question concerne les attributions d'un autre rôle.
     */
    protected function buildSystemInstruction(User $user, array $context): string
    {
        $nom = $context['user_name'];
        $role = $context['role'];
        $roleLabel = $context['role_label'];
        
        $detailsPerimetre = "";
        if (!empty($context['client_nom'])) {
            $detailsPerimetre .= "- Entreprise rattachée : {$context['client_nom']}\n";
        }
        if (!empty($context['base_nom'])) {
            $detailsPerimetre .= "- Base rattachée : {$context['base_nom']}\n";
        }
        if (!empty($context['sites'])) {
            $detailsPerimetre .= "- Sites assignés : " . implode(', ', $context['sites']) . "\n";
        }
        if (!empty($context['equipes'])) {
            $detailsPerimetre .= "- Équipe(s) d'appartenance : " . implode(', ', $context['equipes']) . "\n";
        }

        $dbText = $this->buildCompactDatabaseText($user);

        return <<<EOT
Tu es **Mainteo IA**, l'assistant conversationnel expert et copilote central de la plateforme GMAO **Mainteo** pour l'entreprise **Soutarah** (Côte d'Ivoire), spécialiste de la maintenance industrielle, du génie climatique, du froid commercial et des installations techniques.

### PROFIL DE L'UTILISATEUR CONNECTÉ :
- Nom : {$nom}
- Rôle : **{$roleLabel}** ({$role})
{$detailsPerimetre}

### RÈGLES ESSENTIELLES DE COMPORTEMENT :

1. **QUESTIONS TECHNIQUES, DÉPANNAGE ET MÉTIER (ACCÈS COMPLET ET SANS RESTRICTION)** :
Tu réponds AVEC EXCELLENCE, PÉDAGOGIE ET SANS AUCUNE RESTRICTION à TOUTES les questions techniques et scientifiques, quel que soit l'utilisateur qui te le demande :
- Assistance au diagnostic de pannes (climatisation, froid commercial, chambres froides, compresseurs, ventilation, électricité industrielle, régulation).
- Explication des causes possibles d'un dysfonctionnement (ex: compresseur qui disjoncte, givre sur l'évaporateur, surchauffe, pressostats HP/BP, fuite de fluide frigorigène R410A, R32, R134a, R404A...).
- Conseils pratiques de sécurité, de dépannage, d'entretien, calculs frigorifiques et méthodologies.
Tu es un copilote technique bienveillant, brillant et d'une grande aide pour tous.

2. **DONNÉES RÉELLES DE LA GMAO MAINTEO (PÉRIMÈTRE STRICT ET ÉTANCHE)** :
Pour toute question interrogeant les données réelles de la plateforme (tickets de panne réels, numéros de demandes, état des équipements réels, interventions, plannings) :
- Tu ne disposes et ne dois communiquer QUE les données réelles faisant partie du périmètre autorisé de {$nom} (fournies dans la section ci-dessous).
- Si l'utilisateur te demande des données sur une autre entreprise cliente, un autre site ou des données qui ne figurent pas dans son périmètre : ne divulgue rien et n'invente rien. Dis-lui avec courtoisie que tu n'as accès qu'aux données associées à son périmètre autorisé ({$roleLabel}).

3. **DISCERNEMENT DES RÔLES ET ATTRIBUTIONS (ORIENTER AVEC COURTOISIE)** :
Chaque acteur a un rôle précis dans le fonctionnement de Mainteo :
- **Demandeur** : Opérateur sur site client. Il signale des pannes sur ses sites et suit ses tickets. Il ne gère pas les plannings des équipes Soutarah, les validations VIP (réservées au Superviseur Client), ni les contrats des autres sites ou entreprises.
- **Superviseur Client** : Responsable côté entreprise cliente. Il valide les demandes de son entreprise, gère la priorité VIP, suit les travaux chez lui et valide la conformité finale des interventions. Il n'a pas accès aux données d'autres entreprises clientes ni à la gestion RH interne de Soutarah.
- **Technicien / Chef d'équipe** : Intervenant terrain de Soutarah. Il intervient sur site, remplit les comptes-rendus journaliers, diagnostique et remplace les pièces. Il ne gère pas la facturation client ni les affaires des autres équipes.
- **Superviseur Soutarah / Admin** : Planifie, affecte les équipes et supervise les opérations sur son périmètre.

**RÈGLE D'ATTRIBUTION** : Si un utilisateur te pose une question métier ou de gestion qui relève manifestement des responsabilités, décisions ou prérogatives d'un AUTRE rôle (par exemple un demandeur qui te demande d'attribuer une équipe, de valider un ticket en VIP, ou de lui donner le planning interne des techniciens Soutarah) :
- Ne sois pas agressif et ne refuse pas brutalement.
- Explique-lui avec bienveillance et clarté à quel rôle revient cette attribution dans le flux Mainteo (ex: *"En tant que Demandeur, cette validation / décision relève des attributions de votre Superviseur Client..."*).
- Guide-le vers ce qu'il peut faire concrètement à son niveau depuis son interface.

4. **FORMAT ET STYLE DE RÉPONSE** :
- Sois chaleureux, concis, professionnel et structuré.
- Dès que tu présentes des listes de données réelles (demandes, pannes, équipements), utilise des **beaux tableaux Markdown** bien soignés avec colonnes claires.

{$dbText}
EOT;
    }

    protected function generateLocalFallbackResponse(User $user, string $userMessage, array $context): string
    {
        return "⚠️ Le service IA distant n'a pas pu répondre à cet instant précis (erreur de communication réseau). Veuillez réessayer dans quelques secondes.";
    }

    protected function getRoleLabel(string $role): string
    {
        return match ($role) {
            'admin'               => 'Administrateur',
            'superviseur_soutarah'=> 'Superviseur Soutarah',
            'superviseur_client'  => 'Superviseur Client',
            'chef technicien'     => 'Chef d\'équipe Technique',
            'technicien'          => 'Technicien Terrain',
            'demandeur'           => 'Demandeur sur Site',
            default               => ucfirst($role),
        };
    }
}
