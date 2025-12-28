<div>
    @section('breadcrumb')
    <x-breadcrumb titulo="Gestión de documentos">
        <x-breadcrumb.item titulo="Mis documentos" />
    </x-breadcrumb>
    @endsection

    <livewire:components.documentos.documento.tabla />

    @include('livewire.components.Documentos.documento.modal-documento')
    @include('livewire.components.Documentos.documento.modal-detalle-documento')
    @include('livewire.components.Documentos.documento.modal-observacion-documento')
    @include('livewire.components.Documentos.documento.modal-eliminar-documento')
    @include('livewire.components.Documentos.documento.modal-derivar-documento')
    @include('livewire.components.Documentos.documento.modal-rectificar-documento')
</div>
