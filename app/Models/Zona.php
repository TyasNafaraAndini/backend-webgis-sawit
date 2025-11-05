<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    protected $table = 'zona';
    protected $primaryKey = 'id_zona';
    public $timestamps = false; // created_at dihandle DB

    protected $fillable = [
        'jumlah',
        'lokasi_zona',
        'nama_zona',
        'kode_unik'
    ];
}