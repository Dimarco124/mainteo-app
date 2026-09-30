<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SitesTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            // Exemple 1 : Site avec base (Structure 1) - TOUJOURS remplir Code Client + Code Base
            [
                'BASE01',
                'CLI001',
                'SITE001',
                'Site Central',
                '10 Avenue de la République',
                'Dakar',
                '+221 33 111 22 33',
                'Site principal avec 5 équipements',
            ],
            // Exemple 2 : Site direct client sans base (Structure 2) - Code Base vide, Code Client rempli
            [
                '',
                'CLI002',
                'SITE002',
                'Site Annexe',
                '25 Rue du Commerce',
                'Thiès',
                '+221 33 444 55 66',
                'Entreprise directe sans base',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Code Base',
            'Code Client',
            'Code Site',
            'Nom Site',
            'Adresse',
            'Ville',
            'Telephone',
            'Observations',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '7c3aed']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }
}
