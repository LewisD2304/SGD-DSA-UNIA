<div>
    @section('breadcrumb')
        <x-breadcrumb titulo="Áreas">
            <x-breadcrumb.item titulo="Inicio" route="inicio.index" separator />
            <x-breadcrumb.item titulo="Seguridad" separator />
            <x-breadcrumb.item titulo="Áreas" />
        </x-breadcrumb>
    @endsection

    @include('livewire.components.seguridad.Area.tabla')

    @include('livewire.components.seguridad.Area.modal-area')
    @include('livewire.components.seguridad.Area.modal-detalle-area')
    @include('livewire.components.seguridad.Area.modal-estado-area')
    @include('livewire.components.seguridad.Area.modal-eliminar-area')
</div>
