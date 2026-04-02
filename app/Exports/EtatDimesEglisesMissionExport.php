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

class EtatDimesEglisesMissionExport implements FromArray, WithStyles, WithColumnWidths, WithDrawings
{
    /**
     * @param  array<int, array<int, string|int|float>>  $rows
     */
    public function __construct(
        private readonly string $missionNom,
        private readonly int $annee,
        private readonly array $rows
    ) {}

    /**
     * @return array<int, array<int, string|int|float>>
     */
    public function array(): array
    {
        return array_merge([
            ['', 'STATION MISSIONNAIRE DES EGLISES ADVENTISTES', '', '', '', '', '', '', '', ''],
            ['', 'DU SEPTIEME JOUR AU CONGO', '', '', '', '', '', '', '', ''],
            ['', 'ETAT DES DIMES DES EGLISES', '', '', '', '', '', '', '', ''],
            ['', strtoupper($this->missionNom).' — ANNEE '.$this->annee, '', '', '', '', '', '', '', ''],
            [],
            ['#', 'EGLISES', 'OBJECTIF DIMES '.$this->annee, 'DIMES COLLECTEES '.$this->annee, 'OFFRANDES COLLECTEES '.$this->annee, 'DIMES '.($this->annee - 1), 'ECART '.$this->annee.' VS '.($this->annee - 1), 'DIMES MOYENNE MENSUELLE', 'NBRE DES MEMBRES', 'POURCENTAGE APPORT %'],
        ], $this->rows);
    }

    public function styles(Worksheet $sheet): array
    {
        $maxRow = 6 + count($this->rows);
        $tableRange = 'A6:J'.$maxRow;
        $headerRange = 'A6:J6';

        $sheet->mergeCells('B1:J1');
        $sheet->mergeCells('B2:J2');
        $sheet->mergeCells('B3:J3');
        $sheet->mergeCells('B4:J4');

        $sheet->getStyle('B1:J4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B1:J4')->getFont()->setBold(true);
        $sheet->getStyle('B1:I2')->getFont()->setSize(12);
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

        $sheet->getStyle('A7:J'.$maxRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A7:A'.$maxRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B7:B'.$maxRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        for ($col = 3; $col <= 10; $col++) {
            $letter = Coordinate::stringFromColumnIndex($col);
            $sheet->getStyle($letter.'7:'.$letter.$maxRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        $sheet->getStyle('C6:C'.$maxRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAD3');
        $sheet->getStyle('D6:E'.$maxRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAF7');
        $sheet->getStyle('F6:H'.$maxRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FCE5CD');
        $sheet->getStyle('I6:J'.$maxRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');

        $sheet->getStyle('A'.$maxRow.':J'.$maxRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$maxRow.':J'.$maxRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6E6');

        return [];
    }

    /**
     * @return array<int, Drawing>
     */
    public function drawings(): array
    {
        $path = public_path('images/logo_adventiste.jpg');
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
            'A' => 5,
            'B' => 28,
            'C' => 19,
            'D' => 19,
            'E' => 19,
            'F' => 18,
            'G' => 18,
            'H' => 21,
            'I' => 16,
            'J' => 18,
        ];
    }
}
