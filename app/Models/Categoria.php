<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = ['nombre_categoria'];

    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class, 'categoria_id');
    }
}
