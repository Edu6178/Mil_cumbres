<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    public const ROLES = [
        'super_admin' => 'Superadministrador', 'admin' => 'Administrador',
        'empleado' => 'Empleado', 'guia' => 'Guía', 'cliente' => 'Cuenta personal',
    ];

    public function rolActual(): string
    {
        if ($this->rol !== null) return array_key_exists($this->rol, self::ROLES) ? $this->rol : 'cliente';
        $anteriores = config('administracion.usuarios', []);
        if (! in_array((int) $this->id, $anteriores, true)) return 'cliente';
        return ($anteriores[0] ?? null) === (int) $this->id ? 'super_admin' : 'admin';
    }

    public function accesoActivo(): bool
    {
        return ($this->estado_acceso ?? 'activo') === 'activo';
    }

    public function nombreRol(): string
    {
        return self::ROLES[$this->rolActual()];
    }

    public function destinoAcceso(): string
    {
        return in_array($this->rolActual(), ['admin', 'super_admin'], true) ? 'administracion.inicio'
            : (in_array($this->rolActual(), ['guia', 'empleado'], true) ? 'equipo.inicio' : 'cuenta');
    }
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Campos que se pueden guardar; se conservan los nombres de la tabla users de Laravel.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Datos que se ocultan al convertir una cuenta a un arreglo o respuesta JSON.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Convierte las fechas y protege la contraseña con un hash al guardarla.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'acceso_solicitado_at' => 'datetime',
            'acceso_revisado_at' => 'datetime',
        ];
    }
}
