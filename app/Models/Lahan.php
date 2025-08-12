<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lahan extends Model
{
    protected $table = 'lahan';
    protected $primaryKey = 'id_lahan';
    public $timestamps = false;

    protected $fillable = [
        'penggunaan_sebelumnya',
        'tahun_perubahan',
        'tahun_jadi_sawit',
        'luas'
        // Kolom 'batas' tidak dimasukkan di sini karena akan di-handle via DB::raw
    ];
}
