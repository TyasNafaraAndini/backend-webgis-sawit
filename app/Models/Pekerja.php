<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pekerja extends Model
{
    protected $table = 'pekerja';
    protected $primaryKey = 'id_pekerja';
    public $timestamps = false;

    protected $fillable = [
        'nama', 'umur', 'jenis_kelamin', 'lama_kerja', 'kontak', 'pekerjaan'
    ];

    // Relasi many-to-many ke blok
    public function blok()
    {
        return $this->belongsToMany(Blok::class, 'blok_pekerja', 'pekerja_id', 'blok_id');
    }

    // Relasi many-to-many ke alat
    public function alat()
    {
        return $this->belongsToMany(Alat::class, 'alat_pekerja', 'pekerja_id', 'alat_id');
    }
}
