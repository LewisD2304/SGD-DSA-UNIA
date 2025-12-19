<div>
    @section('breadcrumb')
    <x-breadcrumb titulo="Bandeja de Entrada">
        <x-breadcrumb.item titulo="Pendientes" />
    </x-breadcrumb>
    @endsection

    <livewire:components.documentos.pendientes.tabla />
    @include('livewire.components.documentos.pendientes.modal-detalle-documento')
    @include('livewire.components.documentos.pendientes.modal-accion-documento')
</div>
