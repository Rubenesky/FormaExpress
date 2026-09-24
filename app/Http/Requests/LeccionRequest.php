<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeccionRequest extends FormRequest
{
    /**
     * El acceso ya lo controla el middleware can:administrar de la ruta.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'contenido' => ['required', 'string'],
            'orden' => ['required', 'integer', 'min:1', 'max:65535'],
        ];
    }
}
