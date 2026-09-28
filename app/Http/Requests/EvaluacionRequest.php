<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EvaluacionRequest extends FormRequest
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
            // Escala 0-10 con hasta 2 decimales (columna decimal(4,2)).
            'nota_minima' => ['required', 'numeric', 'decimal:0,2', 'between:0,10'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nota_minima.decimal' => 'La nota mínima admite como máximo 2 decimales.',
        ];
    }
}
