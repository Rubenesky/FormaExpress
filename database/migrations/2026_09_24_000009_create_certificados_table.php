<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('certificados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados');
            $table->foreignId('curso_id')->constrained('cursos');
            $table->date('fecha_emision');
            $table->timestamps();

            // Como mucho un certificado por empleado y curso: el primer aprobado
            // lo emite, y aprobar otra vez no crea una fila nueva ni cambia la fecha.
            $table->unique(['empleado_id', 'curso_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificados');
    }
};
