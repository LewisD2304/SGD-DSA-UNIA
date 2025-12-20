<?php

namespace App\Livewire\Components\Navegacion;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\Documento\DocumentoService;
use Illuminate\Support\Facades\Auth;

class SidebarBadge extends Component
{
    public $cantidad = 0;

    public function mount()
    {
        $this->calcularPendientes();
    }

    // Escucha el evento cuando haces clic en "Recepcionar" para actualizar el número
    #[On('refrescarDocumentos')]
    #[On('refrescarDocumentosPendientes')]
    public function calcularPendientes()
    {
        $user = Auth::user();

        if ($user && $user->persona && $user->persona->id_area) {
            // Usamos resolve para llamar a tu servicio
            $service = resolve(DocumentoService::class);
            // IMPORTANTE: Asegúrate de haber agregado la función contarPendientesPorArea en tu Service
            $this->cantidad = $service->contarPendientesPorArea($user->persona->id_area);
        }
    }

    public function render()
    {
        // Apunta a la vista que creamos en el paso 2
        return view('livewire.components.navegacion.sidebar-badge');
    }
}
