<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CursoRequest extends FormRequest
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
            'descripcion' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
