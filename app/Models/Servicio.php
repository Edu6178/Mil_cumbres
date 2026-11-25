<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Servicio extends Model
{
    protected $table = 'servicios';

    // El precio puede quedar pendiente; la unidad distingue personas, grupos y servicios completos.
    protected $fillable = [
        'categoria_id', 'nombre_servicio', 'descripcion', 'precio_base',
        'itinerario', 'duracion_estimada', 'estado_servicio', 'tipo_precio', 'unidad_cobro', 'moneda',
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
