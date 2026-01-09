@section('breadcrumb')
    <x-breadcrumb titulo="Roles">
        <x-breadcrumb.item titulo="Inicio" route="inicio.index" separator />
        <x-breadcrumb.item titulo="Seguridad" separator />
        <x-breadcrumb.item titulo="Roles" />
    </x-breadcrumb>
@endsection

<div>
    <livewire:components.seguridad.rol.tabla lazy/>

    @include('livewire.components.seguridad.rol.modal-rol')
    @include('livewire.components.seguridad.rol.modal-estado-rol')
    @include('livewire.components.seguridad.rol.modal-eliminar-rol')
</div>
