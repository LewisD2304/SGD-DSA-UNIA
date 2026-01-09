<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ta_archivo_documento', 'tipo_archivo')) {
            Schema::table('ta_archivo_documento', function (Blueprint $table) {
                $table->string('tipo_archivo', 50)->default('original')->after('id_area')->comment('Tipo: original, evidencia_rectificacion');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ta_archivo_documento', 'tipo_archivo')) {
            Schema::table('ta_archivo_documento', function (Blueprint $table) {
                $table->dropColumn('tipo_archivo');
            });
        }
    }
};
