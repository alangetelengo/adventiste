<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithStyles;

class RapportDimesOffrandesMissionExport implements FromArray, WithStyles, WithColumnWidths, WithDrawings
{
    /**
     * @param  array<int, array<int, string|int|float>>  $rows
     */
    public function __construct(
        private readonly string $missionNom,
        private readonly int $annee,
        private readonly int $mois,
        private readonly array $rows
    ) {}

    /**
     * @return array<int, array<int, string|int|float>>
     */
    public function array(): array
    {
        $nomsMois = [1 => 'janvier', 2 => 'fevrier', 3 => 'mars', 4 => 'avril', 5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'aout', 9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'decembre'];

        return array_merge([
            ['', 'STATION MISSIONNAIRE DES EGLISES ADVENTISTES', '', '', ''],
            ['', 'DU SEPTIEME JOUR AU CONGO', '', '', ''],
            ['', 'RAPPORT DIMES ET OFFRANDES', '', '', ''],
            ['', strtoupper($this->missionNom).' — Mois : '.($nomsMois[$this->mois] ?? (string) $this->mois).'-'.substr((string) $this->annee, -2), '', '', ''],
            [],
            ['DESIGNATION', 'POURCENTAGE', 'TOTAL DU MOIS', 'TOTAL PRECEDENT', 'TOTAL CUMULE'],
        ], $this->rows);
    }

    public function styles(Worksheet $sheet): array
    {
        $maxRow = 6 + count($this->rows);
        $tableRange = 'A6:E'.$maxRow;
        $headerRange = 'A6:E6';

        $sheet->mergeCells('B1:E1');
        $sheet->mergeCells('B2:E2');
        $sheet->mergeCells('B3:E3');
        $sheet->mergeCells('B4:E4');

        $sheet->getStyle('B1:E4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B1:E4')->getFont()->setBold(true);
        $sheet->getStyle('B1:E2')->getFont()->setSize(12);
        $sheet->getStyle('B3')->getFont()->setSize(14);
        $sheet->getStyle('B4')->getFont()->setSize(11);

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9D9D9'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle($tableRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '808080'],
                ],
            ],
        ]);

        for ($row = 7; $row <= $maxRow; $row++) {
            $designation = strtoupper((string) $sheet->getCell('A'.$row)->getValue());
            if (str_contains($designation, 'TOTAL')) {
                $sheet->getStyle('A'.$row.':E'.$row)->getFont()->setBold(true);
                $sheet->getStyle('A'.$row.':E'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6E6');
            }
        }

        $sheet->getStyle('C7:C11')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF200');
        $sheet->getStyle('C'.($maxRow - 6).':C'.$maxRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF200');

        for ($col = 2; $col <= 5; $col++) {
            $letter = Coordinate::stringFromColumnIndex($col);
            $sheet->getStyle($letter.'7:'.$letter.$maxRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        return [];
    }

    /**
     * @return array<int, Drawing>
     */
    public function drawings(): array
    {
        $path = public_path('images/logo_sda.png');
        if (! file_exists($path)) {
            return [];
        }

        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo Église Adventiste');
        $drawing->setPath($path);
        $drawing->setHeight(62);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(8);
        $drawing->setOffsetY(4);

        return [$drawing];
    }

    /**
     * @return array<string, float|int>
     */
    public function columnWidths(): array
    {
        return [
            'A' => 48,
            'B' => 16,
            'C' => 18,
            'D' => 18,
            'E' => 18,
        ];
    }
}
