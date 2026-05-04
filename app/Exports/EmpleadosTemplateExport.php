<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmpleadosTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        // Fila de ejemplo para orientar al usuario
        return [
            [
                'Juan Pérez López',
                'jperez@empresa.com',
                'operativo',          // nombre exacto del rol
                'Ventas',             // nombre del área
                'CC-Norte',           // nombre del centro de costo
                'Gerente de Ventas',
                'PELJ850101ABC',
                'PELJ850101HDFRNN09',
                '12345678901',
                'NOM-0001',
                'BBVA',
                '1234567890',
                '012180001234567890',
                '5512345678',
                '2020-01-15',
                'no',                 // tarjeta corporativa (si/no)
                '',                   // límite crédito (vacío si no aplica)
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'nombre_completo',
            'email',
            'rol',                   // nombre del rol
            'area',                  // nombre del área
            'centro_costo',          // nombre del centro de costo
            'puesto',
            'rfc',
            'curp',
            'nss',
            'numero_nomina',
            'banco_nomina',
            'cuenta_nomina',
            'clabe_nomina',
            'telefono',
            'fecha_ingreso',         // formato YYYY-MM-DD
            'tarjeta_corporativa',   // si / no
            'limite_credito',        // número o vacío
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2563EB']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF3F4F6']
                ],
                'font' => [
                    'italic' => true,
                    'color' => ['argb' => 'FF6B7280']
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, 'B' => 28, 'C' => 14, 'D' => 18,
            'E' => 18, 'F' => 22, 'G' => 16, 'H' => 22,
            'I' => 14, 'J' => 14, 'K' => 14, 'L' => 16,
            'M' => 22, 'N' => 14, 'O' => 14, 'P' => 20,
            'Q' => 14,
        ];
    }
}
