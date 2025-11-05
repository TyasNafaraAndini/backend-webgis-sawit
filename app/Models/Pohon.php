<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pohon extends Model
{
    use HasFactory;

    protected $table = 'pohon';
    protected $primaryKey = 'id_pohon';
    public $timestamps = false;

    protected $fillable = [
        'lokasi_koordinat',
        'varietas',
        'zona',
    ];
}