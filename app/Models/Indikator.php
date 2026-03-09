<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Indikator extends Model
{
    use HasFactory;

    protected $table = 'indikator';
    protected $primaryKey = 'id_indikator';
    public $timestamps = false;

    protected $fillable = [
        'id_aspek',
        'nomor_indikator',
        'nama_indikator',
        'tahun',
        'bobot',
    ];

     public function aspek()
    {
        return $this->belongsTo(Aspek::class, 'id_aspek', 'id_aspek');
    }

    public function penjelasan()
    {
        return $this->hasOne(PenjelasanIndikator::class, 'id_indikator', 'id_indikator');
    }
 public function kriteria()
    {
        return $this->hasMany(Kriteria::class, 'id_indikator', 'id_indikator');
    }

    public function penilaianIndikator()
    {
        return $this->hasMany(PenilaianIndikator::class, 'id_indikator', 'id_indikator');
    }
  public function penilaianKriteria()
    {
        return $this->hasOne(PenilaianKriteria::class, 'id_indikator', 'id_indikator');
    }
    public function penilaian()
{
   return $this->hasMany(PenilaianKriteria::class, 'id_indikator', 'id_indikator');
}
}