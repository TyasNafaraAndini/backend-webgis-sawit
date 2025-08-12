<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadPeta extends Model
{
    protected $table = 'upload_peta';
    protected $primaryKey = 'id_peta';
    public $timestamps = false;

    protected $fillable = [
        'nama_peta', 'tanggal_upload', 'uploader',
        'format_file', 'link_peta'
    ];

    public function blok()
    {
        return $this->hasMany(Blok::class, 'id_peta', 'id_peta');
    }
}

