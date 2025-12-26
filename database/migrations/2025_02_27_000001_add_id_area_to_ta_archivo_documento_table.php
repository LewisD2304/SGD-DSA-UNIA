<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ta_archivo_documento', 'id_area')) {
            Schema::table('ta_archivo_documento', function (Blueprint $table) {
                $table->unsignedBigInteger('id_area')->nullable()->after('id_documento');

                $table->foreign('id_area')
                    ->references('id_area')
                    ->on('ta_area')
                    ->onDelete('set null');

                $table->index('id_area');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ta_archivo_documento', 'id_area')) {
            Schema::table('ta_archivo_documento', function (Blueprint $table) {
                $table->dropForeign(['id_area']);
                $table->dropIndex(['id_area']);
                $table->dropColumn('id_area');
            });
        }
    }
};
