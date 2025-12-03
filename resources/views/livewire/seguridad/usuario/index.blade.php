<div>
    @section('breadcrumb')
        <x-breadcrumb titulo="Usuarios">
            <x-breadcrumb.item titulo="Inicio" route="inicio.index" separator />
            <x-breadcrumb.item titulo="Seguridad" separator />
            <x-breadcrumb.item titulo="Usuarios" />
        </x-breadcrumb>
    @endsection

    <livewire:components.seguridad.usuario.tabla/>

    @include('livewire.components.seguridad.usuario.modal-usuario')
    @include('livewire.components.seguridad.usuario.modal-estado-usuario')
    @include('livewire.components.seguridad.usuario.modal-eliminar-usuario')
</div>
