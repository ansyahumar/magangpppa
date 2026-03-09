<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BobotAspek extends Model
{
    protected $table = 'bobot_aspek';
    protected $primaryKey = 'id_aspek';
    public $timestamps = false;
    protected $fillable = ['id_aspek', 'bobot','tahun'];

    public function aspek()
    {
        return $this->belongsTo(Aspek::class, 'id_aspek', 'id_aspek','tahun');
    }
}
