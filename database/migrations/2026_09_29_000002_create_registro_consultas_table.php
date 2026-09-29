<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registro RGPD: quién (encargado o administrador) consultó la ficha de qué
     * empleado y cuándo. Solo se inserta; no se edita ni se borra desde la app.
     * FK con RESTRICT (decisión del usuario): la traza nunca se pierde.
     */
    public function up(): void
    {
        Schema::create('registro_consultas', function (Blueprint $table) {
            $table->id();
            // Quién consultó (encargado o administrador) y a quién.
            $table->foreignId('consultor_id')->constrained('empleados');
            $table->foreignId('consultado_id')->constrained('empleados');
            // Cuándo. Solo esta fecha: el registro no se edita, así que no hay updated_at.
            $table->dateTime('consultado_en');

            $table->index(['consultado_id', 'consultado_en']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_consultas');
    }
};
