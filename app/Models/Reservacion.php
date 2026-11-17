<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservacion extends Model
{
    use HasFactory;

    protected $table = 'reservaciones';

    protected $fillable = [
        'cliente_id',
        'programacion_id',
        'estado_reservacion_id',
        'fecha_solicitud',
        'numero_personas',
        'tipo_grupo',
        'proposito_visita',
        'personalizacion',
        'monto_total',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'numero_personas' => 'integer',
        'monto_total' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function programacion()
    {
        return $this->belongsTo(Programacion::class, 'programacion_id');
    }

    public function estado()
    {
        return $this->belongsTo(
            EstadoReservacion::class,
            'estado_reservacion_id'
        );
    }
}
