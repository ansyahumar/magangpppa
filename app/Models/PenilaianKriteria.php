<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianKriteria extends Model
{
    protected $table = 'penilaian_kriteria';

    protected $fillable = [
        'indikator_id',
        'kriteria_id',
        'nilai',
    ];

    public $timestamps = false;
}
