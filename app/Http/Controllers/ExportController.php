<?php

namespace App\Http\Controllers;

use App\Exports\LaporanPerusahaanExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PenggajianSheetExport;

class ExportController extends Controller
{
    public function laporan()
    {
        return Excel::download(
            new LaporanPerusahaanExport,
            'laporan-perusahaan.xlsx'
        );
    }
    public function penggajian()
    {
        return Excel::download(new PenggajianSheetExport, 'penggajian.xlsx');
    }
}
