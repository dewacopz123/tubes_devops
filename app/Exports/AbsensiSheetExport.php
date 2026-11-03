<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AbsensiSheetExport implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return Absensi::with('karyawan')->get()->map(function ($a) {
            return [
                optional($a->karyawan)->nama ?? '-',
                $a->tanggal,
                $a->jam_masuk,
                $a->jam_keluar,
                $a->status
            ];
        });
    }

    public function headings(): array
    {
        return ['Nama Karyawan', 'Tanggal', 'Jam Masuk', 'Jam Keluar', 'Status'];
    }

    public function title(): string
    {
        return 'Absensi';
    }
}
