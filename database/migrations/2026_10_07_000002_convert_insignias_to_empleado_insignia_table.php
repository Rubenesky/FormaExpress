<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La tabla "insignias" guardaba concesiones con el hito como texto libre.
     * Pasa a ser "empleado_insignia": relación entre empleados e
     * insignias_catalogo, con insignia_id en lugar de hito.
     */
    public function up(): void
    {
        // Antes de cambiar nada: todo hito existente tiene que estar en el catálogo.
        // Si no, se aborta sin tocar la tabla (no se borra ni se adivina nada).
        $desconocidos = DB::table('insignias')
            ->whereNotIn('hito', DB::table('insignias_catalogo')->select('codigo'))
            ->distinct()
            ->pluck('hito');

        if ($desconocidos->isNotEmpty()) {
            throw new RuntimeException(
                'Hay insignias concedidas con un hito que no está en insignias_catalogo: '
                .$desconocidos->implode(', ').'. No se ha cambiado nada.'
            );
        }

        Schema::rename('insignias', 'empleado_insignia');

        Schema::table('empleado_insignia', function (Blueprint $table) {
            $table->unsignedBigInteger('insignia_id')->nullable()->after('empleado_id');
        });

        DB::table('empleado_insignia')->update([
            'insignia_id' => DB::raw('(SELECT id FROM insignias_catalogo WHERE insignias_catalogo.codigo = empleado_insignia.hito)'),
        ]);

        Schema::table('empleado_insignia', function (Blueprint $table) {
            $table->unsignedBigInteger('insignia_id')->nullable(false)->change();
            $table->foreign('insignia_id')->references('id')->on('insignias_catalogo');
            // Se crea antes de quitar el antiguo: empieza por empleado_id, así que
            // MySQL puede usarlo para la FK de empleado_id en todo momento.
            $table->unique(['empleado_id', 'insignia_id']);
        });

        // La FK de empleado_id no se toca: en MySQL conserva su nombre original
        // (insignias_empleado_id_foreign), porque renombrar una tabla no renombra
        // sus restricciones y SQLite (tests) no permite borrar una FK por nombre.
        Schema::table('empleado_insignia', function (Blueprint $table) {
            $table->dropUnique('insignias_empleado_id_hito_unique');
            $table->dropColumn('hito');
        });
    }

    /**
     * Vuelve a "insignias" con hito; cada insignia_id se traduce a su código.
     */
    public function down(): void
    {
        Schema::table('empleado_insignia', function (Blueprint $table) {
            $table->string('hito')->nullable()->after('empleado_id');
        });

        DB::table('empleado_insignia')->update([
            'hito' => DB::raw('(SELECT codigo FROM insignias_catalogo WHERE insignias_catalogo.id = empleado_insignia.insignia_id)'),
        ]);

        Schema::table('empleado_insignia', function (Blueprint $table) {
            $table->string('hito')->nullable(false)->change();
            $table->unique(['empleado_id', 'hito'], 'insignias_empleado_id_hito_unique');
        });

        Schema::table('empleado_insignia', function (Blueprint $table) {
            $table->dropForeign(['insignia_id']);
            $table->dropUnique('empleado_insignia_empleado_id_insignia_id_unique');
            $table->dropColumn('insignia_id');
        });

        Schema::rename('empleado_insignia', 'insignias');
    }
};
