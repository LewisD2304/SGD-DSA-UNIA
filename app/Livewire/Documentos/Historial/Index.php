<?php

namespace App\Livewire\Documentos\Historial;

use App\Services\Documento\DocumentoService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public $modeloDocumento = null;

    protected DocumentoService $documentoService;

    public function __construct()
    {
        $this->documentoService = resolve(DocumentoService::class);
    }

    #[On('abrirModalDetalleDocumento')]
    public function abrirModalDetalleDocumento($id_documento)
    {
        $this->modeloDocumento = $this->documentoService->obtenerPorId($id_documento, ['estado', 'tipoDocumento', 'archivos', 'areaRemitente', 'areaDestino']);

        $this->dispatch('cargando', cargando: 'false');
        $this->modalDocumento('#modal-detalle-documento', 'show');
    }

    public function modalDocumento($nombre, $accion)
    {
        $this->dispatch(
            'modal',
            nombre: $nombre,
            accion: $accion
        );
    }

    public function render()
    {
        return view('livewire.documentos.historial.index');
    }
}
