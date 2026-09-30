<?php

namespace App\Exports;

use App\Models\Equipement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EquipementsExistingDataExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        $user = auth()->user();
        $query = Equipement::with(['site.baseSite.client', 'site.client', 'baseSite.client', 'client', 'zone']);

        // Filtrer selon le rôle de l'utilisateur
        if ($user && $user->type_utilisateur === 'superviseur_client') {
            if ($user->base_id) {
                $query->whereHas('site', function($q) use ($user) {
                    $q->where('base_id', $user->base_id);
                });
            } elseif ($user->client_id) {
                $query->where('client_id', $user->client_id);
            }
        } elseif ($user && $user->type_utilisateur === 'superviseur_soutarah') {
            $assignment = \App\Models\Assignment::where('superviseur_soutarah_id', $user->id)->first();
            if ($assignment) {
                if ($assignment->base_id) {
                    $query->whereHas('site', function($q) use ($assignment) {
                        $q->where('base_id', $assignment->base_id);
                    });
                } elseif ($assignment->client_id) {
                    $query->whereHas('site.baseSite', function($q) use ($assignment) {
                        $q->where('client_id', $assignment->client_id);
                    });
                }
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Conserver l'ordre exact d'enregistrement dans la base de données
        $equipements = $query->orderBy('id', 'asc')->get();

        return $equipements->map(function ($e) {
            // Résoudre le Code Base
            $codeBase = '';
            if ($e->site && $e->site->baseSite) {
                $codeBase = $e->site->baseSite->code_base;
            } elseif ($e->baseSite) {
                $codeBase = $e->baseSite->code_base;
            }

            // Résoudre le Code Site
            $codeSite = $e->site ? $e->site->code_site : '';

            // Code Emplacement : Laissé VIDE pour que l'utilisateur le saisisse dans Excel
            $codeEmplacement = '';

            // Résoudre le Code Client
            $codeClient = '';
            if ($e->site && $e->site->baseSite && $e->site->baseSite->client) {
                $codeClient = $e->site->baseSite->client->code;
            } elseif ($e->site && $e->site->client) {
                $codeClient = $e->site->client->code;
            } elseif ($e->client) {
                $codeClient = $e->client->code;
            } elseif ($e->baseSite && $e->baseSite->client) {
                $codeClient = $e->baseSite->client->code;
            }

            return [
                'code_base'          => $codeBase,
                'code_site'          => $codeSite,
                'code_emplacement'   => $codeEmplacement, // Volontairement vide
                'code_client'        => $codeClient,
                'code_equipement'    => $e->equipement_code,
                'numero'             => $e->equipement_numero,
                'nom'                => $e->equipement_nom,
                'marque'             => $e->marque,
                'type'               => $e->type,
                'position'           => $e->emplacement,
                'puissance'          => $e->puissance,
                'refrigerant'        => $e->refrigerant,
                'annee_installation' => $e->annee_installation,
                'etat'               => $e->etat,
                'observations'       => $e->observations,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Code Base',
            'Code Site',
            'Code Emplacement',
            'Code Client',
            'Code Equipement',
            'Numero',
            'Nom',
            'Marque',
            'Type',
            'Position (Interne/Externe)',
            'Puissance',
            'Refrigerant',
            'Annee Installation',
            'Etat',
            'Observations',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'] // Vert Émeraude Mainteo
                ],
            ],
        ];
    }
}
