<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class Karyawan extends Authenticatable
{
    use HasFactory;

    protected $table = 'karyawans';

    protected $fillable = [
        'kode_karyawan',
        'nama',
        'email',
        'telepon',
        'password',
        'role',
        'status',
        'api_token',
        'foto'
    ];

    protected $appends = ['foto_url'];

    public function getFotoUrlAttribute()
    {
        return $this->foto
            ? asset('storage/' . $this->foto)
            : null;
    }

    protected $hidden = ['password', 'api_token'];

    public function createToken(): string
    {
        $this->api_token = Str::random(80);
        $this->save();

        return $this->api_token;
    }

    public function revokeToken(): void
    {
        $this->api_token = null;
        $this->save();
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }

    public function jobdesks()
    {
        return $this->belongsToMany(Jobdesk::class, 'jobdesk_karyawan');
    }

    public function penggajians()
    {
        return $this->hasMany(Penggajian::class);
    }
}
