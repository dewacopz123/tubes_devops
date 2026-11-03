<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penggajian extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_penggajian',
        'karyawan_id',
        'tanggal',
        'gaji_pokok'
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}


