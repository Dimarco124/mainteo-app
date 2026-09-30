<?php

namespace App\Imports;

use App\Models\Equipement;
use App\Models\Site;
use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;

class EquipementsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, WithBatchInserts, WithChunkReading
{
    use SkipsErrors;

    private $importedCount = 0;
    private $updatedCount = 0;
    private $customErrors = [];

    /**
     * Traiter la collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Ignorer les lignes vides ou sans code équipement
            if (empty($row['code_equipement'])) {
                continue; // Passer à la ligne suivante sans erreur
            }
            
            // Déterminer la structure (avec site OU emplacement OU entreprise directe)
            $siteId = null;
            $baseId = null;  
            $clientId = null;
            $zoneId = null;

            $codeEmplacement = !empty($row['code_emplacement']) ? $row['code_emplacement'] : (!empty($row['code_zone']) ? $row['code_zone'] : null);

            if (!empty($row['code_site'])) {
                // Structure normale OU Structure 3 : Client → Site → Équipement
                // code_client EST OBLIGATOIRE pour isoler correctement les entreprises
                if (empty($row['code_client'])) {
                    $this->customErrors[] = "Ligne code_equipement '{$row['code_equipement']}' : La colonne 'Code Client' est obligatoire même quand 'Code Site' est fourni.";
                    continue;
                }

                $clientPrefilter = Client::where('code', $row['code_client'])->first();
                if (!$clientPrefilter) {
                    $this->customErrors[] = "Ligne : Client avec code '{$row['code_client']}' introuvable";
                    continue;
                }

                $siteQuery = Site::with(['baseSite'])->where('code_site', $row['code_site']);

                // Si code_base est aussi fourni (pour différencier deux sites de même code dans 2 bases différentes)
                if (!empty($row['code_base'])) {
                    $basePrefilter = \App\Models\BaseSite::where('code_base', $row['code_base'])
                        ->where('client_id', $clientPrefilter->id)
                        ->first();

                    if ($basePrefilter) {
                        $siteQuery->where('base_id', $basePrefilter->id);
                    } else {
                        $this->customErrors[] = "Ligne : Base avec code '{$row['code_base']}' pour le client '{$row['code_client']}' introuvable";
                        continue;
                    }
                } else {
                    $siteQuery->where(function ($q) use ($clientPrefilter) {
                        $q->where('client_id', $clientPrefilter->id)
                          ->orWhereHas('baseSite', function ($bq) use ($clientPrefilter) {
                              $bq->where('client_id', $clientPrefilter->id);
                          });
                    });
                }

                $site = $siteQuery->first();

                if (!$site) {
                    $baseInfo = !empty($row['code_base']) ? " dans la base '{$row['code_base']}'" : '';
                    $this->customErrors[] = "Ligne : Site avec code '{$row['code_site']}'{$baseInfo} pour le client '{$row['code_client']}' introuvable";
                    continue;
                }

                $siteId = $site->id;

                // Déterminer client_id et base_id selon la structure du site
                if ($site->base_id) {
                    $baseId = $site->base_id;
                    $clientId = $site->baseSite ? $site->baseSite->client_id : $clientPrefilter->id;
                } else {
                    $baseId = null;
                    $clientId = $site->client_id ?? $clientPrefilter->id;
                }

                // Résoudre la zone si code_emplacement est fourni
                if (!empty($codeEmplacement)) {
                    $zone = \App\Models\ZoneSite::where('code_zone', $codeEmplacement)
                        ->where('site_id', $siteId)
                        ->first();
                    if ($zone) {
                        $zoneId = $zone->id;
                    } else {
                        $this->customErrors[] = "Ligne : Emplacement avec code '{$codeEmplacement}' introuvable sur le site '{$row['code_site']}'";
                        continue;
                    }
                }
            } elseif (!empty($codeEmplacement)) {
                // Emplacement direct : Code Emplacement → Site → Base → Client
                $zone = \App\Models\ZoneSite::where('code_zone', $codeEmplacement)->first();
                if ($zone) {
                    $zoneId = $zone->id;
                    $siteId = $zone->site_id;
                    $baseId = $zone->base_id;
                    $clientId = $zone->client_id;
                } else {
                    $this->customErrors[] = "Ligne : Emplacement avec code '{$codeEmplacement}' introuvable";
                    continue;
                }
            } elseif (!empty($row['code_client'])) {
                // Structure directe : Client → Équipement (sans base ni site)
                $client = Client::where('code', $row['code_client'])->first();

                if (!$client) {
                    $this->customErrors[] = "Ligne : Client avec code '{$row['code_client']}' introuvable";
                    continue;
                }

                $clientId = $client->id;
                $siteId = null;
                $baseId = null;
                $zoneId = null;
            } else {
                $this->customErrors[] = "Ligne : Code site, Code emplacement OU Code client obligatoire";
                continue;
            }

            // Vérifier si l'équipement existe déjà POUR CE CLIENT OU SITE
            $equipement = Equipement::where('equipement_code', $row['code_equipement'])
                ->where(function ($q) use ($clientId, $siteId) {
                    if ($siteId) {
                        $q->where('site_id', $siteId);
                    } elseif ($clientId) {
                        $q->where('client_id', $clientId);
                    }
                })
                ->first();

            // Normaliser le type d'équipement
            $type = null;
            if (!empty($row['type'])) {
                $typeRaw = trim((string)$row['type']);
                // Normaliser les variations communes
                $type = $this->normalizeType($typeRaw);
            }

            $data = [
                'client_id' => $clientId,
                'base_id' => $baseId,
                'site_id' => $siteId,
                'zone_id' => $zoneId,
                'equipement_code' => $row['code_equipement'],
                'equipement_numero' => !empty($row['numero']) ? (string)$row['numero'] : null,
                'equipement_nom' => $row['nom'] ?? 'Équipement sans nom',
                'marque' => !empty($row['marque']) ? (string)$row['marque'] : null,
                'type' => $type,
                'emplacement' => !empty($row['emplacement']) ? (string)$row['emplacement'] : null,
                'puissance' => !empty($row['puissance']) ? (string)$row['puissance'] : null,
                'refrigerant' => !empty($row['refrigerant']) ? (string)$row['refrigerant'] : null,
                'annee_installation' => !empty($row['annee_installation']) ? (int)$row['annee_installation'] : null,
                'etat' => !empty($row['etat']) ? (string)$row['etat'] : 'En service',
                'observations' => !empty($row['observations']) ? (string)$row['observations'] : null,
            ];

            if ($equipement) {
                // Mise à jour
                $equipement->update($data);
                $this->updatedCount++;
            } else {
                // Création
                Equipement::create($data);
                $this->importedCount++;
            }
        }
    }

    /**
     * Règles de validation
     */
    public function rules(): array
    {
        return [
            'code_equipement' => 'nullable|string|max:100', // Changé en nullable
            'nom' => 'nullable|string|max:255', // Changé en nullable
            'code_base' => 'nullable|string|max:50',
            'code_site' => 'nullable|string|max:50',
            'code_client' => 'nullable|string|max:50',
            'numero' => 'nullable', // Accepte tout type
            'marque' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'emplacement' => 'nullable|string|max:255',
            'puissance' => 'nullable', // Accepte tout type
            'refrigerant' => 'nullable', // Accepte tout type
            'annee_installation' => 'nullable', // Accepte tout type
            'etat' => 'nullable|string|max:50',
            'observations' => 'nullable|string',
        ];
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function customValidationMessages()
    {
        return [
            'code_equipement.required' => 'Le code équipement est obligatoire',
            'nom.required' => 'Le nom de l\'équipement est obligatoire',
        ];
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getErrors(): array
    {
        return $this->customErrors;
    }

    /**
     * Normaliser les types d'équipements pour éviter les doublons
     */
    private function normalizeType(string $type): string
    {
        // Convertir en minuscules pour comparaison
        $typeLower = strtolower(trim($type));
        
        // Mapping des variations vers les types standards
        $typeMapping = [
            // Split variations
            'split' => 'Split',
            'splits' => 'Split',
            'climatiseur split' => 'Split',
            'clim split' => 'Split',
            
            // Gainable variations
            'gainable' => 'Gainable',
            'gainables' => 'Gainable',
            'climatiseur gainable' => 'Gainable',
            
            // Windows variations
            'window' => 'Windows',
            'windows' => 'Windows',
            'fenetre' => 'Windows',
            'climatiseur fenêtre' => 'Windows',
            
            // Armoire variations
            'armoire' => 'Armoire',
            'armoires' => 'Armoire',
            'climatiseur armoire' => 'Armoire',
            
            // GF variations (Groupe Froid)
            'gf' => 'GF',
            'groupe froid' => 'GF',
            'groupes froids' => 'GF',
        ];
        
        // Chercher dans le mapping
        if (isset($typeMapping[$typeLower])) {
            return $typeMapping[$typeLower];
        }
        
        // Si pas trouvé, retourner avec majuscule initiale
        return ucfirst($typeLower);
    }
}
