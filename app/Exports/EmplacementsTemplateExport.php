<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EmplacementsTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            // Exemple 1 : Structure avec Base et Site
            [
                'BASE01',
                'SITE001',
                '',
                'BUR-101',
                'Bureau 101',
                'Premier étage aile Ouest',
            ],
            [
                'BASE01',
                'SITE001',
                '',
                'VIL-04',
                'Villa 4',
                'Résidence des cadres',
            ],
            // Exemple 2 : Structure directe (Client → Emplacement, sans base ni site)
            [
                '',
                '',
                'CLI001',
                'EMP-001',
                'Entrepôt Principal',
                'Client sans structure de sites',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Code Base',
            'Code Site',
            'Code Client',
            'Code Emplacement',
            'Nom Emplacement',
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
                    'startColor' => ['rgb' => '8B5CF6']
                ],
            ],
        ];
    }
}
