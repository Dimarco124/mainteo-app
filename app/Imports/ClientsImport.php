<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ClientsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, WithBatchInserts, WithChunkReading
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
            // Vérifier si le client existe déjà (par code)
            $client = Client::where('code', $row['code'])->first();

            if ($client) {
                // Mise à jour
                $client->update([
                    'nom' => $row['nom'],
                    'telephone' => $row['telephone'] ?? null,
                    'mail' => $row['email'] ?? $row['mail'] ?? null,
                    'adresse' => $row['adresse'] ?? null,
                    'ville' => $row['ville'] ?? null,
                    'pays' => $row['pays'] ?? 'Sénégal',
                ]);
                $this->updatedCount++;
            } else {
                // Création
                Client::create([
                    'code' => $row['code'],
                    'nom' => $row['nom'],
                    'telephone' => $row['telephone'] ?? null,
                    'mail' => $row['email'] ?? $row['mail'] ?? null,
                    'adresse' => $row['adresse'] ?? null,
                    'ville' => $row['ville'] ?? null,
                    'pays' => $row['pays'] ?? 'Sénégal',
                    'date_creation' => now(),
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
            'code' => 'required|string|max:50',
            'nom' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'mail' => 'nullable|email|max:255',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'pays' => 'nullable|string|max:100',
        ];
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function customValidationMessages()
    {
        return [
            'code.required' => 'Le code client est obligatoire',
            'nom.required' => 'Le nom du client est obligatoire',
            'email.email' => 'L\'email doit être valide',
            'mail.email' => 'L\'email doit être valide',
        ];
    }

    /**
     * Batch inserts (traiter par lots de 1000)
     */
    public function batchSize(): int
    {
        return 1000;
    }

    /**
     * Chunk reading (lire par morceaux de 1000)
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * Récupérer le nombre d'imports
     */
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    /**
     * Récupérer le nombre de mises à jour
     */
    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    /**
     * Récupérer les erreurs
     */
    public function getErrors(): array
    {
        return $this->customErrors;
    }
}
