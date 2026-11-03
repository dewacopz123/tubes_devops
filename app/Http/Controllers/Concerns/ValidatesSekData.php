<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Validation\Rule;

trait ValidatesSekData
{
    protected function karyawanRules(?int $ignoreId = null): array
    {
        return [
            'nama' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
            'email' => [
                'required',
                'email',
                Rule::unique('karyawans', 'email')->ignore($ignoreId),
            ],
            'telepon' => ['nullable', 'string', 'max:13', 'regex:/^[0-9+\-\s]+$/'],
            'role' => ['required', Rule::in(['admin', 'karyawan'])],
            'status' => ['required', Rule::in(['Aktif', 'Nonaktif'])],
        ];
    }

    protected function jobdeskRules(?int $ignoreId = null): array
    {
        return [
            'nama_jobdesk' => [
                'required',
                'string',
                'max:255',
                Rule::unique('jobdesks', 'nama_jobdesk')->ignore($ignoreId),
                'regex:/^[A-Za-z0-9\s]+$/',
            ],
            'tugas_utama' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s,]+$/'],
        ];
    }

    protected function profileRules(?int $ignoreId = null): array
    {
        return [
            'nama' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
            'email' => [
                'required',
                'email',
                Rule::unique('karyawans', 'email')->ignore($ignoreId),
            ],
            'telepon' => ['nullable', 'string', 'max:13', 'regex:/^[0-9+\-\s]+$/'],
        ];
    }

    protected function penggajianRules(): array
    {
        return [
            'karyawan_id' => ['required', 'exists:karyawans,id'],
            'tanggal' => ['required', 'date'],
            'gaji_pokok' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function absensiRules(): array
    {
        return [
            'karyawan_id' => ['required', 'exists:karyawans,id'],
            'tanggal' => ['required', 'date'],
            'jam_masuk' => ['nullable', 'date_format:H:i:s'],
            'jam_keluar' => ['nullable', 'date_format:H:i:s', 'after_or_equal:jam_masuk'],
            'status' => ['required', Rule::in(['Masuk', 'Selesai'])],
        ];
    }

    protected function validationMessages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'email' => 'Format email tidak valid.',
            'unique' => ':attribute sudah digunakan.',
            'exists' => ':attribute tidak ditemukan.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'date_format' => ':attribute harus memakai format :format.',
            'after_or_equal' => ':attribute tidak boleh lebih awal dari :date.',
            'numeric' => ':attribute harus berupa angka.',
            'min' => ':attribute tidak boleh kurang dari :min.',
            'max' => ':attribute tidak boleh lebih dari :max karakter.',
            'in' => ':attribute tidak valid.',
            'regex' => 'Format :attribute tidak valid.',
            'nama.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'telepon.regex' => 'Telepon hanya boleh berisi angka, spasi, tanda plus, atau strip.',
            'nama_jobdesk.regex' => 'Nama jobdesk hanya boleh berisi huruf, angka, dan spasi.',
            'tugas_utama.regex' => 'Tugas utama hanya boleh berisi huruf, angka, dan spasi.',
            'karyawan_id.array' => 'Karyawan harus berupa daftar pilihan.',
            'karyawan_id.min' => 'Pilih minimal satu karyawan.',
            'karyawan_id.*.exists' => 'Salah satu karyawan tidak ditemukan.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama' => 'Nama',
            'email' => 'Email',
            'telepon' => 'Telepon',
            'role' => 'Role',
            'status' => 'Status',
            'nama_jobdesk' => 'Nama jobdesk',
            'tugas_utama' => 'Tugas utama',
            'karyawan_id' => 'Karyawan',
            'jobdesk_id' => 'Jobdesk',
            'tanggal' => 'Tanggal',
            'gaji_pokok' => 'Gaji pokok',
            'jam_masuk' => 'Jam masuk',
            'jam_keluar' => 'Jam keluar',
        ];
    }
}
