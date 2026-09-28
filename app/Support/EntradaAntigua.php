<?php

namespace App\Support;

/**
 * Qué valores de un formulario rechazado se guardan para old().
 *
 * Laravel guarda toda la entrada tal cual. Si una petición manipulada envía un
 * array donde el formulario espera un texto (p. ej. titulo[]=x), la validación
 * lo rechaza bien, pero al volver al formulario {{ old('titulo') }} recibe un
 * array y la vista falla con un 500. Aquí se descartan esos valores, usando las
 * reglas del propio validador para saber qué campos son arrays legítimos.
 */
class EntradaAntigua
{
    /** Igual que el Handler de Laravel: las contraseñas nunca se guardan. */
    public const NO_GUARDAR = ['current_password', 'password', 'password_confirmation'];

    /**
     * @param  array<string, mixed>  $entrada  $request->input()
     * @param  array<string, array<int, mixed>>  $reglas  $validator->getRules()
     * @return array<string, mixed>
     */
    public static function limpiar(array $entrada, array $reglas): array
    {
        $limpia = [];

        foreach ($entrada as $campo => $valor) {
            if (in_array($campo, self::NO_GUARDAR, true)) {
                continue;
            }

            if (! is_array($valor)) {
                $limpia[$campo] = $valor;

                continue;
            }

            // Solo se guardan arrays que el formulario espera (regla "array"),
            // y de ellos solo sus valores simples.
            if (self::esperaArray($reglas[$campo] ?? [])) {
                $limpia[$campo] = array_filter($valor, fn ($elemento) => ! is_array($elemento));
            }
        }

        return $limpia;
    }

    /**
     * @param  array<int, mixed>  $reglas
     */
    private static function esperaArray(array $reglas): bool
    {
        foreach ($reglas as $regla) {
            if (is_string($regla) && ($regla === 'array' || str_starts_with($regla, 'array:'))) {
                return true;
            }
        }

        return false;
    }
}
