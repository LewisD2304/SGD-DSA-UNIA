<div>
    @section('breadcrumb')
    <x-breadcrumb titulo="Gestión de documentos">
        <x-breadcrumb.item titulo="Mis documentos" />
    </x-breadcrumb>
    @endsection

    <livewire:components.Documentos.documento.tabla />

    @include('livewire.components.Documentos.Documento.modal-documento')
    @include('livewire.components.Documentos.Documento.modal-detalle-documento')
    @include('livewire.components.Documentos.Documento.modal-eliminar-documento')
    @include('livewire.components.Documentos.Documento.modal-anular-documento')
    @include('livewire.components.Documentos.Documento.modal-derivar-documento')
    @include('livewire.components.Documentos.Documento.modal-rectificar-documento')
    @include('livewire.components.Documentos.Documento.modal-responder-documento')
    @include('livewire.components.Documentos.Documento.modal-observacion-documento')
</div>
