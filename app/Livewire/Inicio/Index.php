<?php

namespace App\Livewire\Inicio;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.inicio.index');
    }
}
