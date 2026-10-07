<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo de insignias (Tarea 2). "codigo" es la clave estable con la que
     * la aplicación las concede (constantes de App\Models\Insignia).
     *
     * Las 4 filas van escritas aquí, no leídas del modelo, para que la migración
     * dé siempre el mismo resultado aunque el modelo cambie.
     */
    public function up(): void
    {
        Schema::create('insignias_catalogo', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre');
            $table->string('descripcion');
        });

        // Descripciones como instrucción: sirven igual para insignias conseguidas y pendientes.
        DB::table('insignias_catalogo')->insert([
            ['codigo' => 'primer-certificado', 'nombre' => 'Primer curso certificado', 'descripcion' => 'Obtén tu primer certificado.'],
            ['codigo' => 'cinco-certificados', 'nombre' => '5 cursos certificados', 'descripcion' => 'Obtén 5 certificados.'],
            ['codigo' => 'nota-maxima', 'nombre' => 'Nota máxima', 'descripcion' => 'Saca un 10 en una evaluación.'],
            ['codigo' => 'primer-intento', 'nombre' => 'A la primera', 'descripcion' => 'Aprueba una evaluación en el primer intento.'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('insignias_catalogo');
    }
};
