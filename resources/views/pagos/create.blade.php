@extends('administracion.layout')

@section('titulo', 'Registrar pago')

@section('contenido')

    <style>
        .pago-panel {
            background: #fff;
            border: 1px solid #dce5e1;
            border-radius: 12px;
            overflow: hidden;
            max-width: 1100px;
            margin: 0 auto;
        }

        .pago-encabezado {
            background: #198754;
            color: white;
            padding: 20px 25px;
        }

        .pago-encabezado h2 {
            margin: 0;
            font-size: 24px;
        }

        .pago-contenido {
            padding: 25px;
        }

        .pago-campo {
            margin-bottom: 20px;
        }

        .pago-campo label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .pago-control {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 7px;
            font: inherit;
            background: white;
            color: #222;
        }

        .pago-control:focus {
            outline: 2px solid #a3d6b9;
            border-color: #198754;
        }

        .pago-ayuda {
            display: block;
            color: #667085;
            margin-top: 5px;
            font-size: 13px;
        }

        .pago-aviso {
            padding: 15px;
            background: #fff3cd;
            color: #856404;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .pago-seccion {
            padding: 20px;
            background: #f7faf8;
            border: 1px solid #e0e8e3;
            border-radius: 9px;
            margin: 20px 0;
        }

        .pago-seccion h3 {
            margin: 0 0 18px;
            font-size: 20px;
        }

        .pago-botones {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .pago-boton {
            border: none;
            border-radius: 7px;
            padding: 12px 20px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .pago-guardar {
            background: #198754;
            color: white;
        }

        .pago-volver {
            background: #e9ecef;
            color: #333;
        }

        .pago-boton:disabled {
            opacity: .5;
            cursor: not-allowed;
        }
    </style>

    <div class="pago-panel">

        <div class="pago-encabezado">
            <h2>Registrar pago - Mil Cumbres</h2>
        </div>

        <div class="pago-contenido">

            <form action="{{ route('pagos.store') }}"
                  method="POST">

                @csrf

                <div class="pago-campo">
                    <label for="reservacion_id">
                        Reservación
                    </label>

                    <select
                        name="reservacion_id"
                        id="reservacion_id"
                        class="pago-control"
                        required
                    >
                        <option value="">
                            Selecciona una reservación
                        </option>

                        @foreach($reservaciones as $reservacion)
                            <option
                                value="{{ $reservacion->id }}"
                                @selected(old('reservacion_id') == $reservacion->id)
                            >
                                Reservación #{{ $reservacion->id }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($reservaciones->isEmpty())
                    <div class="pago-aviso">
                        No hay reservaciones registradas todavía.
                        Podrás registrar pagos cuando exista
                        una reservación.
                    </div>
                @endif

                <div class="pago-campo">
                    <label for="monto">
                        Monto del pago ($ MXN)
                    </label>

                    <input
                        type="text"
                        name="monto"
                        id="monto"
                        class="pago-control"
                        inputmode="decimal"
                        maxlength="13"
                        pattern="[0-9]{1,10}(\.[0-9]{1,2})?"
                        value="{{ old('monto') }}"
                        placeholder="Ej. 1500.00"
                        required
                    >

                    <small class="pago-ayuda">
                        Máximo 10 dígitos enteros y
                        2 decimales.
                    </small>
                </div>

                <div class="pago-campo">
                    <label for="metodo_pago_id">
                        Método de pago
                    </label>

                    <select
                        name="metodo_pago_id"
                        id="metodo_pago_id"
                        class="pago-control"
                        required
                    >
                        <option value="">
                            Selecciona un método
                        </option>

                        @foreach($metodos as $metodo)
                            <option
                                value="{{ $metodo->id }}"
                                data-metodo="{{ mb_strtolower($metodo->metodo) }}"
                                @selected(old('metodo_pago_id') == $metodo->id)
                            >
                                {{ $metodo->metodo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="datos-link"
                     class="pago-seccion"
                     hidden>

                    <h3>Datos del link de pago</h3>

                    <div class="pago-campo">
                        <label for="proveedor">
                            Proveedor
                        </label>

                        <input
                            type="text"
                            name="proveedor"
                            id="proveedor"
                            class="pago-control"
                            maxlength="120"
                            value="{{ old('proveedor') }}"
                            placeholder="Ej. Mercado Pago"
                        >
                    </div>

                    <div class="pago-campo">
                        <label for="url_pago">
                            URL del pago
                        </label>

                        <input
                            type="url"
                            name="url_pago"
                            id="url_pago"
                            class="pago-control"
                            maxlength="2000"
                            value="{{ old('url_pago') }}"
                            placeholder="https://..."
                        >
                    </div>

                    <div class="pago-campo">
                        <label for="proveedor_referencia">
                            Referencia del proveedor
                        </label>

                        <input
                            type="text"
                            name="proveedor_referencia"
                            id="proveedor_referencia"
                            class="pago-control"
                            maxlength="180"
                            value="{{ old('proveedor_referencia') }}"
                            placeholder="Identificador del enlace"
                        >
                    </div>
                </div>

                <div id="datos-bancarios"
                     class="pago-seccion"
                     hidden>

                    <h3 id="titulo-bancario">
                        Datos bancarios
                    </h3>

                    <div class="pago-campo">
                        <label for="fecha_pago">
                            Fecha y hora del pago
                        </label>

                        <input
                            type="datetime-local"
                            name="fecha_pago"
                            id="fecha_pago"
                            class="pago-control"
                            value="{{ old('fecha_pago') }}"
                        >
                    </div>

                    <div class="pago-campo">
                        <label for="referencia_comprobante">
                            Referencia del comprobante
                        </label>

                        <input
                            type="text"
                            name="referencia_comprobante"
                            id="referencia_comprobante"
                            class="pago-control"
                            maxlength="180"
                            value="{{ old('referencia_comprobante') }}"
                            placeholder="Folio o número de operación"
                        >
                    </div>
                </div>

                <div class="pago-botones">

                    <button
                        type="submit"
                        class="pago-boton pago-guardar"
                        @disabled($reservaciones->isEmpty())
                    >
                        Guardar información del pago
                    </button>

                    <a href="{{ route('pagos.index') }}"
                       class="pago-boton pago-volver">
                        Ver pagos
                    </a>

                </div>

            </form>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const monto = document.getElementById('monto');
            const metodo = document.getElementById('metodo_pago_id');

            const link = document.getElementById('datos-link');
            const banco = document.getElementById('datos-bancarios');

            const proveedor = document.getElementById('proveedor');
            const url = document.getElementById('url_pago');
            const fecha = document.getElementById('fecha_pago');
            const referencia = document.getElementById('referencia_comprobante');

            const tituloBanco = document.getElementById('titulo-bancario');

            monto.addEventListener('input', function () {
                let valor = this.value.replace(/[^0-9.]/g, '');
                const partes = valor.split('.');

                valor = partes[0].slice(0, 10);

                if (partes.length > 1) {
                    valor += '.' + partes[1].slice(0, 2);
                }

                this.value = valor;
            });

            function actualizarMetodo() {

                const opcion = metodo.options[metodo.selectedIndex];
                const nombre = opcion?.dataset.metodo || '';

                const esLink = nombre === 'link de pago';

                const esBanco = [
                    'transferencia bancaria',
                    'depósito bancario',
                    'deposito bancario'
                ].includes(nombre);

                link.hidden = !esLink;
                banco.hidden = !esBanco;

                proveedor.required = esLink;
                url.required = esLink;

                fecha.required = esBanco;
                referencia.required = esBanco;

                if (esBanco) {
                    tituloBanco.textContent =
                        nombre.includes('transferencia')
                            ? 'Datos de transferencia bancaria'
                            : 'Datos de depósito bancario';
                }
            }

            metodo.addEventListener('change', actualizarMetodo);

            actualizarMetodo();
        });
    </script>

@endsection
