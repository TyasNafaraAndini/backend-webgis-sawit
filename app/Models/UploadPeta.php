<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Blok;

class UploadPeta extends Model
{
    protected $table = 'upload_peta';
    protected $primaryKey = 'id_peta';
    public $timestamps = false;

    // Kalau id_peta auto increment integer
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama_peta',
        'uploader',
        'format_file',
        'link_peta',
        'uploaded_at',   
    ];

    // Relasi ke tabel blok
    public function blok()
    {
        return $this->hasMany(Blok::class, 'id_peta', 'id_peta');
    }
}
