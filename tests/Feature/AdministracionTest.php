<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdministracionTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_visitante_debe_iniciar_sesion(): void
    {
        $this->get('/administracion')->assertRedirect('/acceso');
        $this->post('/administracion/servicios', [])->assertRedirect('/acceso');
    }

    public function test_una_cuenta_publica_no_puede_leer_ni_modificar_la_administracion(): void
    {
        $usuario = User::factory()->create();
        config(['administracion.usuarios' => []]);
        $this->actingAs($usuario);

        foreach (['', '/servicios', '/servicios/crear', '/categorias', '/guias', '/programaciones', '/asistencia'] as $ruta) {
            $this->get('/administracion'.$ruta)->assertForbidden();
        }
        $this->post('/administracion/categorias', ['nombre_categoria' => 'Sin autorización', 'admin' => true])
            ->assertForbidden();
        $this->post('/administracion/servicios', ['role' => 'admin'])->assertForbidden();
        $this->assertDatabaseCount('categorias', 0);
        $this->assertDatabaseCount('servicios', 0);
    }

    public function test_el_panel_y_los_listados_vacios_funcionan_para_una_cuenta_autorizada(): void
    {
        $this->autorizar();

        foreach (['', '/servicios', '/servicios/crear', '/categorias', '/guias', '/programaciones', '/asistencia'] as $ruta) {
            $this->get('/administracion'.$ruta)->assertOk();
        }
        $this->get('/mi-cuenta')->assertSee('Entrar a administración');
        $this->get('/administracion/asistencia')->assertSee('próxima etapa');
    }

    public function test_permite_crear_y_editar_categorias_sin_duplicar_nombres(): void
    {
        $this->autorizar();
        $this->post('/administracion/categorias', ['nombre_categoria' => 'Senderismo'])
            ->assertRedirect('/administracion/categorias');
        $categoria = Categoria::firstOrFail();
        $this->get('/administracion/categorias/'.$categoria->id.'/editar')->assertOk();
        $this->put('/administracion/categorias/'.$categoria->id, ['nombre_categoria' => 'Recorridos'])
            ->assertRedirect('/administracion/categorias');
        $this->post('/administracion/categorias', ['nombre_categoria' => 'Recorridos'])
            ->assertSessionHasErrors(['nombre_categoria' => 'Ya existe una categoría con ese nombre.']);
        $this->assertDatabaseCount('categorias', 1);
        $this->assertDatabaseHas('categorias', ['id' => $categoria->id, 'nombre_categoria' => 'Recorridos']);
    }

    public function test_crea_un_servicio_y_regresa_al_catalogo_con_confirmacion(): void
    {
        $this->autorizar();
        $categoria = Categoria::create(['nombre_categoria' => 'Senderismo']);
        $this->post('/administracion/servicios', $this->datosServicio($categoria->id))
            ->assertRedirect('/administracion/servicios')->assertSessionHas('estado');
        $servicio = Servicio::firstOrFail();
        $this->assertSame('1250.50', $servicio->precio_base);
        $this->get('/administracion/servicios/'.$servicio->id.'/editar')->assertOk()->assertSee('Camino del bosque');
        $this->get('/administracion/servicios')->assertSee('Recientemente creado');
    }

    #[DataProvider('serviciosInvalidos')]
    public function test_valida_la_tarifa_y_los_datos_del_servicio(array $cambios, string $campo): void
    {
        $this->autorizar();
        $categoria = Categoria::create(['nombre_categoria' => 'Senderismo']);
        $this->post('/administracion/servicios', [...$this->datosServicio($categoria->id), ...$cambios])
            ->assertSessionHasErrors($campo);
        $this->assertDatabaseCount('servicios', 0);
    }

    public static function serviciosInvalidos(): array
    {
        return [
            'precio negativo' => [['precio_base' => '-10'], 'precio_base'],
            'más de dos decimales' => [['precio_base' => '10.123'], 'precio_base'],
            'categoría inexistente' => [['categoria_id' => 9999], 'categoria_id'],
            'duración inválida' => [['duracion_estimada' => 0], 'duracion_estimada'],
            'estado no permitido' => [['estado_servicio' => 'inventado'], 'estado_servicio'],
        ];
    }

    public function test_actualizar_precio_y_ocultar_no_altera_reservaciones_ni_historial(): void
    {
        $this->autorizar();
        [$programacion, $servicio] = $this->programacion();
        $persona = DB::table('personas')->insertGetId(['nombre' => 'María', 'apellido_paterno' => 'López']);
        $cliente = DB::table('clientes')->insertGetId(['persona_id' => $persona, 'fecha_registro' => '2026-11-13']);
        $estado = DB::table('estado_reservaciones')->insertGetId(['nombre_estado' => 'Confirmada']);
        $reserva = DB::table('reservaciones')->insertGetId([
            'cliente_id' => $cliente, 'programacion_id' => $programacion, 'estado_reservacion_id' => $estado,
            'fecha_solicitud' => '2026-11-13 10:00:00', 'numero_personas' => 2, 'monto_total' => '2501.00',
        ]);
        $creado = $servicio->created_at->toDateTimeString();

        $this->put('/administracion/servicios/'.$servicio->id, [
            ...$this->datosServicio($servicio->categoria_id), 'precio_base' => '1500.00',
        ])->assertRedirect('/administracion/servicios');
        $this->patch('/administracion/servicios/'.$servicio->id.'/visibilidad', ['estado_servicio' => 'oculto'])
            ->assertRedirect();

        $this->assertDatabaseHas('servicios', ['id' => $servicio->id, 'precio_base' => '1500.00', 'estado_servicio' => 'oculto', 'created_at' => $creado]);
        $this->assertDatabaseHas('reservaciones', ['id' => $reserva, 'monto_total' => '2501.00']);
        $this->assertDatabaseHas('programaciones', ['id' => $programacion]);
        $this->delete('/administracion/servicios/'.$servicio->id)->assertStatus(405);
        $this->assertDatabaseCount('servicios', 1);
    }

    public function test_busca_y_filtra_el_catalogo_sin_mezclar_categorias(): void
    {
        $this->autorizar();
        $categoria = Categoria::create(['nombre_categoria' => 'Senderismo']);
        Servicio::create($this->datosServicio($categoria->id));
        Servicio::create([...$this->datosServicio($categoria->id), 'nombre_servicio' => 'Recorrido oculto', 'estado_servicio' => 'oculto']);
        $this->get('/administracion/servicios?buscar=CAMINO&estado=activo&categoria='.$categoria->id)
            ->assertOk()->assertSee('Camino del bosque')->assertDontSee('Recorrido oculto');
    }

    public function test_asigna_varios_guias_sin_duplicados_y_conserva_las_bajas(): void
    {
        $this->autorizar();
        [$programacion] = $this->programacion();
        $primero = $this->guia('Ana');
        $segundo = $this->guia('Luis');

        foreach ([[$primero, $segundo], [$primero, $segundo], [$segundo]] as $seleccion) {
            $this->put('/administracion/programaciones/'.$programacion.'/guias', ['guias' => $seleccion])
                ->assertRedirect('/administracion/programaciones');
        }
        $this->assertDatabaseCount('asigna_guias', 2);
        $this->assertDatabaseHas('asigna_guias', ['personal_id' => $primero, 'disponibilidad' => false]);
        $this->assertDatabaseHas('asigna_guias', ['personal_id' => $segundo, 'disponibilidad' => true]);
        $this->get('/administracion/guias')->assertOk()->assertSee('Ana');
        $this->get('/administracion/programaciones')->assertOk()->assertSee('Camino del bosque');
        $this->get('/administracion/programaciones/'.$programacion.'/guias')->assertOk()->assertSee('Luis');
    }

    public function test_un_traslape_rechaza_toda_la_asignacion_sin_cambios_parciales(): void
    {
        $this->autorizar();
        [$primera] = $this->programacion();
        [$segunda] = $this->programacion('2026-12-01 10:00:00', '2026-12-01 13:00:00');
        $ocupado = $this->guia('Ana');
        $libre = $this->guia('Luis');
        $this->put('/administracion/programaciones/'.$primera.'/guias', ['guias' => [$ocupado]])->assertRedirect();
        $this->put('/administracion/programaciones/'.$segunda.'/guias', ['guias' => [$libre, $ocupado]])
            ->assertSessionHasErrors('guias');
        $this->assertDatabaseCount('asigna_guias', 1);
        $this->assertDatabaseMissing('asigna_guias', ['programacion_id' => $segunda]);
    }

    public function test_permite_actividades_consecutivas_y_rechaza_guias_inactivos(): void
    {
        $this->autorizar();
        [$primera] = $this->programacion();
        [$segunda] = $this->programacion('2026-12-01 12:00:00', '2026-12-01 15:00:00');
        $guia = $this->guia('Ana');
        $this->put('/administracion/programaciones/'.$primera.'/guias', ['guias' => [$guia]])->assertRedirect();
        $this->put('/administracion/programaciones/'.$segunda.'/guias', ['guias' => [$guia]])->assertRedirect();
        DB::table('personal')->where('id', $guia)->update(['estado_laboral' => 'inactivo']);
        $this->put('/administracion/programaciones/'.$segunda.'/guias', ['guias' => [$guia]])
            ->assertSessionHasErrors('guias');
        $this->assertDatabaseCount('asigna_guias', 2);
    }

    public function test_no_admite_personal_de_otro_rol_ni_actividades_bloqueadas(): void
    {
        $this->autorizar();
        [$programacion] = $this->programacion();
        $personal = $this->guia('Ana');
        DB::table('personal')->where('id', $personal)->update(['tipo_rol' => 'administracion']);
        $this->put('/administracion/programaciones/'.$programacion.'/guias', ['guias' => [$personal]])->assertSessionHasErrors('guias');
        DB::table('programaciones')->where('id', $programacion)->update(['estado' => 'bloqueado']);
        $this->put('/administracion/programaciones/'.$programacion.'/guias', [])->assertSessionHasErrors('guias');
        $this->assertDatabaseCount('asigna_guias', 0);
    }

    public function test_lista_participantes_sin_exponer_notas_medicas_y_no_simula_asistencia(): void
    {
        $this->autorizar();
        [$programacion] = $this->programacion();
        $persona = DB::table('personas')->insertGetId(['nombre' => 'María', 'apellido_paterno' => 'López']);
        $cliente = DB::table('clientes')->insertGetId(['persona_id' => $persona, 'fecha_registro' => '2026-11-13']);
        $estado = DB::table('estado_reservaciones')->insertGetId(['nombre_estado' => 'Confirmada']);
        $reserva = DB::table('reservaciones')->insertGetId([
            'cliente_id' => $cliente, 'programacion_id' => $programacion, 'estado_reservacion_id' => $estado,
            'fecha_solicitud' => '2026-11-13 10:00:00', 'numero_personas' => 1,
        ]);
        DB::table('asigna_reservacion_invitados')->insert([
            'reservacion_id' => $reserva, 'persona_id' => $persona, 'notas_medicas' => 'INFORMACION-PRIVADA-NO-MOSTRAR',
        ]);
        $this->get('/administracion/asistencia/'.$programacion)->assertOk()->assertSee('María')
            ->assertSee('Solo consulta')->assertDontSee('INFORMACION-PRIVADA-NO-MOSTRAR');
        $this->get('/administracion/asistencia/999999')->assertNotFound();
    }

    private function autorizar(): User
    {
        $usuario = User::factory()->create();
        config(['administracion.usuarios' => [$usuario->id]]);
        $this->actingAs($usuario);

        return $usuario;
    }

    private function datosServicio(int $categoria): array
    {
        return [
            'categoria_id' => $categoria, 'nombre_servicio' => 'Camino del bosque',
            'descripcion' => 'Una experiencia de prueba.', 'precio_base' => '1250.50',
            'itinerario' => 'Encuentro y recorrido.', 'duracion_estimada' => 180, 'estado_servicio' => 'activo',
        ];
    }

    private function programacion(string $inicio = '2026-12-01 09:00:00', string $fin = '2026-12-01 12:00:00'): array
    {
        $categoria = Categoria::create(['nombre_categoria' => 'Categoría '.Categoria::count()]);
        $servicio = Servicio::create($this->datosServicio($categoria->id));
        $region = DB::table('regiones')->insertGetId(['nombre_region' => 'Región de prueba']);
        $relacion = DB::table('servicio_regiones')->insertGetId(['servicio_id' => $servicio->id, 'region_id' => $region, 'cupo_maximo' => 20]);
        $programacion = DB::table('programaciones')->insertGetId([
            'servicio_reg_id' => $relacion, 'cupo_maximo' => 20, 'fecha_inicio' => $inicio, 'fecha_fin' => $fin,
        ]);

        return [$programacion, $servicio];
    }

    private function guia(string $nombre): int
    {
        $persona = DB::table('personas')->insertGetId(['nombre' => $nombre, 'apellido_paterno' => 'Prueba']);

        return DB::table('personal')->insertGetId(['persona_id' => $persona, 'tipo_rol' => 'guia', 'estado_laboral' => 'activo']);
    }
}
