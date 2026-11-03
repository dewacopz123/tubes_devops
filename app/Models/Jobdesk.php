<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jobdesk extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_jobdesk',
        'nama_jobdesk',
        'tugas_utama',
    ];

    // generate kode otomatis sebelum insert
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $latest = Jobdesk::latest('id')->first();
            $number = $latest ? $latest->id + 1 : 1;
            $model->kode_jobdesk = 'JD' . str_pad($number, 3, '0', STR_PAD_LEFT);
        });
    }

    public function karyawans()
    {
        return $this->belongsToMany(Karyawan::class, 'jobdesk_karyawan');
    }
}
