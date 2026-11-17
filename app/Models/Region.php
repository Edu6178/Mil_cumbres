<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $table = 'regiones';

    protected $fillable = [
        'nombre_region',
        'descripcion',
        'disponibilidad',
    ];

    protected $casts = [
        'disponibilidad' => 'boolean',
    ];

    public function servicioRegiones()
    {
        return $this->hasMany(ServicioRegion::class, 'region_id');
    }
}