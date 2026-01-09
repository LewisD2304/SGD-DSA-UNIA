<div>
    @section('breadcrumb')
    <x-breadcrumb titulo="Historial de Documentos">
        <x-breadcrumb.item titulo="Historial" />
    </x-breadcrumb>
    @endsection

    <livewire:components.documentos.historial.tabla />
    @include('livewire.components.documentos.historial.modal-detalle-documento')
</div>
