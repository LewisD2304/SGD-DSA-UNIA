<div>
    @section('breadcrumb')
        <x-breadcrumb titulo="Áreas">
            <x-breadcrumb.item titulo="Inicio" route="inicio.index" separator />
            <x-breadcrumb.item titulo="Seguridad" separator />
            <x-breadcrumb.item titulo="Áreas" />
        </x-breadcrumb>
    @endsection

    @include('livewire.components.seguridad.area.tabla')

    @include('livewire.components.seguridad.area.modal-area')
    @include('livewire.components.seguridad.area.modal-detalle-area')
    @include('livewire.components.seguridad.area.modal-estado-area')
    @include('livewire.components.seguridad.area.modal-eliminar-area')
</div>
