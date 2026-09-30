<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EquipementsTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            // Exemple 1 : Structure 1 (Client → Base → Site → Emplacement → Équipement)
            [
                'BASE01',
                'SITE001',
                'EMP0001',
                'CLI001',
                'EQUIP001',
                'EQ-001',
                'Climatiseur Split 18000 BTU',
                'DAIKIN',
                'Split',
                'interne',
                '18000 BTU',
                'R410A',
                '2022',
                'En service',
                'Révision annuelle effectuée',
            ],
            // Exemple 2 : Structure 2 (Client → Site → Emplacement → Équipement sans base)
            [
                '',
                'SITE002',
                'EMP0002',
                'CLI001',
                'EQUIP002',
                'EQ-002',
                'Chambre froide 50m³',
                'CARRIER',
                'Gainable',
                'externe',
                '15 kW',
                'R404A',
                '2021',
                'En service',
                '',
            ],
            // Exemple 3 : Structure 3 (Client → Équipement direct sans site ni base)
            [
                '',
                '',
                '',
                'CLI002',
                'EQUIP003',
                'EQ-003',
                'Groupe Électrogène 100kVA',
                'CATERPILLAR',
                'GF',
                'externe',
                '100 kVA',
                'Diesel',
                '2020',
                'En service',
                'Types valides: Split, Gainable, Windows, Armoire, GF',
            ],
        ];
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
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'd97706']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }
}
