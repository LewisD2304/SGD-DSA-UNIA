<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Services\Documento\ArchivoDocumentoService;
use Illuminate\Support\Facades\Schema;

class ArchivoDocumentoServiceTest extends TestCase
{
    public function test_guardar_multiples_archivos_incluye_id_area_si_existe_columna()
    {
        Storage::fake('share');
        $service = new ArchivoDocumentoService();

        $file = UploadedFile::fake()->create('prueba.pdf', 10, 'application/pdf');

        $result = $service->guardarMultiplesArchivos([
            $file
        ], 'gestion.documentos.documentos', 123, 3, 'share');

        $this->assertCount(1, $result);
        $payload = $result[0];

        $this->assertArrayHasKey('ruta_archivo', $payload);
        $this->assertTrue(Storage::disk('share')->exists($payload['ruta_archivo']));

        if (Schema::hasColumn('ta_archivo_documento', 'id_area')) {
            $this->assertArrayHasKey('id_area', $payload);
            $this->assertSame(3, $payload['id_area']);
        }
    }
}
