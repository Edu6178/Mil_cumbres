@extends('administracion.layout')

@section('titulo', 'Ventas y Pagos')

@section('contenido')

    <style>
        .pagos-contenedor {
            background: white;
            border: 1px solid #dce5e1;
            border-radius: 12px;
            padding: 25px;
        }

        .pagos-superior {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .pagos-superior h2 {
            margin: 0;
            color: #193e34;
        }

        .pagos-nuevo {
            background: #198754;
            color: white;
            padding: 12px 18px;
            border-radius: 7px;
            text-decoration: none;
            font-weight: 600;
        }

        .pagos-tabla-contenedor {
            overflow-x: auto;
        }

        .pagos-tabla {
            width: 100%;
            border-collapse: collapse;
        }

        .pagos-tabla th {
            background: #193e34;
            color: white;
            padding: 13px;
            text-align: left;
        }

        .pagos-tabla td {
            padding: 13px;
            border-bottom: 1px solid #e5e7eb;
        }

        .pagos-tabla tr:hover {
            background: #f7faf8;
        }

        .pagos-vacio {
            padding: 30px;
            text-align: center;
            color: #667085;
        }

        .pagos-paginacion {
            margin-top: 20px;
        }
    </style>

    <div class="pagos-contenedor">

        <div class="pagos-superior">

            <h2>Registro de Ventas y Pagos</h2>

            <a href="{{ route('pagos.create') }}"
               class="pagos-nuevo">
                + Registrar pago
            </a>

        </div>

        <div class="pagos-tabla-contenedor">

            <table class="pagos-tabla">

                <thead>
                <tr>
                    <th>ID</th>
                    <th>Reservación</th>
                    <th>Método</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                    <th>Referencia</th>
                    <th>Proveedor</th>
                </tr>
                </thead>

                <tbody>

                @forelse($pagos as $pago)

                    <tr>
                        <td>{{ $pago->id }}</td>

                        <td>
                            #{{ $pago->reservacion_id }}
                        </td>

                        <td>
                            {{ $pago->metodoPago?->metodo ?? 'No disponible' }}
                        </td>

                        <td>
                            ${{ number_format((float) $pago->monto, 2) }}
                        </td>

                        <td>
                            {{ $pago->fecha_pago?->format('d/m/Y H:i') ?? 'Sin confirmar' }}
                        </td>

                        <td>
                            {{ $pago->referencia_comprobante ?? '—' }}
                        </td>

                        <td>
                            {{ $pago->proveedor ?? '—' }}
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="pagos-vacio">
                            Todavía no existen pagos registrados.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="pagos-paginacion">
            {{ $pagos->links() }}
        </div>

    </div>

@endsection
