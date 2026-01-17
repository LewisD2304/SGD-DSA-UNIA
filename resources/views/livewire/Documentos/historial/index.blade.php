<div>
    @section('breadcrumb')
    <x-breadcrumb titulo="Historial de Documentos">
        <x-breadcrumb.item titulo="Historial" />
    </x-breadcrumb>
    @endsection

    <livewire:components.Documentos.historial.tabla />
    @include('livewire.components.Documentos.historial.modal-detalle-documento')
</div>
