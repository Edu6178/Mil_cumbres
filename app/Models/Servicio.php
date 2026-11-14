<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Servicio extends Model
{
    protected $table = 'servicios';

    // Son exclusivamente los campos disponibles en la migración recibida.
    protected $fillable = [
        'categoria_id', 'nombre_servicio', 'descripcion', 'precio_base',
        'itinerario', 'duracion_estimada', 'estado_servicio',
    ];

    protected function casts(): array
    {
        return ['precio_base' => 'decimal:2', 'duracion_estimada' => 'integer'];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
