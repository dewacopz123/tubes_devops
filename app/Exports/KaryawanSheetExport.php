<?php

namespace App\Exports;

use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KaryawanSheetExport implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return Karyawan::all()->map(function ($k) {
            return [
                $k->nama,
                $k->email,
                $k->status
            ];
        });
    }

    public function headings(): array
    {
        return ['Nama', 'Email', 'Status'];
    }

    public function title(): string
    {
        return 'Karyawan';
    }
}
