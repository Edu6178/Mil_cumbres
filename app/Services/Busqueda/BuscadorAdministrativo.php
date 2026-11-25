<?php

namespace App\Services\Busqueda;

use App\Models\Categoria;
use App\Models\Servicio;
use App\Services\Administracion\AgendaGuias;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BuscadorAdministrativo
{
    public function buscar(string $texto, string $ambito = 'todo', array $filtros = []): array
    {
        $texto = trim($texto);
        $resultados = $ambito === 'todo' ? $this->apartados($texto) : [];
        // Al enfocar se ofrecen accesos; no se descargan registros privados sin escribir una búsqueda.
        if ($texto === '') return array_slice($resultados, 0, 6);

        if (in_array($ambito, ['todo', 'servicios'], true)) {
            $consulta = Servicio::with('categoria');
            TextoBusqueda::filtrar($consulta, ['nombre_servicio', 'descripcion'], $texto);
            if ($ambito === 'servicios') {
                if (! empty($filtros['categoria'])) $consulta->where('categoria_id', $filtros['categoria']);
                if (! empty($filtros['estado'])) $consulta->where('estado_servicio', $filtros['estado']);
            }
            foreach ($consulta->orderBy('nombre_servicio')->limit($ambito === 'todo' ? 3 : 8)->get() as $servicio) {
                $resultados[] = $this->ficha('Servicio', $servicio->nombre_servicio,
                    ($servicio->categoria?->nombre_categoria ?? 'Sin categoría').' · '.ucfirst($servicio->estado_servicio),
                    $servicio->descripcion ?? 'Consulta el itinerario, la duración y la tarifa de este servicio.',
                    route('administracion.servicios.editar', $servicio), $servicio->nombre_servicio);
            }
        }
        if (in_array($ambito, ['todo', 'ventas'], true)) {
            $consulta = self::solicitudes($texto);
            if ($ambito === 'ventas' && ! empty($filtros['estado'])) $consulta->where('r.estado_reservacion_id', $filtros['estado']);
            foreach ($consulta->select('r.id', 'r.fecha_solicitud', 'r.numero_personas', 'p.nombre', 'p.apellido_paterno', 'p.apellido_materno', 's.nombre_servicio', 'e.nombre_estado')
                ->orderByDesc('r.id')->limit($ambito === 'todo' ? 3 : 8)->get() as $reserva) {
                $folio = 'MC-'.substr($reserva->fecha_solicitud, 0, 4).'-'.str_pad((string) $reserva->id, 6, '0', STR_PAD_LEFT);
                $nombre = $this->nombre($reserva);
                $resultados[] = $this->ficha('Solicitud', $nombre, $folio.' · '.$reserva->nombre_estado,
                    ($reserva->nombre_servicio ?? 'Experiencia por definir').' · '.$reserva->numero_personas.' personas. Abre la solicitud para revisar el grupo y sus importes.',
                    route('administracion.ventas.mostrar', $reserva->id), $folio);
            }
        }
        if (in_array($ambito, ['todo', 'guias'], true)) {
            $consulta = AgendaGuias::guias();
            TextoBusqueda::filtrar($consulta, ['personas.nombre', 'personas.apellido_paterno', 'personas.apellido_materno', 'personal.formacion', 'personal.talentos', 'personal.tipo_rol'], $texto);
            foreach ($consulta->orderBy('personas.nombre')->limit($ambito === 'todo' ? 3 : 8)->get() as $guia) {
                $nombre = $this->nombre($guia);
                $resultados[] = $this->ficha('Guía', $nombre, $guia->estado_laboral ?: 'Estado sin especificar',
                    'Formación: '.($guia->formacion ?: 'Por registrar').'. Talentos: '.($guia->talentos ?: 'Por registrar').'.',
                    route('administracion.guias', ['buscar' => mb_substr($nombre, 0, 100)]).'#guia-'.$guia->id, $nombre);
            }
        }
        if ($ambito === 'todo') {
            $categorias = Categoria::withCount('servicios');
            TextoBusqueda::filtrar($categorias, ['nombre_categoria'], $texto);
            foreach ($categorias->orderBy('nombre_categoria')->limit(2)->get() as $categoria) {
                $resultados[] = $this->ficha('Categoría', $categoria->nombre_categoria, $categoria->servicios_count.' servicios',
                    'Agrupa los servicios de esta categoría y consulta su nombre.', route('administracion.categorias.editar', $categoria), $categoria->nombre_categoria);
            }
            $pagos = DB::table('pagos as pago')->leftJoin('reservaciones as r', 'r.id', '=', 'pago.reservacion_id')
                ->leftJoin('clientes as c', 'c.id', '=', 'r.cliente_id')->leftJoin('personas as p', 'p.id', '=', 'c.persona_id');
            if (preg_match('/^(?:pago\s*#?\s*)?(\d+)$/iu', $texto, $id)) {
                $pagos->where('pago.id', (int) $id[1]);
            } else {
                TextoBusqueda::filtrar($pagos, ['p.nombre', 'p.apellido_paterno', 'p.apellido_materno', 'pago.referencia_comprobante', 'pago.estado'], $texto);
            }
            foreach ($pagos->select('pago.id', 'pago.monto', 'pago.moneda', 'pago.estado', 'p.nombre', 'p.apellido_paterno', 'p.apellido_materno')->orderByDesc('pago.id')->limit(2)->get() as $pago) {
                $resultados[] = $this->ficha('Pago', 'Pago #'.$pago->id.' · '.$this->nombre($pago), ucfirst($pago->estado),
                    number_format((float) $pago->monto, 2).' '.$pago->moneda.'. Consulta el abono y su validación.', route('pagos.show', $pago->id), 'Pago #'.$pago->id);
            }
            $agenda = AgendaGuias::programaciones();
            TextoBusqueda::filtrar($agenda, ['servicios.nombre_servicio', 'regiones.nombre_region'], $texto);
            foreach ($agenda->orderByDesc('fecha_inicio')->limit(2)->get() as $actividad) {
                $resultados[] = $this->ficha('Programación', $actividad->nombre_servicio, substr($actividad->fecha_inicio, 0, 16).' · '.$actividad->estado,
                    $actividad->nombre_region.'. Revisa el horario y el cupo de esta salida.', route('administracion.calendario.editar', $actividad->id), $actividad->nombre_servicio);
            }
        }

        return array_slice($resultados, 0, 18);
    }

    // Comparte el mismo criterio con el listado de ventas: cada palabra puede coincidir en un campo diferente.
    public static function solicitudes(string $texto)
    {
        $consulta = DB::table('reservaciones as r')->leftJoin('clientes as c', 'c.id', '=', 'r.cliente_id')
            ->leftJoin('personas as p', 'p.id', '=', 'c.persona_id')->leftJoin('programaciones as a', 'a.id', '=', 'r.programacion_id')
            ->leftJoin('servicio_regiones as sr', 'sr.id', '=', 'a.servicio_reg_id')->leftJoin('servicios as s', 's.id', '=', 'sr.servicio_id')
            ->leftJoin('estado_reservaciones as e', 'e.id', '=', 'r.estado_reservacion_id');
        if (preg_match('/^(?:MC-(\d{4})-)?(\d+)$/i', trim($texto), $id)) {
            $consulta->where('r.id', (int) $id[2]);
            if ($id[1] !== '') $consulta->whereYear('r.fecha_solicitud', (int) $id[1]);
        } else {
            TextoBusqueda::filtrar($consulta, ['p.nombre', 'p.apellido_paterno', 'p.apellido_materno', 's.nombre_servicio'], $texto);
        }

        return $consulta;
    }

    private function apartados(string $texto): array
    {
        $apartados = [
            ['Resumen general', 'administracion.inicio', 'Reservaciones, servicios y próximos recorridos en una sola vista.', 'inicio panel dashboard estadísticas'],
            ['Resumen financiero', 'administracion.finanzas', 'Compara ventas confirmadas, ingresos cobrados por fecha de pago y saldos por cobrar.', 'finanzas reportes ingresos cobros dinero presupuesto'],
            ['Solicitudes y ventas', 'administracion.ventas.indice', 'Encuentra pre-reservas por cliente o folio, revisa el grupo y confirma el importe acordado.', 'clientes reservas reservaciones pedidos'],
            ['Servicios y precios', 'administracion.servicios.indice', 'Consulta y edita experiencias, itinerarios, duración, tarifas y visibilidad.', 'catálogo consultoría experiencias actividades'],
            ['Pagos y abonos', 'pagos.index', 'Revisa enlaces y referencias, registra abonos y valida los pagos recibidos.', 'cobros depósitos finanzas'],
            ['Calendario y cupos', 'administracion.calendario.indice', 'Programa salidas, consulta lugares disponibles y bloquea fechas o territorios.', 'agenda horarios disponibilidad fechas'],
            ['Equipo de guías', 'administracion.guias', 'Busca al personal registrado como guía por nombre, formación o talentos.', 'empleados personas especialidades equipo'],
            ['Asignación de guías', 'administracion.programaciones', 'Consulta las actividades y asigna guías según su disponibilidad.', 'agenda programaciones equipo disponibilidad'],
            ['Categorías', 'administracion.categorias.indice', 'Organiza las líneas del catálogo y consulta sus servicios asociados.', 'clasificación tipos'],
            ['Fotografías y portadas', 'administracion.fotografias', 'Carga fotografías, organiza los álbumes y el carrusel, y cambia las portadas y las imágenes de los servicios.', 'fotos imágenes galería contenido carrusel'],
            ['Canales externos', 'administracion.canales.indice', 'Registra reservas recibidas por otros canales y conserva su origen.', 'booking tripadvisor agencias'],
            ['Participantes', 'administracion.asistencia', 'Consulta los integrantes de cada grupo. El registro de asistencia está pendiente.', 'visitantes grupos personas asistencia'],
            ['Mi perfil', 'administracion.perfil', 'Consulta los datos de tu cuenta y actualiza tu fotografía.', 'cuenta usuario foto'],
            ['Página pública', 'inicio', 'Abre el portal de experiencias, comunidades y perfiles de guías.', 'inicio web visitantes'],
        ];
        $fichas = [];
        foreach ($apartados as [$nombre, $ruta, $resumen, $claves]) {
            if (TextoBusqueda::coincide($nombre.' '.$claves, $texto)) {
                $fichas[] = $this->ficha('Apartado', $nombre, 'Administración', $resumen, route($ruta), $nombre);
            }
        }
        usort($fichas, fn ($a, $b) => (int) str_starts_with(TextoBusqueda::normalizar($b['titulo']), TextoBusqueda::normalizar($texto))
            <=> (int) str_starts_with(TextoBusqueda::normalizar($a['titulo']), TextoBusqueda::normalizar($texto)));

        return array_slice($fichas, 0, $texto === '' ? 6 : 4);
    }

    private function nombre(object $persona): string
    {
        return Str::squish(implode(' ', [$persona->nombre ?? '', $persona->apellido_paterno ?? '', $persona->apellido_materno ?? ''])) ?: 'Cliente sin nombre registrado';
    }

    private function ficha(string $tipo, string $titulo, string $contexto, string $resumen, string $url, string $valor): array
    {
        $valor = mb_substr($valor, 0, 100);
        return compact('tipo', 'titulo', 'contexto', 'url', 'valor') + ['resumen' => Str::limit(Str::squish(strip_tags($resumen)), 220)];
    }
}
