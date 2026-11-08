<?php

namespace App\Http\Controllers;

use App\Exports\LaporanPerusahaanExport;
use App\Exports\PenggajianSheetExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    /**
     * Ekspor rekapitulasi data terpadu perusahaan ke file spreadsheet Excel (.xlsx) multi-sheet.
     * Mencakup data karyawan, absensi, jobdesk, dan penggajian.
     *
     * @return BinaryFileResponse
     */
    public function laporan()
    {
        return Excel::download(
            new LaporanPerusahaanExport,
            'laporan-perusahaan.xlsx'
        );
    }

    /**
     * Ekspor rekapitulasi data penggajian karyawan ke file spreadsheet Excel.
     *
     * @return BinaryFileResponse
     */
    public function penggajian()
    {
        return Excel::download(
            new PenggajianSheetExport,
            'penggajian.xlsx'
        );
    }
}
