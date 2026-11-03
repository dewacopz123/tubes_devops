<?php

namespace App\Exports;

use App\Models\Jobdesk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JobdeskSheetExport implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return Jobdesk::with('karyawans')->get()->map(function ($j) {
            return [
                $j->karyawans->pluck('nama')->implode(', ') ?: '-',
                $j->nama_jobdesk ?? '-',
                $j->tugas_utama ?? '-'
            ];
        });
    }

    public function headings(): array
    {
        return ['Nama Karyawan', 'Jobdesk', 'Tugas Utama'];
    }

    public function title(): string
    {
        return 'Jobdesk';
    }
}
