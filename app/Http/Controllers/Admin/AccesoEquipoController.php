<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Busqueda\TextoBusqueda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccesoEquipoController extends Controller
{
    public function index(Request $request)
    {
        $datos = $request->validate(['buscar' => ['nullable', 'string', 'max:100'], 'estado' => ['nullable', Rule::in(['pendiente', 'activo', 'rechazado'])]]);
        $consulta = User::query()->when($datos['estado'] ?? null, fn ($q, $estado) => $q->where('estado_acceso', $estado));
        TextoBusqueda::filtrar($consulta, ['name', 'email'], $datos['buscar'] ?? '');
        return view('administracion.accesos', [
            'usuarios' => $consulta->orderByDesc('acceso_solicitado_at')->orderBy('name')->paginate(15)->withQueryString(),
            'pendientes' => User::where('estado_acceso', 'pendiente')->count(),
            'personal' => DB::table('personal')->join('personas', 'personas.id', '=', 'personal.persona_id')
                ->select('personal.id', 'personal.tipo_rol', 'personal.estado_laboral', 'personas.nombre', 'personas.apellido_paterno')->orderBy('personas.nombre')->get(),
            'vinculados' => User::whereNotNull('personal_id')->pluck('id', 'personal_id'),
        ]);
    }

    public function actualizar(User $usuario, Request $request)
    {
        $datos = $request->validate([
            'rol' => ['required', Rule::in(array_keys(User::ROLES))],
            'estado_acceso' => ['required', Rule::in(['activo', 'rechazado', 'pendiente'])],
            'personal_id' => ['nullable', 'integer', 'exists:personal,id', Rule::unique('users', 'personal_id')->ignore($usuario->id)],
        ]);

        DB::transaction(function () use ($usuario, $request, $datos) {
            // Serializa cambios de permisos y conserva al menos un superadministrador activo.
            $cuentas = User::orderBy('id')->lockForUpdate()->get();
            $actor = $cuentas->firstWhere('id', $request->user()->id);
            abort_unless($actor?->accesoActivo() && $actor->rolActual() === 'super_admin', 403);
            $actual = $cuentas->firstWhere('id', $usuario->id);
            if ($actual->accesoActivo() && $actual->rolActual() === 'super_admin'
                && ($datos['rol'] !== 'super_admin' || $datos['estado_acceso'] !== 'activo')
                && $cuentas->filter(fn ($u) => $u->accesoActivo() && $u->rolActual() === 'super_admin')->count() <= 1) {
                throw ValidationException::withMessages(['rol' => 'Conserva al menos un superadministrador activo.']);
            }
            if ($actual->estado_acceso === 'pendiente' && $datos['estado_acceso'] === 'activo'
                && ! in_array($datos['rol'], ['admin', 'empleado', 'guia'], true)) {
                throw ValidationException::withMessages(['rol' => 'Aprueba la solicitud como administrador, empleado o guía.']);
            }
            if (! empty($datos['personal_id'])) {
                if ($cuentas->contains(fn ($cuenta) => $cuenta->id !== $actual->id && $cuenta->personal_id === (int) $datos['personal_id'])) {
                    throw ValidationException::withMessages(['personal_id' => 'Esta ficha laboral ya está vinculada a otra cuenta.']);
                }
                $persona = DB::table('personal')->where('id', $datos['personal_id'])->lockForUpdate()->first();
                if ($datos['rol'] === 'guia' && ! in_array(mb_strtolower($persona->tipo_rol), ['guia', 'guía'], true)) {
                    throw ValidationException::withMessages(['personal_id' => 'Para el rol guía, selecciona una ficha laboral de guía.']);
                }
            }
            $actual->forceFill([...$datos, 'personal_id' => $datos['personal_id'] ?? null,
                'acceso_revisado_at' => now(), 'acceso_revisado_por' => $actor->id])->save();
        }, 3);

        if ($usuario->id === $request->user()->id) {
            return to_route($usuario->fresh()->destinoAcceso())->with('estado', 'Tu acceso se actualizó.');
        }
        return back()->with('estado', 'Acceso actualizado. La cuenta utilizará los permisos asignados.');
    }
}
