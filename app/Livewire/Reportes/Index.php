<?php

namespace App\Livewire\Reportes;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Reportes | GESTIÓN DOCUMENTAL')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.reportes.index');
    }
}
