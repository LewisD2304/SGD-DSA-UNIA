<?php

namespace App\Livewire\Seguridad\Catalogo;

use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Catálogo de tablas | SGD-DSA')]
class Index extends Component
{
    public $id_padre;

    public function render()
    {
        return view('livewire.seguridad.catalogo.index');
    }

    public function mount()
    {
        $this->id_padre = request()->query('padre');
    }

    #[On('obtener_id_padre')]
    public function obtenerPadre($id_padre)
    {
        $this->id_padre = $id_padre;
    }
}
