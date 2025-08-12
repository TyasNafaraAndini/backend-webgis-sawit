<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jalan extends Model
{
    protected $table = 'jalan';
    protected $primaryKey = 'id_jalan';
    public $timestamps = false;

    protected $fillable = [
        'lokasi', 'kondisi', 'lebar', 'id_peta'
    ];

    public function uploadPeta()
    {
        return $this->belongsTo(UploadPeta::class, 'id_peta', 'id_peta');
    }
}
