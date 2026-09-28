<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AsignacionRequest extends FormRequest
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
            'empleado_id' => [
                'required',
                'integer',
                // Solo se asignan cursos a empleados (no a administradores ni encargados).
                Rule::exists('empleados', 'id')->where('rol', 'empleado'),
                Rule::unique('asignaciones_curso', 'empleado_id')->where('curso_id', $this->route('curso')->id),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'empleado_id.unique' => 'Este empleado ya tiene el curso asignado.',
        ];
    }
}
