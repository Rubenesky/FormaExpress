<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['titulo', 'contenido', 'orden'])]
class Leccion extends Model
{
    // Laravel deduciría "leccions".
    protected $table = 'lecciones';

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }
}
