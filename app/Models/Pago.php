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
        'moneda',
        'estado',
        'fecha_confirmacion',
        'confirmado_por',
        'clave_registro',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'datetime',
        'fecha_confirmacion' => 'datetime',
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

    public function confirmador()
    {
        return $this->belongsTo(User::class, 'confirmado_por');
    }
}
