<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatosDemostracionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AccesosEquipoTest extends TestCase
{
    use RefreshDatabase;
    private string $registro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->registro = storage_path('framework/testing/accesos-demo-'.bin2hex(random_bytes(8)).'.json');
        config(['demostracion.registro' => $this->registro]);
    }

    protected function tearDown(): void
    {
        File::delete($this->registro);
        parent::tearDown();
    }

    public function test_solo_superadmin_puede_revisar_y_aprobar_una_solicitud(): void
    {
        $pendiente = $this->usuario('empleado', 'pendiente');
        foreach (['cliente', 'empleado', 'guia', 'admin'] as $rol) {
            $this->actingAs($this->usuario($rol))->get('/administracion/accesos')->assertForbidden();
            $this->patch('/administracion/accesos/'.$pendiente->id, ['rol' => 'admin', 'estado_acceso' => 'activo'])->assertForbidden();
        }
        $super = $this->usuario('super_admin');
        $this->actingAs($super)->get('/administracion/accesos')->assertOk()->assertSee($pendiente->email);
        $this->patch('/administracion/accesos/'.$pendiente->id, ['rol' => 'guia', 'estado_acceso' => 'activo'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $pendiente->id, 'rol' => 'guia', 'estado_acceso' => 'activo', 'acceso_revisado_por' => $super->id]);
    }

    public function test_solicitud_pendiente_no_puede_iniciar_sesion_y_aprobada_va_a_su_agenda(): void
    {
        $usuario = $this->usuario('empleado', 'pendiente');
        $this->post('/acceso', ['correo' => $usuario->email, 'contrasena' => 'Bosque123'])->assertSessionHasErrors('correo');
        $this->assertGuest();
        $usuario->forceFill(['estado_acceso' => 'activo'])->save();
        $this->post('/acceso', ['correo' => $usuario->email, 'contrasena' => 'Bosque123'])->assertRedirect('/equipo');
        $this->get('/equipo')->assertOk()->assertSee('Mi agenda')->assertDontSee('Resumen financiero');
        $this->get('/administracion')->assertForbidden();
        $this->get('/pagos')->assertForbidden();
    }

    public function test_no_desactiva_ni_degrada_al_ultimo_superadministrador(): void
    {
        $super = $this->usuario('super_admin');
        $this->actingAs($super)->patch('/administracion/accesos/'.$super->id, ['rol' => 'admin', 'estado_acceso' => 'activo'])->assertSessionHasErrors('rol');
        $this->patch('/administracion/accesos/'.$super->id, ['rol' => 'super_admin', 'estado_acceso' => 'rechazado'])->assertSessionHasErrors('rol');
        $this->assertTrue(Gate::forUser($super->fresh())->allows('gestionar-accesos'));
    }

    public function test_un_rol_revisado_tiene_prioridad_sobre_la_lista_anterior(): void
    {
        $usuario = $this->usuario('empleado');
        config(['administracion.usuarios' => [$usuario->id]]);
        $this->assertFalse(Gate::forUser($usuario)->allows('administrar'));
        $this->assertTrue(Gate::forUser($usuario)->allows('equipo'));
    }

    public function test_desactivar_una_cuenta_cierra_su_sesion_existente(): void
    {
        $usuario = $this->usuario('guia');
        $this->actingAs($usuario)->get('/equipo')->assertOk();
        $usuario->forceFill(['estado_acceso' => 'rechazado'])->save();
        $this->get('/equipo')->assertRedirect('/acceso');
        $this->assertGuest();
    }

    public function test_guia_solo_ve_sus_asignaciones_y_la_ficha_no_se_puede_compartir(): void
    {
        $this->seed(DatosDemostracionSeeder::class);
        $personal = DB::table('personal')->where('tipo_rol', 'guia')->first();
        $actividad = DB::table('programaciones')->where('fecha_fin', '>', now())->first();
        DB::table('asigna_guias')->where('personal_id', $personal->id)->delete();
        DB::table('asigna_guias')->insert(['personal_id' => $personal->id, 'programacion_id' => $actividad->id, 'fecha_asignacion' => now(), 'disponibilidad' => true]);
        $guia = $this->usuario('guia');
        $guia->forceFill(['personal_id' => $personal->id])->save();
        $this->actingAs($guia)->get('/equipo')->assertOk()->assertViewHas('actividades', fn ($lista) => $lista->pluck('id')->all() === [$actividad->id]);
        $otro = $this->usuario('guia');
        $this->actingAs($otro)->get('/equipo')->assertOk()->assertViewHas('actividades', fn ($lista) => $lista->isEmpty());
        $this->actingAs($this->usuario('super_admin'))->patch('/administracion/accesos/'.$otro->id,
            ['rol' => 'guia', 'estado_acceso' => 'activo', 'personal_id' => $personal->id])->assertSessionHasErrors('personal_id');
    }

    public function test_la_consola_puede_habilitar_una_cuenta_existente_sin_crear_usuarios(): void
    {
        $usuario = $this->usuario('cliente');
        $this->artisan('equipo:superadmin', ['correo' => $usuario->email])->assertSuccessful();
        $this->assertSame('super_admin', $usuario->fresh()->rol);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_la_migracion_conserva_cuentas_y_promueve_al_primer_admin_que_existe(): void
    {
        $primero = User::factory()->create();
        $segundo = User::factory()->create();
        config(['administracion.usuarios' => [99999, $primero->id, $segundo->id]]);
        $migracion = require database_path('migrations/2026_11_25_120000_agregar_accesos_del_equipo.php');
        $migracion->down();
        $migracion->up();
        $this->assertSame('super_admin', $primero->fresh()->rol);
        $this->assertSame('admin', $segundo->fresh()->rol);
        $this->assertDatabaseCount('users', 2);
    }

    private function usuario(string $rol, string $estado = 'activo'): User
    {
        $usuario = User::factory()->create(['password' => 'Bosque123']);
        $usuario->forceFill(['rol' => $rol, 'estado_acceso' => $estado])->save();
        return $usuario;
    }
}
