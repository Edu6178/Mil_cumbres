<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoReservacion extends Model
{
    use HasFactory;

    protected $table = 'estado_reservaciones';

    protected $fillable = [
        'nombre_estado',
    ];

    public function reservaciones()
    {
        return $this->hasMany(Reservacion::class, 'estado_reservacion_id');
    }
}
