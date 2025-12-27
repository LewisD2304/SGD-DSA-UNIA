<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Documento;
use App\Services\Documento\DocumentoService;

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

    /**
     * Registrar solicitud de rectificación desde vista pública.
     */
    public function solicitarRectificacion(Request $request, DocumentoService $service)
    {
        $data = $request->validate([
            'expediente' => ['required', 'string', 'max:100'],
            'motivo' => ['required', 'string', 'max:500'],
        ]);

        $expediente = trim($data['expediente']);
        $documento = Documento::where('expediente_documento', $expediente)->first();

        if (!$documento) {
            return redirect()->route('consulta.index', ['expediente' => $expediente])
                ->withErrors(['expediente' => 'Expediente no encontrado.']);
        }

        try {
            $service->registrarSolicitudRectificacionPublica($documento, $data['motivo']);

            return redirect()->route('consulta.buscar', ['expediente' => $expediente])
                ->with('status', 'Solicitud de rectificación enviada correctamente a Mesa de Partes.');
        } catch (\Exception $e) {
            return redirect()->route('consulta.buscar', ['expediente' => $expediente])
                ->withErrors(['motivo' => 'Error al enviar solicitud: ' . $e->getMessage()]);
        }
    }
}
