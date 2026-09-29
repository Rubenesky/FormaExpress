<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada hito se concede como mucho una vez por empleado (módulo 3).
     */
    public function up(): void
    {
        Schema::table('insignias', function (Blueprint $table) {
            $table->unique(['empleado_id', 'hito']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // MySQL retira el índice automático de la FK al crear el único (empieza por
        // empleado_id) y no deja borrar el único mientras la FK lo use: se quita la
        // FK, se borra el único y se vuelve a crear la FK tal como estaba.
        Schema::table('insignias', function (Blueprint $table) {
            $table->dropForeign(['empleado_id']);
            $table->dropUnique(['empleado_id', 'hito']);
            $table->foreign('empleado_id')->references('id')->on('empleados');
        });
    }
};
