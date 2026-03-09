<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilIndeks extends Model
{
    protected $primaryKey = 'id_hasil';

    protected $table = 'hasil_indeks';

    protected $fillable = [
        'tahun',
        'indeks_aspek',
        'indeks_domain',
        'indeks_spbe',
        'predikat'
    ];
}