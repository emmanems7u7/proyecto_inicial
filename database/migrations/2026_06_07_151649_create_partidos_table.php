<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('partidos', function (Blueprint $table) {
            $table->id();

            $table->string('api_event_id')->unique();

            $table->foreignId('equipo_local_id')->constrained('equipos');
            $table->foreignId('equipo_visitante_id')->constrained('equipos');

            $table->string('temporada')->nullable();
            $table->string('liga')->nullable();
            $table->string('ronda')->nullable();

            $table->date('fecha');
            $table->time('hora');

            $table->string('estadio')->nullable();
            $table->string('pais')->nullable();

            $table->integer('goles_local')->nullable();
            $table->integer('goles_visitante')->nullable();

            $table->string('estado')->nullable();

            $table->text('thumbnail')->nullable();
            $table->text('poster')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partidos');
    }
};
