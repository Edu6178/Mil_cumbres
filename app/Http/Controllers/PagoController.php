<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\MetodoPago;
use App\Models\Reservacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PagoController extends Controller
{
    public function index()
    {
        $pagos = Pago::with([
            'reservacion',
            'metodoPago'
        ])
            ->latest()
            ->paginate(10);

        return view('pagos.index', compact('pagos'));
    }

    public function create()
    {
        $reservaciones = Reservacion::orderByDesc('id')->get();

        $metodos = MetodoPago::orderBy('id')->get();

        return view('pagos.create', compact(
            'reservaciones',
            'metodos'
        ));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'reservacion_id' => [
                'required',
                'integer',
                'exists:reservaciones,id',
            ],
            'metodo_pago_id' => [
                'required',
                'integer',
                'exists:metodo_pago,id',
            ],
            'monto' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,2',
                'max:9999999999.99',
            ],
            'fecha_pago' => [
                'nullable',
                'date',
            ],
            'referencia_comprobante' => [
                'nullable',
                'string',
                'max:180',
                'unique:pagos,referencia_comprobante',
            ],
            'proveedor' => [
                'nullable',
                'string',
                'max:120',
            ],
            'proveedor_referencia' => [
                'nullable',
                'string',
                'max:180',
            ],
            'url_pago' => [
                'nullable',
                'url:http,https',
                'max:2000',
            ],
        ]);

        $metodo = MetodoPago::findOrFail(
            $datos['metodo_pago_id']
        );

        $nombre = mb_strtolower($metodo->metodo);

        if ($nombre === 'link de pago') {
            $request->validate([
                'proveedor' => ['required', 'string', 'max:120'],
                'url_pago' => [
                    'required',
                    'url:http,https',
                    'max:2000',
                ],
            ]);

            $datos['referencia_comprobante'] = null;
            $datos['fecha_pago'] = null;
        } elseif (
            in_array($nombre, [
                'transferencia bancaria',
                'depósito bancario',
                'deposito bancario',
            ], true)
        ) {
            $request->validate([
                'fecha_pago' => ['required', 'date'],
                'referencia_comprobante' => [
                    'required',
                    'string',
                    'max:180',
                    Rule::unique('pagos', 'referencia_comprobante'),
                ],
            ]);

            $datos['proveedor'] = null;
            $datos['proveedor_referencia'] = null;
            $datos['url_pago'] = null;
        } else {
            throw ValidationException::withMessages([
                'metodo_pago_id' => 'Método de pago no permitido.',
            ]);
        }

        Pago::create($datos);

        return redirect()
            ->route('pagos.index')
            ->with('estado', 'Información del pago registrada correctamente.');
    }
}
