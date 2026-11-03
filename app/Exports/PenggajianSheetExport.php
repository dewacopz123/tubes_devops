<?php

namespace App\Exports;

use App\Models\Penggajian;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PenggajianSheetExport implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return Penggajian::with('karyawan')->get()->map(function ($p) {
            return [
                $p->kode_penggajian,
                optional($p->karyawan)->nama ?? '-',
                $p->tanggal,
                $p->gaji_pokok
            ];
        });
    }

    public function headings(): array
    {
        return ['Kode Penggajian', 'Nama Karyawan', 'Tanggal', 'Gaji Pokok'];
    }

    public function title(): string
    {
        return 'Penggajian';
    }
}
