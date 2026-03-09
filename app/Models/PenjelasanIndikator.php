<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjelasanIndikator extends Model
{
    use HasFactory;

    protected $table = 'penjelasan_indikator';

    protected $primaryKey = 'id_penjelasan_penulisan'; 
    public $timestamps = true;
    protected $fillable = [
        'id_penjelasan_penulisan',
        'id_indikator',
        'tatacara_penilaian',
        'penjelasan_kriteria',
        'deskripsi',
        'tahun', 
    ];

    public function indikator()
    {
        return $this->belongsTo(Indikator::class, 'id_indikator', 'id_indikator');
    }
}