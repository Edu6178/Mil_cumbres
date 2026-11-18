<?php

namespace App\Services\Finanzas;

use App\Models\EstadoReservacion;
use App\Models\Pago;
use App\Models\Programacion;
use App\Models\Reservacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GestionFinanciera
{
    public function confirmarVenta(Reservacion $reservacion, array $datos, int $usuario): void
    {
        DB::transaction(function () use ($reservacion, $datos, $usuario) {
            // El calendario y las ventas toman primero el mismo bloqueo de programación.
            $programacion = Programacion::whereKey($reservacion->programacion_id)->lockForUpdate()->firstOrFail();
            $venta = Reservacion::with('estado')->whereKey($reservacion->id)->lockForUpdate()->firstOrFail();
            if ($venta->estado?->nombre_estado === 'Confirmada') {
                if ($venta->moneda !== $datos['moneda'] || Dinero::centavos($venta->monto_total) !== Dinero::centavos($datos['monto_total'])) {
                    $this->rechazar('monto_total', 'El importe pactado de una venta confirmada se conserva. No se puede reemplazar al reenviar el formulario.');
                }

                return;
            }
            if ($venta->estado?->nombre_estado !== 'Pre-reserva') {
                $this->rechazar('venta', 'Solo una pre-reservación pendiente puede convertirse en venta.');
            }
            if (!in_array($programacion->estado, ['programada', 'activo'], true)
                || !$programacion->fecha_inicio->isFuture()
                || !$programacion->servicioRegion?->region?->disponibilidad
                || $programacion->servicioRegion?->servicio?->estado_servicio !== 'activo'
                || $programacion->cupos_ocupados > $programacion->cupo_maximo) {
                $this->rechazar('venta', 'La programación está bloqueada, vencida o no dispone de cupo válido. Revisa el calendario antes de confirmar.');
            }
            $estado = EstadoReservacion::firstOrCreate(['nombre_estado' => 'Confirmada']);
            $venta->update([
                'monto_total' => Dinero::decimal(Dinero::centavos($datos['monto_total'])),
                'moneda' => $datos['moneda'], 'estado_reservacion_id' => $estado->id,
                'fecha_confirmacion' => now(), 'confirmada_por' => $usuario,
            ]);
        }, 3);
    }

    public function registrarPago(array $datos): Pago
    {
        return DB::transaction(function () use ($datos) {
            $venta = Reservacion::with('estado')->whereKey($datos['reservacion_id'])->lockForUpdate()->firstOrFail();
            $previo = Pago::where('clave_registro', $datos['clave_registro'])->first();
            if ($previo) {
                if ($previo->reservacion_id !== $venta->id || $previo->monto !== Dinero::decimal(Dinero::centavos($datos['monto']))) {
                    $this->rechazar('clave_registro', 'Este formulario ya fue utilizado. Abre un registro nuevo.');
                }

                return $previo;
            }
            $this->validarVenta($venta, $datos['moneda']);
            if (Dinero::centavos($datos['monto']) > $this->saldo($venta)) {
                $this->rechazar('monto', 'El importe supera el saldo pendiente de la venta.');
            }

            // Registrar un enlace o un comprobante nunca confirma por sí solo un cobro.
            return Pago::create([...$datos, 'estado' => 'pendiente']);
        }, 3);
    }

    public function confirmarPago(Pago $pago, array $datos, int $usuario): void
    {
        DB::transaction(function () use ($pago, $datos, $usuario) {
            $venta = Reservacion::with('estado')->whereKey($pago->reservacion_id)->lockForUpdate()->firstOrFail();
            $registro = Pago::whereKey($pago->id)->lockForUpdate()->firstOrFail();
            if ($registro->estado === 'confirmado') {
                return;
            }
            if ($registro->estado !== 'pendiente') {
                $this->rechazar('pago', 'Un registro anulado no puede confirmarse.');
            }
            $this->validarVenta($venta, $registro->moneda);
            if (Dinero::centavos($registro->monto) > $this->saldo($venta)) {
                $this->rechazar('monto', 'Otro abono ya cubrió parte de este saldo. Revisa los pagos antes de confirmar.');
            }
            $registro->update([
                'estado' => 'confirmado', 'fecha_pago' => $datos['fecha_pago'],
                'referencia_comprobante' => $datos['referencia_comprobante'] ?? $registro->referencia_comprobante,
                'fecha_confirmacion' => now(), 'confirmado_por' => $usuario,
            ]);
        }, 3);
    }

    public function anularPago(Pago $pago): void
    {
        DB::transaction(function () use ($pago) {
            Reservacion::whereKey($pago->reservacion_id)->lockForUpdate()->firstOrFail();
            $registro = Pago::whereKey($pago->id)->lockForUpdate()->firstOrFail();
            if ($registro->estado === 'confirmado') {
                $this->rechazar('pago', 'El cobro confirmado se conserva en el historial. Una devolución requiere un flujo autorizado independiente.');
            }
            $registro->update(['estado' => 'anulado']);
        }, 3);
    }

    public function saldo(Reservacion $venta): int
    {
        $cobrado = $venta->pagos()->where('estado', 'confirmado')->where('moneda', $venta->moneda)
            ->get(['monto'])->sum(fn ($pago) => Dinero::centavos($pago->monto));

        return max(0, Dinero::centavos($venta->monto_total) - $cobrado);
    }

    private function validarVenta(Reservacion $venta, string $moneda): void
    {
        if ($venta->estado?->nombre_estado !== 'Confirmada' || !$venta->monto_total || Dinero::centavos($venta->monto_total) <= 0) {
            $this->rechazar('reservacion_id', 'Confirma primero la venta y su importe pactado.');
        }
        if ($venta->moneda !== $moneda) {
            $this->rechazar('moneda', 'El pago debe usar la misma moneda que la venta. No se realizan conversiones automáticas.');
        }
    }

    private function rechazar(string $campo, string $mensaje): never
    {
        throw ValidationException::withMessages([$campo => $mensaje]);
    }
}
