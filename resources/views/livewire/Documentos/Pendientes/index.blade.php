<div>
    @section('breadcrumb')
    <x-breadcrumb titulo="Bandeja de Entrada">
        <x-breadcrumb.item titulo="Pendientes" />
    </x-breadcrumb>
    @endsection

    <livewire:components.documentos.pendientes.tabla />
    @include('livewire.components.Documentos.Pendientes.modal-detalle-documento')
    @include('livewire.components.Documentos.Pendientes.modal-accion-documento')
</div>
