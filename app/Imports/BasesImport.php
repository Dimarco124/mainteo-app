<?php

namespace App\Imports;

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
use Illuminate\Validation\Rule;

class BasesImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, WithBatchInserts, WithChunkReading
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
            // Trouver le client par code
            $client = Client::where('code', $row['code_client'])->first();

            if (!$client) {
                $this->customErrors[] = "Ligne : Client avec code '{$row['code_client']}' introuvable";
                continue;
            }

            // Vérifier si la base existe déjà POUR CE CLIENT
            $base = BaseSite::where('code_base', $row['code_base'])
                ->where('client_id', $client->id)
                ->first();

            if ($base) {
                // Mise à jour
                $base->update([
                    'client_id' => $client->id,
                    'nom_base' => $row['nom_base'],
                ]);
                $this->updatedCount++;
            } else {
                // Création
                BaseSite::create([
                    'client_id' => $client->id,
                    'code_base' => $row['code_base'],
                    'nom_base' => $row['nom_base'],
                ]);
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
            'code_client' => 'required|string|max:50',
            'code_base' => 'required|string|max:50',
            'nom_base' => 'required|string|max:255',
        ];
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function customValidationMessages()
    {
        return [
            'code_client.required' => 'Le code client est obligatoire',
            'code_base.required' => 'Le code base est obligatoire',
            'nom_base.required' => 'Le nom de la base est obligatoire',
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
