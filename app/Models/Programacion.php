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
        'estado',
    ];

    // Relación con el servicio y región
    public function servicioRegion()
    {
        return $this->belongsTo(ServicioRegion::class, 'servicio_reg_id');
    }

    // Relación con las reservaciones registradas
    public function reservaciones()
    {
        return $this->hasMany(Reservacion::class, 'programacion_id');
    }

    // Método para obtener los cupos ocupados actualmente
    public function getCuposOcupadosAttribute()
    {
        return $this->reservaciones()
            ->whereIn('estado_reservacion_id', [1, 2]) // Adaptar según IDs de pre-reserva/confirmada
            ->sum('numero_personas');
    }

    // Método para obtener el cupo restante disponible
    public function getCuposDisponiblesAttribute()
    {
        return max(0, $this->cupo_maximo - $this->cupos_ocupados);
    }
}