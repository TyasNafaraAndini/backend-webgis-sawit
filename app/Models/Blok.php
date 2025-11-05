<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Pekerja;

class Blok extends Model
{
    protected $table = 'blok';
    protected $primaryKey = 'id_blok';
    public $timestamps = false;

    protected $fillable = [
        'nama_blok',
        'lokasi',
        'waktu_tanam',
        'waktu_panen',
        'kode_unik'
    ];

    // Eager load relasi pekerja otomatis
    protected $with = ['pekerja'];

    // Append untuk JSON
    protected $appends = ['umur', 'kategori_umur'];

    // Casting tanggal otomatis
    protected $casts = [
        'waktu_tanam' => 'date',
        'waktu_panen' => 'date',
    ];

    public function pekerja()
    {
        return $this->belongsToMany(
            Pekerja::class,    // Model tujuan
            'blok_pekerja',    // Tabel pivot
            'blok_id',         // FK pivot ke blok
            'pekerja_id'       // FK pivot ke pekerja
        )
        ->withPivot(['created_at', 'updated_at'])
        ->withTimestamps(); // otomatis isi timestamp pivot
    }

    // Umur pohon
    public function getUmurAttribute()
    {
        return $this->waktu_tanam 
            ? round(Carbon::parse($this->waktu_tanam)->floatDiffInYears(now()), 1) 
            : null;
    }

    // Kategori umur pohon
    public function getKategoriUmurAttribute()
    {
        $umur = $this->umur;
        if (is_null($umur)) return 'Tidak diketahui';
        if ($umur < 4) return 'Muda';
        if ($umur <= 18) return 'Produktif';
        return 'Tua';
    }
}