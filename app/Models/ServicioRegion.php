<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicioRegion extends Model
{
    use HasFactory;

    protected $table = 'servicio_regiones';

    protected $fillable = [
        'servicio_id',
        'region_id',
        'cupo_maximo',
    ];

    // Servicio al que pertenece esta relación.
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    // Región donde se ofrece el servicio.
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    // Programaciones asociadas al servicio y la región.
    public function programaciones()
    {
        return $this->hasMany(Programacion::class, 'servicio_reg_id');
    }
}
