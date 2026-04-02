<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aspek extends Model
{
        protected $fillable = [
        'id_domain',
        'nama_aspek',
        'target',
        'tahun',
        'urutan'
    ];
    
    protected $table = 'aspek';
    protected $primaryKey = 'id_aspek';
    public $timestamps = false;

    public function indikator()
    {
        return $this->hasMany(Indikator::class, 'id_aspek', 'id_aspek');
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'id_domain', 'id_domain');
    }
        public function bobot()
    {
        return $this->hasOne(BobotAspek::class, 'id_aspek');
    }
    public function bobotAspek()
{
    return $this->hasMany(BobotAspek::class, 'id_aspek', 'id_aspek');
}

}
