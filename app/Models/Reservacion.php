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
        'edad_responsable',
        'tipo_grupo',
        'proposito_visita',
        'personalizacion',
        'monto_total',
        'moneda',
        'fecha_confirmacion',
        'confirmada_por',
        'tarifa_publicada', 'importe_publicado', 'unidad_cobro', 'tipo_precio', 'moneda_publicada',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'numero_personas' => 'integer',
        'edad_responsable' => 'integer',
        'monto_total' => 'decimal:2',
        'tarifa_publicada' => 'decimal:2',
        'importe_publicado' => 'decimal:2',
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

    // Recupera a los acompañantes del grupo usando la relación que ya existe en la base.
    public function acompanantes()
    {
        return $this->belongsToMany(Persona::class, 'asigna_reservacion_invitados', 'reservacion_id', 'persona_id')
            ->withPivot('edad')
            ->orderBy('asigna_reservacion_invitados.id');
    }

    public function getFolioAttribute(): string
    {
        return 'MC-'.$this->fecha_solicitud->format('Y').'-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getTarifaCatalogoAttribute(): array
    {
        if ($this->moneda_publicada !== null) {
            return ['precio' => $this->tarifa_publicada, 'importe' => $this->importe_publicado,
                'unidad' => $this->unidad_cobro, 'tipo' => $this->tipo_precio, 'moneda' => $this->moneda_publicada, 'guardada' => true];
        }

        // Una solicitud anterior puede mostrar una sugerencia actual, sin inventarle un precio histórico.
        return [...\App\Services\Finanzas\TarifaReserva::calcular($this->programacion?->servicioRegion?->servicio, $this->numero_personas), 'guardada' => false];
    }

    public function scopeConCobros($consulta)
    {
        return $consulta->withSum(['pagos as cobrado' => fn ($pagos) => $pagos
            ->where('estado', 'confirmado')->whereColumn('pagos.moneda', 'reservaciones.moneda')], 'monto');
    }
}
