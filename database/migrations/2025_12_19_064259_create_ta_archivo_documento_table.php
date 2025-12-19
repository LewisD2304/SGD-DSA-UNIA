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
        Schema::create('ta_archivo_documento', function (Blueprint $table) {
            $table->id('id_archivo_documento');
            $table->unsignedBigInteger('id_documento');
            $table->string('nombre_original', 255);
            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 500);
            $table->string('extension', 10);
            $table->bigInteger('tamanio')->comment('Tamaño en bytes');
            $table->integer('orden')->default(0)->comment('Orden de visualización');

            // Auditoría
            $table->timestamp('au_fechacr')->useCurrent();
            $table->timestamp('au_fechamd')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('au_fechael')->nullable();
            $table->string('au_usuariocr', 100)->nullable();
            $table->string('au_usuariomd', 100)->nullable();
            $table->string('au_usuarioel', 100)->nullable();

            // Llave foránea
            $table->foreign('id_documento')->references('id_documento')->on('ta_documento')->onDelete('cascade');

            // Índices
            $table->index('id_documento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ta_archivo_documento');
    }
};
