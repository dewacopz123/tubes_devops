<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanPerusahaanExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new AbsensiSheetExport(),
            new KaryawanSheetExport(),
            new JobdeskSheetExport(),
            new PenggajianSheetExport(),
        ];
    }
}
