<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Blok extends Model
{
    protected $table = 'blok';
    protected $primaryKey = 'id_blok';
    public $timestamps = false;

    protected $fillable = [
        'nama_blok', 'lokasi', 'waktu_tanam', 'waktu_panen', 'id_peta'
    ];

    // Agar otomatis muncul saat dikonversi ke JSON
    protected $appends = ['umur', 'kategori_umur'];

    public function uploadPeta()
    {
        return $this->belongsTo(UploadPeta::class, 'id_peta', 'id_peta');
    }

    public function pekerja()
    {
        return $this->belongsToMany(Pekerja::class, 'blok_pekerja', 'blok_id', 'pekerja_id');
    }

    // Accessor untuk umur pohon
    public function getUmurAttribute()
    {
        if (!$this->waktu_tanam) return null;

        return round(Carbon::parse($this->waktu_tanam)->floatDiffInYears(now()), 1);
    }

    // Accessor untuk kategori umur pohon
    public function getKategoriUmurAttribute()
    {
        $umur = $this->umur;

        if (is_null($umur)) return 'Tidak diketahui';
        if ($umur < 4) return 'Muda';
        if ($umur <= 18) return 'Produktif';
        return 'Tua';
    }
}
