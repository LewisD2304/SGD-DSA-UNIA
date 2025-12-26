<?php

namespace App\Livewire\Documentos\Historial;

use App\Services\Documento\DocumentoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public $modeloDocumento = null;
    public $idAreaUsuario = 0;

    protected ?DocumentoService $documentoService = null;

    public function mount()
    {
        // Inicializar servicios dentro de mount para evitar problemas de ciclo de vida
        $this->documentoService = resolve(DocumentoService::class);
        $usuario = Auth::user();
        $this->idAreaUsuario = (int) ($usuario?->persona?->id_area ?? 0);
    }

    #[On('abrirModalDetalleDocumento')]
    public function abrirModalDetalleDocumento($id_documento)
    {
        // Asegurar el servicio inicializado por si el evento llega antes de mount
        if ($this->documentoService === null) {
            $this->documentoService = resolve(DocumentoService::class);
        }
        $this->modeloDocumento = $this->documentoService->obtenerPorIdParaArea(
            $id_documento,
            $this->idAreaUsuario,
            ['estado', 'tipoDocumento', 'archivos', 'areaRemitente', 'areaDestino']
        );

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
