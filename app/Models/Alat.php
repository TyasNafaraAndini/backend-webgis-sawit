<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alat extends Model
{
    protected $table = 'alat';
    protected $primaryKey = 'id_alat';
    public $timestamps = false;

    protected $fillable = [
        'nama_alat', 'id_blok', 'penggunaan', 'tanggal'
    ];

    // Relasi ke blok (many to one)
    public function blok()
    {
        return $this->belongsTo(Blok::class, 'id_blok', 'id_blok');
    }

    // Relasi ke pekerja (many to many via tabel pivot alat_pekerja)
    public function pekerja()
    {
        return $this->belongsToMany(Pekerja::class, 'alat_pekerja', 'alat_id', 'pekerja_id');
    }
}
