<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kriteria extends Model
{
    protected $table = 'kriteria';
    protected $primaryKey = 'id_kriteria';
    public $timestamps = false;

    protected $fillable = [
        'id_indikator',
        'nama_kriteria',
        'bobot_nilai',
        'tahun'
    ];

    public function indikator()
    {
        return $this->belongsTo(Indikator::class, 'id_indikator', 'id_indikator');
    }
}