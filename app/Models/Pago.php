<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'reservacion_id',
        'metodo_pago_id',
        'monto',
        'fecha_pago',
        'referencia_comprobante',
        'proveedor',
        'proveedor_referencia',
        'url_pago',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'datetime',
    ];

    public function reservacion()
    {
        return $this->belongsTo(
            Reservacion::class,
            'reservacion_id'
        );
    }

    public function metodoPago()
    {
        return $this->belongsTo(
            MetodoPago::class,
            'metodo_pago_id'
        );
    }
}
