<div>
    @section('breadcrumb')
        <x-breadcrumb titulo="Personas">
            <x-breadcrumb.item titulo="Inicio" route="inicio.index" separator />
            <x-breadcrumb.item titulo="Seguridad" separator />
            <x-breadcrumb.item titulo="Personas" />
        </x-breadcrumb>
    @endsection

    <livewire:components.seguridad.persona.tabla/>
    

    @include('livewire.components.seguridad.persona.modal-persona')
    @include('livewire.components.seguridad.persona.modal-detalle-persona')
    @include('livewire.components.seguridad.persona.modal-estado-persona')
    @include('livewire.components.seguridad.persona.modal-eliminar-persona')
</div>
