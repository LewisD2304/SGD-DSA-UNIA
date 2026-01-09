<?php

namespace App\Livewire\Reportes;

use App\Models\Documento;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Detalle del Documento | GESTIÓN DOCUMENTAL')]
class DocumentosDetalles extends Component
{
    public $documento;
    public $idDocumento;

    public function mount($idDocumento)
    {
        $this->idDocumento = $idDocumento;

        $this->documento = Documento::with([
            'areaRemitente',
            'areaDestino',
            'estado',
            'tipoDocumento',
            'archivos',
            'movimientos' => function($q) {
                $q->with(['areaOrigen', 'areaDestino', 'estado'])
                  ->orderBy('au_fechacr', 'desc');
            }
        ])->findOrFail($idDocumento);
    }

    public function render()
    {
        return view('livewire.reportes.documentos-detalles');
    }
}
