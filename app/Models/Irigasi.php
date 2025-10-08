<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Irigasi extends Model
{
    protected $table = 'irigasi';
    protected $primaryKey = 'id_irigasi';
    public $timestamps = false; // created_at di-handle manual

    protected $fillable = [
        'lokasi',
        'kondisi',
        'sumber',
        'luas',
        'kode_unik',
        'created_at'
    ];
}