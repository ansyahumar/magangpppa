<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BobotDomain extends Model
{
    protected $table = 'bobot_domain';
    protected $primaryKey = 'id_domain';
    public $timestamps = false;
    protected $fillable = ['id_domain', 'bobot','tahun'];

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'id_domain', 'id_domain');
    }
}
