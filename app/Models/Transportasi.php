<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transportasi extends Model
{
    protected $table = 'transportasi';
    protected $primaryKey = 'id_transportasi';
    public $timestamps = false;

    protected $fillable = [
        'jenis_transportasi', 'kapasitas'
    ];
}

