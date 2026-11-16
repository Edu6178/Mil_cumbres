<?php

namespace App\Http\Controllers;

use App\Models\User;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AutenticacionController extends Controller
{
    public function acceso(): View
    {
        return view('autenticacion.acceso');
    }

    public function registro(): View
    {
        return view('autenticacion.registro');
    }

    // Comprueba las credenciales con Laravel y renueva la sesión para evitar reutilizar su identificador.
    public function entrar(Request $request): RedirectResponse
    {
        $this->normalizarCorreo($request);
        $datos = $request->validate([
            'correo' => ['bail', 'required', 'string', 'email:rfc', 'max:254'],
            'contrasena' => ['bail', 'required', 'string', 'max:72'],
        ]);

        $clave = 'acceso:'.hash('sha256', $datos['correo'].'|'.$request->ip());
        $claveIp = 'acceso-ip:'.hash('sha256', (string) $request->ip());
        $this->comprobarLimite($clave, 5);
        $this->comprobarLimite($claveIp, 20);

        // También limita intentos con correos diferentes desde una misma dirección.
        RateLimiter::hit($clave, 60);
        RateLimiter::hit($claveIp, 60);

        if (! Auth::attempt(['email' => $datos['correo'], 'password' => $datos['contrasena']])) {
            throw ValidationException::withMessages([
                'correo' => 'El correo o la contraseña no son correctos.',
            ]);
        }

        RateLimiter::clear($clave);
        $request->session()->regenerate();

        // Las cuentas autorizadas entran al panel; las cuentas personales conservan su propia pantalla.
        $destino = Gate::allows('administrar') ? 'administracion.inicio' : 'cuenta';

        return redirect()->intended(route($destino, absolute: false));
    }

    // Crea únicamente una cuenta personal en users; no asigna permisos administrativos ni inventa clientes.
    public function registrar(Request $request): RedirectResponse
    {
        $this->normalizarCorreo($request);
        $clave = 'registro:'.hash('sha256', (string) $request->ip());
        $this->comprobarLimite($clave, 5);
        RateLimiter::hit($clave, 60);

        $datos = $request->validate([
            'nombre' => ['bail', 'required', 'string', 'max:120'],
            'correo' => ['bail', 'required', 'string', 'email:rfc', 'max:254', Rule::unique('users', 'email')],
            'contrasena' => [
                'bail', 'required', 'string', 'max:72', 'confirmed', Password::min(8)->letters()->numbers(),
                // Bcrypt admite hasta 72 bytes; se comprueba también al usar caracteres acentuados.
                function (string $atributo, mixed $valor, Closure $fallar): void {
                    if (strlen($valor) > 72) {
                        $fallar('La contraseña es demasiado larga. Usa una más corta.');
                    }
                },
            ],
        ]);

        try {
            // El modelo User convierte automáticamente la contraseña en un hash antes de guardarla.
            $usuario = User::create([
                'name' => $datos['nombre'],
                'email' => $datos['correo'],
                'password' => $datos['contrasena'],
            ]);
        } catch (UniqueConstraintViolationException) {
            // Conserva la unicidad incluso si dos registros llegan al mismo tiempo.
            throw ValidationException::withMessages(['correo' => 'Este correo ya tiene una cuenta.']);
        }

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('cuenta')->with('estado', 'Tu cuenta está lista. ¡Bienvenido a Mil Cumbres!');
    }

    // Permite cerrar la sesión incluso si la conexión deja de estar disponible.
    public function salir(Request $request): RedirectResponse
    {
        try {
            Auth::logout();
        } catch (QueryException $error) {
            report($error);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('acceso')->with('estado', 'La sesión se cerró correctamente.');
    }

    private function normalizarCorreo(Request $request): void
    {
        // Solo normaliza textos; los valores de otro tipo deben fallar en la validación.
        if (is_string($request->input('correo'))) {
            $request->merge(['correo' => Str::lower(trim($request->input('correo')))]);
        }
    }

    private function comprobarLimite(string $clave, int $maximo): void
    {
        if (RateLimiter::tooManyAttempts($clave, $maximo)) {
            $segundos = RateLimiter::availableIn($clave);

            throw ValidationException::withMessages([
                'correo' => "Has realizado varios intentos. Vuelve a intentarlo en {$segundos} segundos.",
            ]);
        }
    }
}
