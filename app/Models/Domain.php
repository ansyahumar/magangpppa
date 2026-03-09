<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $table = 'domain';
    protected $primaryKey = 'id_domain';
    public $timestamps = false;
protected $fillable = [
        'nama_domain',
        'tahun',
        'urutan'
    ];
    public function aspek()
    {
        return $this->hasMany(Aspek::class, 'id_domain', 'id_domain');
    }
        public function bobot()
    {
        return $this->hasOne(BobotDomain::class, 'id_domain');
    }
    public function bobotDomain()
{
    return $this->hasMany(BobotDomain::class, 'id_domain', 'id_domain');
}

}
