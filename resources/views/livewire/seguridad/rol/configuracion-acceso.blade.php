@section('breadcrumb')
    <x-breadcrumb titulo="Configurar acceso">
        <x-breadcrumb.item titulo="Inicio"/>
        <x-breadcrumb.item titulo="/"/>
        <x-breadcrumb.item titulo="Seguridad"/>
        <x-breadcrumb.item titulo="/"/>
        <x-breadcrumb.item titulo="Rol"/>
        <x-breadcrumb.item titulo="/"/>
        <x-breadcrumb.item titulo="Configuración"/>
    </x-breadcrumb>
@endsection
<div>
    @livewire('components.seguridad.rol.configuracion-acceso.tabla', ['id_rol' => $id_rol], key('tabla-'.$id_rol))

    @include('livewire.components.seguridad.rol.configuracion-acceso.modal-acceso')
    @include('livewire.components.seguridad.rol.configuracion-acceso.modal-permiso')
    @include('livewire.components.seguridad.rol.configuracion-acceso.modal-eliminar-acceso')
</div>
