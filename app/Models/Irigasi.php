<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Irigasi extends Model
{
    protected $table = 'irigasi';
    protected $primaryKey = 'id_irigasi';
    public $timestamps = false;

    protected $fillable = [
        'lokasi', 'kondisi', 'sumber', 'luas', 'id_peta'
    ];

    public function uploadPeta()
    {
        return $this->belongsTo(UploadPeta::class, 'id_peta', 'id_peta');
    }
}

