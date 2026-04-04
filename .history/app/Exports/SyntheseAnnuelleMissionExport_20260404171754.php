<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SyntheseAnnuelleMissionExport implements FromArray, WithStyles, WithColumnWidths, WithDrawings
{
    /**
     * @param  array<int, string>  $nomsMois
     * @param  array<int, array<int, string|float|int>>  $rows
     */
    public function __construct(
        private readonly string $missionNom,
        private readonly int $annee,
        private readonly array $nomsMois,
        private readonly array $rows
    ) {}

    /**
     * @return array<int, array<int, string|float|int>>
     */
    public function array(): array
    {
        $headings = ['CONGO'];
        foreach ($this->nomsMois as $nomMois) {
            $headings[] = $nomMois;
        }
        $headings[] = 'TOTAL';

        return array_merge([
            ['', 'STATION MISSIONNAIRE DES EGLISES ADVENTISTES', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', 'DU SEPTIEME JOUR AU CONGO', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', 'SYNTHESE ANNUELLE TRESORERIE MISSION', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', strtoupper($this->missionNom).' — ANNEE '.$this->annee, '', '', '', '', '', '', '', '', '', '', '', ''],
            [],
            $headings,
        ], $this->rows);
    }

    public function styles(Worksheet $sheet): array
    {
        $maxCol = 14; // A..N
        $maxColLetter = Coordinate::stringFromColumnIndex($maxCol);
        $maxRow = 6 + count($this->rows);
        $tableRange = 'A6:'.$maxColLetter.$maxRow;

        $sheet->mergeCells('B1:'.$maxColLetter.'1');
        $sheet->mergeCells('B2:'.$maxColLetter.'2');
        $sheet->mergeCells('B3:'.$maxColLetter.'3');
        $sheet->mergeCells('B4:'.$maxColLetter.'4');

        $sheet->getStyle('B1:'.$maxColLetter.'4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B1:'.$maxColLetter.'4')->getFont()->setBold(true);
        $sheet->getStyle('B1:'.$maxColLetter.'2')->getFont()->setSize(12);
        $sheet->getStyle('B3')->getFont()->setSize(14);
        $sheet->getStyle('B4')->getFont()->setSize(11);

        $sheet->getStyle('A6:'.$maxColLetter.'6')->applyFromArray([
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

        $sheet->getStyle('A7:A'.$maxRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        for ($col = 2; $col <= $maxCol; $col++) {
            $letter = Coordinate::stringFromColumnIndex($col);
            $sheet->getStyle($letter.'7:'.$letter.$maxRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        for ($row = 7; $row <= $maxRow; $row++) {
            $label = strtoupper((string) $sheet->getCell('A'.$row)->getValue());
            if ($label === '') {
                continue;
            }
            if (str_starts_with($label, 'SECTION:')) {
                $sheet->getStyle('A'.$row.':'.$maxColLetter.$row)->getFont()->setBold(true);
                $sheet->getStyle('A'.$row.':'.$maxColLetter.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6E6');
                continue;
            }
            if (str_contains($label, 'TOTAL') || str_contains($label, 'DIFFERENCE')) {
                $sheet->getStyle('A'.$row.':'.$maxColLetter.$row)->getFont()->setBold(true);
            }
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
            'A' => 34,
            'B' => 12,
            'C' => 12,
            'D' => 12,
            'E' => 12,
            'F' => 12,
            'G' => 12,
            'H' => 12,
            'I' => 12,
            'J' => 12,
            'K' => 12,
            'L' => 12,
            'M' => 12,
            'N' => 14,
        ];
    }
}
