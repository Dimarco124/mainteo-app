<?php

namespace App\Imports;

use App\Models\ZoneSite;
use App\Models\Site;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;

class EmplacementsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, WithBatchInserts, WithChunkReading
{
    use SkipsErrors;

    private $importedCount = 0;
    private $updatedCount = 0;
    private $customErrors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $codeBase = $row['code_base'] ?? null;
            $codeSite = $row['code_site'] ?? null;
            $codeClient = $row['code_client'] ?? null;
            $nomEmplacement = $row['nom_emplacement'] ?? null;
            $codeEmplacement = $row['code_emplacement'] ?? null;

            // Vérifier qu'on a au moins un site OU un client
            if (empty($codeSite) && empty($codeClient)) {
                $this->customErrors[] = "Ligne : Code site OU Code client obligatoire";
                continue;
            }

            $siteId = null;
            $baseId = null;
            $clientId = null;

            // CAS 1 : Structure avec Site (Client → Base → Site → Emplacement)
            if (!empty($codeSite)) {
                $siteQuery = Site::where('code_site', $codeSite);
                
                // Si code_base fourni, filtrer par base pour éviter les ambiguïtés
                if (!empty($codeBase)) {
                    $base = \App\Models\BaseSite::where('code_base', $codeBase)->first();
                    if ($base) {
                        $siteQuery->where('base_id', $base->id);
                    } else {
                        $this->customErrors[] = "Ligne : Base '{$codeBase}' introuvable";
                        continue;
                    }
                }
                
                $site = $siteQuery->first();

                if (!$site) {
                    $baseInfo = !empty($codeBase) ? " dans la base '{$codeBase}'" : '';
                    $this->customErrors[] = "Ligne : Site '{$codeSite}'{$baseInfo} introuvable";
                    continue;
                }

                $siteId = $site->id;
                $baseId = $site->base_id;
                $clientId = $site->client_id ?? ($site->baseSite->client_id ?? null);
            }
            // CAS 2 : Structure directe (Client → Emplacement, sans base ni site)
            elseif (!empty($codeClient)) {
                $client = \App\Models\Client::where('code', $codeClient)->first();

                if (!$client) {
                    $this->customErrors[] = "Ligne : Client '{$codeClient}' introuvable";
                    continue;
                }

                $clientId = $client->id;
                $siteId = null;
                $baseId = null;
            }

            // Vérifier si l'emplacement existe déjà
            $emplacementQuery = ZoneSite::query();
            
            if ($siteId) {
                $emplacementQuery->where('site_id', $siteId);
            } else {
                $emplacementQuery->where('client_id', $clientId)->whereNull('site_id');
            }

            // Recherche par code_zone en priorité, sinon par nom_zone
            if (!empty($codeEmplacement)) {
                $emplacement = $emplacementQuery->where('code_zone', $codeEmplacement)->first();
            } else {
                $emplacement = $emplacementQuery->where('nom_zone', $nomEmplacement)->first();
            }

            $data = [
                'site_id'      => $siteId,
                'base_id'      => $baseId,
                'client_id'    => $clientId,
                'nom_zone'     => $nomEmplacement,
                'code_zone'    => $codeEmplacement ?? \App\Helpers\CodeGenerator::generateEmplacementCode($nomEmplacement),
                'observations' => $row['observations'] ?? null,
            ];

            if ($emplacement) {
                $emplacement->update($data);
                $this->updatedCount++;
            } else {
                ZoneSite::create($data);
                $this->importedCount++;
            }
        }
    }

    public function rules(): array
    {
        return [
            'code_base'        => 'nullable|string|max:50',
            'code_site'        => 'nullable|string|max:50',
            'code_client'      => 'nullable|string|max:50',
            'nom_emplacement' => 'required|string|max:255',
            'code_emplacement' => 'nullable|string|max:50',
            'observations'     => 'nullable|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nom_emplacement.required' => 'Le nom de l\'emplacement est obligatoire',
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
