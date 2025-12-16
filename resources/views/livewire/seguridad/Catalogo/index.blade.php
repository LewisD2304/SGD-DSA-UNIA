@section('breadcrumb')
    <x-breadcrumb titulo="Catálogos de tablas">
        <x-breadcrumb.item titulo="Inicio"/>
        <x-breadcrumb.item titulo="/"/>
        <x-breadcrumb.item titulo="Seguridad"/>
        <x-breadcrumb.item titulo="/"/>
        <x-breadcrumb.item titulo="Catálogos"/>
    </x-breadcrumb>
@endsection
<div>
    <div class="app-container container-fluid">
        <div class="row g-5 g-md-7">
            <div class="col-xl-4">
                <livewire:components.seguridad.catalogo.lista-padre />
            </div>
            <div class="col-xl-8">
                @if($id_padre)
                    <livewire:components.seguridad.catalogo.lista-hijos :id_padre="$id_padre" :key="'lista-hijos-'.$id_padre" />
                @endif
            </div>
        </div>
    </div>
</div>
