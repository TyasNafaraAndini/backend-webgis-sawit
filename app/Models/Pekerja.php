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
}
