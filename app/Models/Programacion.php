<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programacion extends Model
{
    use HasFactory;

    public const ESTADOS_CON_CUPO = ['Pre-reserva', 'Confirmada'];

    protected $table = 'programaciones';

    protected $fillable = [
        'servicio_reg_id',
        'cupo_maximo',
        'fecha_inicio',
        'fecha_fin',
        'estado', // Valores: 'programada', 'bloqueada', 'cancelada', 'finalizada'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function servicioRegion()
    {
        return $this->belongsTo(ServicioRegion::class, 'servicio_reg_id');
    }

    public function reservaciones()
    {
        return $this->hasMany(Reservacion::class, 'programacion_id');
    }

    // Comparte la misma regla de ocupación en la portada, el calendario y las solicitudes.
    public function getCuposOcupadosAttribute(): int
    {
        if ($this->relationLoaded('reservaciones')) {
            return (int) $this->reservaciones
                ->filter(fn ($reservacion) => in_array(
                    $reservacion->estado?->nombre_estado, self::ESTADOS_CON_CUPO, true
                ))->sum('numero_personas');
        }

        return (int) $this->reservaciones()
            ->whereHas('estado', function ($query) {
                $query->whereIn('nombre_estado', self::ESTADOS_CON_CUPO);
            })
            ->sum('numero_personas');
    }

    // Devuelve los lugares reales disponibles
    public function getCuposDisponiblesAttribute(): int
    {
        return max(0, $this->cupo_maximo - $this->cupos_ocupados);
    }

    // Verifica si la programación está habilitada tanto por su estado como por la disponibilidad de su región
    public function getEstaDisponibleAttribute(): bool
    {
        $regionActiva = $this->servicioRegion?->region?->disponibilidad ?? true;

        return $this->estado === 'programada'
            && $regionActiva
            && $this->fecha_inicio->isFuture()
            && $this->cupos_disponibles > 0;
    }
}
