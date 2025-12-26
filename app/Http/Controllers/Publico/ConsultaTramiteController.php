<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Documento;

class ConsultaTramiteController extends Controller
{
    /**
     * Página de inicio de consulta pública.
     */
    public function index(Request $request)
    {
        $expediente = trim((string) $request->query('expediente'));
        $resultado = null;

        if ($expediente !== '') {
            $resultado = $this->buscarDocumentoPorExpediente($expediente);
        }

        return view('public.consulta.inicio', [
            'expediente' => $expediente,
            'resultado' => $resultado,
        ]);
    }

    /**
     * Maneja el POST del formulario.
     */
    public function buscar(Request $request)
    {
        $data = $request->validate([
            'expediente' => ['required', 'string']
        ]);

        $expediente = trim($data['expediente']);
        $resultado = $this->buscarDocumentoPorExpediente($expediente);

        return view('public.consulta.inicio', [
            'expediente' => $expediente,
            'resultado' => $resultado,
        ]);
    }

    private function buscarDocumentoPorExpediente(string $expediente)
    {
        return Documento::with(['estado', 'areaRemitente', 'areaDestino', 'movimientos.estado'])
            ->where('expediente_documento', $expediente)
            ->first();
    }
}
