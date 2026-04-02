<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatatanKriteria extends Model
{
    protected $table = 'catatan_kriteria';
    protected $primaryKey = 'id_catatan';

     protected $fillable = [
        'id_indikator',
        'tahun',
        'nama_catatankriteria',
        'pencapaian',
        'bukti',
        'id_user'
    ];

    protected $casts = [
        'bukti' => 'array',
    ];
}