<?php

namespace App\Http\Controllers;

use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\Reservacion;
use App\Services\Finanzas\Dinero;
use App\Services\Finanzas\GestionFinanciera;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->validate(['estado' => ['nullable', Rule::in(['pendiente', 'confirmado', 'anulado'])], 'moneda' => ['nullable', Rule::in(Dinero::MONEDAS)]]);
        $pagos = Pago::with(['reservacion.cliente.persona', 'metodoPago'])
            ->when($filtros['estado'] ?? null, fn ($q, $estado) => $q->where('estado', $estado))
            ->when($filtros['moneda'] ?? null, fn ($q, $moneda) => $q->where('moneda', $moneda))
            ->latest('id')->paginate(15)->withQueryString();

        return view('pagos.index', ['pagos' => $pagos, 'filtros' => $filtros, 'monedas' => Dinero::MONEDAS]);
    }

    public function create(Request $request)
    {
        $request->validate(['reservacion' => ['nullable', 'integer', 'exists:reservaciones,id']]);
        $reservaciones = Reservacion::with(['cliente.persona', 'programacion.servicioRegion.servicio'])->conCobros()
            ->whereHas('estado', fn ($q) => $q->where('nombre_estado', 'Confirmada'))->where('monto_total', '>', 0)->latest('id')->get();

        return view('pagos.create', [
            'reservaciones' => $reservaciones, 'metodos' => MetodoPago::orderBy('metodo')->get(),
            'monedas' => Dinero::MONEDAS, 'seleccion' => $request->integer('reservacion'), 'clave' => (string) Str::uuid(),
        ]);
    }

    public function store(Request $request, GestionFinanciera $finanzas)
    {
        if (is_string($request->input('proveedor'))) {
            $request->merge(['proveedor' => mb_strtolower(trim($request->input('proveedor')))]);
        }
        $previo = is_string($request->input('clave_registro')) ? Pago::where('clave_registro', $request->input('clave_registro'))->first() : null;
        $datos = $request->validate([
            'reservacion_id' => ['required', 'integer', 'exists:reservaciones,id'],
            'metodo_pago_id' => ['required', 'integer', 'exists:metodo_pago,id'],
            'monto' => ['required', 'regex:/^\d{1,10}(\.\d{1,2})?$/', 'numeric', 'gt:0', 'max:9999999999.99'],
            'moneda' => ['required', Rule::in(Dinero::MONEDAS)], 'clave_registro' => ['required', 'uuid'],
            'fecha_pago' => ['nullable', 'date', 'before_or_equal:now'],
            'referencia_comprobante' => ['nullable', 'string', 'max:180', Rule::unique('pagos')->ignore($previo?->id)],
            'proveedor' => ['nullable', 'string', 'max:120'], 'proveedor_referencia' => ['nullable', 'string', 'max:180'],
            'url_pago' => ['nullable', 'url:https', 'max:2000'],
        ]);
        $metodo = MetodoPago::findOrFail($datos['metodo_pago_id']);
        if ($request->filled('url_pago') || Str::contains(mb_strtolower($metodo->metodo), ['link', 'enlace'])) {
            $request->validate(['proveedor' => ['required', 'string', 'max:120'], 'url_pago' => ['required', 'url:https', 'max:2000']]);
        }
        if ($request->filled('proveedor_referencia') && !$request->filled('proveedor')) {
            throw ValidationException::withMessages(['proveedor' => 'Indica el proveedor de esta referencia.']);
        }
        $datos['monto'] = Dinero::decimal(Dinero::centavos($datos['monto']));
        $pago = $this->guardarSinDuplicados(fn () => $finanzas->registrarPago($datos));

        return to_route('pagos.show', $pago)->with('estado', 'Registro guardado pendiente de revisión. El cobro requiere confirmación manual.');
    }

    public function show(Pago $pago, GestionFinanciera $finanzas)
    {
        $pago->load(['reservacion.cliente.persona', 'reservacion.estado', 'metodoPago', 'confirmador']);

        return view('pagos.show', ['pago' => $pago, 'saldo' => $finanzas->saldo($pago->reservacion)]);
    }

    public function confirmar(Request $request, Pago $pago, GestionFinanciera $finanzas)
    {
        $bancario = Str::contains(Str::ascii(mb_strtolower($pago->metodoPago->metodo)), ['transferencia', 'deposito']);
        $datos = $request->validate([
            'fecha_pago' => ['required', 'date', 'before_or_equal:now'], 'verificado' => ['accepted'],
            'referencia_comprobante' => [$bancario ? 'required' : 'nullable', 'string', 'max:180', Rule::unique('pagos')->ignore($pago->id)],
        ]);
        $this->guardarSinDuplicados(fn () => $finanzas->confirmarPago($pago, $datos, $request->user()->id));

        return to_route('pagos.show', $pago)->with('estado', 'Cobro confirmado manualmente. El saldo de la venta se actualizó.');
    }

    public function anular(Pago $pago, GestionFinanciera $finanzas)
    {
        $finanzas->anularPago($pago);

        return to_route('pagos.show', $pago)->with('estado', 'Registro anulado. Se conserva en el historial y no suma ingresos.');
    }

    private function guardarSinDuplicados(callable $operacion): mixed
    {
        try {
            return $operacion();
        } catch (QueryException $error) {
            // La restricción de base de datos también protege peticiones simultáneas.
            if (in_array((string) $error->getCode(), ['23000', '23505'], true)) {
                throw ValidationException::withMessages(['referencia_comprobante' => 'La referencia o este formulario ya fue registrado. Revisa el historial antes de repetirlo.']);
            }
            throw $error;
        }
    }
}
