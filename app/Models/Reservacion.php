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
        'moneda',
        'fecha_confirmacion',
        'confirmada_por',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'numero_personas' => 'integer',
        'monto_total' => 'decimal:2',
        'cobrado' => 'decimal:2',
        'fecha_confirmacion' => 'datetime',
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

    // Relación con el módulo de Ventas y Pagos
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'reservacion_id');
    }

    public function getFolioAttribute(): string
    {
        return 'MC-'.$this->fecha_solicitud->format('Y').'-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function scopeConCobros($consulta)
    {
        return $consulta->withSum(['pagos as cobrado' => fn ($pagos) => $pagos
            ->where('estado', 'confirmado')->whereColumn('pagos.moneda', 'reservaciones.moneda')], 'monto');
    }
}
