<?php

namespace App\Imports;

use App\Models\Site;
use App\Models\BaseSite;
use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;

class SitesImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, WithBatchInserts, WithChunkReading
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
            $baseId = null;
            $clientId = null;

            // Déterminer la structure : avec base OU directement avec client
            if (!empty($row['code_base'])) {
                // Structure normale : Client → Base → Site
                // Si code_client est fourni, restreindre la recherche pour éviter les collisions inter-entreprises
                $baseQuery = BaseSite::where('code_base', $row['code_base']);
                if (!empty($row['code_client'])) {
                    $clientPrefilter = Client::where('code', $row['code_client'])->first();
                    if ($clientPrefilter) {
                        $baseQuery->where('client_id', $clientPrefilter->id);
                    }
                }
                $base = $baseQuery->first();

                if (!$base) {
                    $this->customErrors[] = "Ligne : Base avec code '{$row['code_base']}'" . (!empty($row['code_client']) ? " pour le client '{$row['code_client']}'" : '') . " introuvable";
                    continue;
                }

                $baseId = $base->id;
                $clientId = $base->client_id;
            } elseif (!empty($row['code_client'])) {
                // Structure directe : Client → Site (sans base)
                $client = Client::where('code', $row['code_client'])->first();

                if (!$client) {
                    $this->customErrors[] = "Ligne : Client avec code '{$row['code_client']}' introuvable";
                    continue;
                }

                $clientId = $client->id;
                $baseId = null;
            } else {
                $this->customErrors[] = "Ligne : Code base OU code client obligatoire";
                continue;
            }

            // Vérifier si le site existe déjà POUR CE CLIENT OU BASE
            $site = Site::where('code_site', $row['code_site'])
                ->where(function ($q) use ($clientId, $baseId) {
                    if ($baseId) {
                        $q->where('base_id', $baseId);
                    } elseif ($clientId) {
                        $q->where('client_id', $clientId);
                    }
                })
                ->first();

            $data = [
                'client_id' => $clientId,
                'base_id' => $baseId,
                'code_site' => $row['code_site'],
                'nom_site' => $row['nom_site'],
                'adresse' => $row['adresse'] ?? null,
                'ville' => $row['ville'] ?? null,
                'telephone' => $row['telephone'] ?? null,
                'observations' => $row['observations'] ?? null,
            ];

            if ($site) {
                // Mise à jour
                $site->update($data);
                $this->updatedCount++;
            } else {
                // Création
                Site::create($data);
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
            'code_base' => 'nullable|string|max:50',
            'code_client' => 'nullable|string|max:50',
            'code_site' => 'required|string|max:50',
            'nom_site' => 'required|string|max:255',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:50',
            'observations' => 'nullable|string',
        ];
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function customValidationMessages()
    {
        return [
            'code_base.required' => 'Le code base est obligatoire',
            'code_site.required' => 'Le code site est obligatoire',
            'nom_site.required' => 'Le nom du site est obligatoire',
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
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
}
