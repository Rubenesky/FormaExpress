<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mensajes de validación
    |--------------------------------------------------------------------------
    |
    | Solo se traducen las reglas que usa la aplicación. Una regla que no
    | aparezca aquí se muestra en inglés (idioma de respaldo, clave a clave).
    |
    */

    'accepted' => 'Debes aceptar el campo :attribute.',
    'array' => 'El campo :attribute no tiene un formato válido.',
    'between' => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
    ],
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'current_password' => 'La contraseña no es correcta.',
    'date' => 'El campo :attribute debe ser una fecha válida.',
    'decimal' => 'El campo :attribute debe tener :decimal decimales.',
    'email' => 'El campo :attribute debe ser un correo electrónico válido.',
    'exists' => 'El :attribute seleccionado no es válido.',
    'in' => 'El :attribute seleccionado no es válido.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'max' => [
        'numeric' => 'El campo :attribute no puede ser mayor que :max.',
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
    ],
    'min' => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'numeric' => 'El campo :attribute debe ser un número.',
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser un texto.',
    'unique' => 'Ya existe un registro con ese :attribute.',

    /*
    |--------------------------------------------------------------------------
    | Nombres de los campos
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'titulo' => 'título',
        'descripcion' => 'descripción',
        'contenido' => 'contenido',
        'orden' => 'orden',
        'empleado_id' => 'empleado',
        'nota_minima' => 'nota mínima',
        'enunciado' => 'enunciado',
        'opciones' => 'opciones',
        'opciones.*' => 'opción',
        'correcta' => 'opción correcta',
    ],

];
