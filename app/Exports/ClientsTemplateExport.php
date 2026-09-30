<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ClientsTemplateExport implements FromArray, WithHeadings, WithStyles
{
    /**
     * Retourner des données d'exemple
     */
    public function array(): array
    {
        return [
            [
                'CLI001',
                'Entreprise Exemple SARL',
                '+221 33 123 45 67',
                'contact@exemple.sn',
                '123 Avenue Example',
                'Dakar',
                'Sénégal',
            ],
            [
                'CLI002',
                'Société Test SA',
                '+221 77 987 65 43',
                'info@test.sn',
                '456 Rue Test',
                'Thiès',
                'Sénégal',
            ],
        ];
    }

    /**
     * En-têtes du fichier
     */
    public function headings(): array
    {
        return [
            'Code',
            'Nom',
            'Telephone',
            'Email',
            'Adresse',
            'Ville',
            'Pays',
        ];
    }

    /**
     * Styles du fichier
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }
}
