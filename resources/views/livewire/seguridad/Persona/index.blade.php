<div>
    @section('breadcrumb')
        <x-breadcrumb titulo="Personas">
            <x-breadcrumb.item titulo="Inicio" route="inicio.index" separator />
            <x-breadcrumb.item titulo="Seguridad" separator />
            <x-breadcrumb.item titulo="Personas" />
        </x-breadcrumb>
    @endsection

    <livewire:components.seguridad.Persona.tabla/>


    @include('livewire.components.seguridad.Persona.modal-persona')
    @include('livewire.components.seguridad.Persona.modal-detalle-persona')
    @include('livewire.components.seguridad.Persona.modal-estado-persona')
    @include('livewire.components.seguridad.Persona.modal-eliminar-persona')
</div>
