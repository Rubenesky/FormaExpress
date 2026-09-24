<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['titulo', 'descripcion'])]
class Curso extends Model
{
    public function lecciones(): HasMany
    {
        return $this->hasMany(Leccion::class)->orderBy('orden');
    }
}
