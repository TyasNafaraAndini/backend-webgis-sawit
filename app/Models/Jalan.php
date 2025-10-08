<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jalan extends Model
{
    protected $table = 'jalan';
    protected $primaryKey = 'id_jalan';
    public $timestamps = false; // created_at di-handle DB

    protected $fillable = [
        'lokasi',
        'kondisi',
        'lebar',
        'kode_unik'
    ];
}
