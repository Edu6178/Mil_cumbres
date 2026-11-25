<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EstadoReservacion;
use App\Models\Reservacion;
use App\Services\Finanzas\Dinero;
use App\Services\Finanzas\GestionFinanciera;
use App\Services\Busqueda\BuscadorAdministrativo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->validate(['buscar' => ['nullable', 'string', 'max:100'], 'estado' => ['nullable', 'integer', 'exists:estado_reservaciones,id']]);
        $ventas = Reservacion::with(['cliente.persona', 'estado', 'programacion.servicioRegion.servicio'])->conCobros()
            ->when($filtros['estado'] ?? null, fn ($q, $estado) => $q->where('estado_reservacion_id', $estado))
            ->when($filtros['buscar'] ?? null, fn ($q, $buscar) => $q->whereIn('reservaciones.id', BuscadorAdministrativo::solicitudes($buscar)->select('r.id')))
            ->latest('id')->paginate(15)->withQueryString();

        return view('administracion.ventas.indice', ['ventas' => $ventas, 'estados' => EstadoReservacion::orderBy('id')->get(), 'filtros' => $filtros]);
    }

    public function mostrar(Reservacion $reservacion, GestionFinanciera $finanzas)
    {
        $reservacion->load(['cliente.persona', 'acompanantes', 'estado', 'programacion.servicioRegion.servicio', 'programacion.servicioRegion.region', 'pagos.metodoPago']);

        return view('administracion.ventas.detalle', ['venta' => $reservacion, 'saldo' => $finanzas->saldo($reservacion), 'monedas' => Dinero::MONEDAS]);
    }

    public function confirmar(Request $request, Reservacion $reservacion, GestionFinanciera $finanzas)
    {
        $datos = $request->validate([
            'monto_total' => ['required', 'regex:/^\d{1,10}(\.\d{1,2})?$/', 'numeric', 'gt:0', 'max:9999999999.99'],
            'moneda' => ['required', Rule::in(Dinero::MONEDAS)], 'aceptar' => ['accepted'],
        ]);
        $finanzas->confirmarVenta($reservacion, $datos, $request->user()->id);

        return to_route('administracion.ventas.mostrar', $reservacion)->with('estado', 'Venta confirmada. El importe acordado quedó guardado.');
    }
}
