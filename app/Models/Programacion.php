<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programacion extends Model
{
    use HasFactory;

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

    // Calcula los lugares ya ocupados excluyendo reservaciones canceladas
    public function getCuposOcupadosAttribute(): int
    {
        return (int) $this->reservaciones()
            ->whereHas('estadoReservacion', function ($query) {
                $query->whereNotIn('nombre_estado', ['Cancelada', 'Rechazada']);
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