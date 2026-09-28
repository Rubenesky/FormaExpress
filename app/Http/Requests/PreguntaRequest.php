<?php

namespace App\Http\Requests;

use App\Models\Pregunta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

/**
 * Pregunta de opción única: mínimo 2 opciones rellenas y exactamente 1
 * correcta. El radio "correcta" ya impide marcar más de una; aquí se exige
 * que haya una y que sea una opción rellena.
 */
class PreguntaRequest extends FormRequest
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
            'enunciado' => ['required', 'string', 'max:2000'],
            'opciones' => ['required', 'array', 'max:'.Pregunta::MAX_OPCIONES],
            'opciones.*' => ['nullable', 'string', 'max:255'],
            'correcta' => ['required', 'integer', 'between:0,'.(Pregunta::MAX_OPCIONES - 1)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'correcta.required' => 'Marca cuál es la opción correcta.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                // Si las opciones o la correcta ya tienen errores de formato, no se añaden más.
                if ($validator->errors()->hasAny(['opciones', 'opciones.*', 'correcta'])) {
                    return;
                }

                $rellenas = $this->opcionesRellenas();

                if ($rellenas->count() < 2) {
                    $validator->errors()->add('opciones', 'La pregunta necesita al menos 2 opciones.');
                } elseif (! $rellenas->has((int) $this->input('correcta'))) {
                    $validator->errors()->add('correcta', 'La opción marcada como correcta está vacía.');
                }
            },
        ];
    }

    /**
     * Opciones con texto, conservando su posición en el formulario.
     *
     * @return Collection<int, string>
     */
    public function opcionesRellenas(): Collection
    {
        return collect($this->input('opciones', []))
            ->map(fn ($texto) => is_string($texto) ? trim($texto) : '')
            ->filter(fn ($texto) => $texto !== '');
    }
}
