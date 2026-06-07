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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('orden')->default(0);
            $table->unsignedBigInteger('padre_id')->nullable();
            $table->unsignedBigInteger('seccion_id');
            $table->string('ruta')->nullable();
            $table->foreign('seccion_id')->references('id')->on('secciones')->onDelete('cascade');
            $table->foreign('padre_id')->references('id')->on('menus')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
