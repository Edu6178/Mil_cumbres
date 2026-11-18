<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Programacion;
use App\Models\Reservacion;
use App\Services\Finanzas\Dinero;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FinanzaController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'desde' => ['nullable', 'date_format:Y-m-d'], 'hasta' => ['nullable', 'date_format:Y-m-d'],
            'moneda' => ['nullable', Rule::in(Dinero::MONEDAS)],
        ]);
        $desde = Carbon::parse($filtros['desde'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $hasta = Carbon::parse($filtros['hasta'] ?? now()->endOfMonth()->toDateString())->endOfDay();
        if ($hasta->lt($desde) || $desde->diffInDays($hasta) > 366) {
            throw ValidationException::withMessages(['hasta' => 'Elige un periodo ordenado de hasta un año.']);
        }
        $moneda = $filtros['moneda'] ?? 'MXN';
        $confirmadas = Reservacion::with('estado')->conCobros()->where('moneda', $moneda)
            ->whereHas('estado', fn ($q) => $q->where('nombre_estado', 'Confirmada'))->get();
        $ventas = $confirmadas->filter(fn ($v) => $v->fecha_confirmacion?->betweenIncluded($desde, $hasta));
        $pagos = Pago::where('estado', 'confirmado')->where('moneda', $moneda)->whereBetween('fecha_pago', [$desde, $hasta])->get();
        $solicitudes = Reservacion::with(['estado', 'programacion.servicioRegion.servicio'])->whereBetween('fecha_solicitud', [$desde, $hasta])->get();

        // Cada venta se suma una vez; los ingresos se suman por abono y por fecha efectiva de pago.
        $serie = collect(CarbonPeriod::create($desde->toDateString(), $hasta->toDateString()))->map(fn ($dia) => [
            'fecha' => $dia->format('Y-m-d'), 'etiqueta' => $dia->format('d/m'),
            'ventas' => $ventas->filter(fn ($v) => $v->fecha_confirmacion->toDateString() === $dia->toDateString())->sum(fn ($v) => Dinero::centavos($v->monto_total)),
            'cobros' => $pagos->filter(fn ($p) => $p->fecha_pago->toDateString() === $dia->toDateString())->sum(fn ($p) => Dinero::centavos($p->monto)),
        ])->values();
        $datos = [
            'desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString(), 'moneda' => $moneda, 'monedas' => Dinero::MONEDAS,
            'totalVentas' => $ventas->sum(fn ($v) => Dinero::centavos($v->monto_total)),
            'totalCobros' => $pagos->sum(fn ($p) => Dinero::centavos($p->monto)),
            'saldoPendiente' => $confirmadas->sum(fn ($v) => max(0, Dinero::centavos($v->monto_total) - Dinero::centavos($v->cobrado))),
            'numeroVentas' => $ventas->count(), 'numeroCobros' => $pagos->count(),
            'sinFecha' => $confirmadas->whereNull('fecha_confirmacion')->count(), 'serie' => $serie,
            'estados' => $solicitudes->groupBy(fn ($v) => $v->estado?->nombre_estado ?? 'Sin estado')->map->count(),
            'demanda' => $solicitudes->groupBy(fn ($v) => $v->programacion?->servicioRegion?->servicio?->nombre_servicio ?? 'Sin servicio')->map->count()->sortDesc(),
            'ocupacion' => Programacion::with(['servicioRegion.servicio', 'reservaciones.estado'])->whereBetween('fecha_inicio', [$desde, $hasta])->orderBy('fecha_inicio')->get(),
        ];

        return view('administracion.finanzas.indice', $datos);
    }
}
