<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianIndikator extends Model
{
    protected $table = 'penilaian_indikator';
    protected $fillable = ['id_indikator', 'tingkat', 'nilai', 'tahun','status'];
    protected $primaryKey = 'id_penilaian';
    public $timestamps = false;

    public function indikator()
    {
        return $this->belongsTo(Indikator::class, 'id_indikator', 'id_indikator');
    }
}
