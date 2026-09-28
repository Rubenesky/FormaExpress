<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Una evaluación por curso (regla de negocio del módulo 2).
     */
    public function up(): void
    {
        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->unique('curso_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // MySQL retiró el índice automático de la FK al crear el único y no deja
        // borrar el único mientras la FK lo use: se quita la FK, se borra el
        // único y se vuelve a crear la FK tal como estaba (recrea su índice).
        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->dropForeign(['curso_id']);
            $table->dropUnique(['curso_id']);
            $table->foreign('curso_id')->references('id')->on('cursos')->cascadeOnDelete();
        });
    }
};
