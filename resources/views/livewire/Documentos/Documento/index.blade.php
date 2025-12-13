<div>
    @section('breadcrumb')
    <x-breadcrumb titulo="Gestión de documentos">
        <x-breadcrumb.item titulo="Mis documentos" />
    </x-breadcrumb>
    @endsection

    <livewire:components.documentos.documento.tabla />

    @include('livewire.components.documentos.documento.modal-documento')
    @include('livewire.components.documentos.documento.modal-detalle-documento')
    @include('livewire.components.documentos.documento.modal-estado-documento')
    @include('livewire.components.documentos.documento.modal-eliminar-documento')
</div>
