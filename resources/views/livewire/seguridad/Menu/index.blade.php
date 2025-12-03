@section('breadcrumb')
    <x-breadcrumb titulo="Lista de menús">
        <x-breadcrumb.item titulo="Inicio"/>
        <x-breadcrumb.item titulo="/"/>
        <x-breadcrumb.item titulo="Seguridad"/>
        <x-breadcrumb.item titulo="/"/>
        <x-breadcrumb.item titulo="Menú"/>
    </x-breadcrumb>
@endsection

<div>
    <livewire:components.seguridad.menu.tabla lazy/>

    @include('livewire.components.seguridad.menu.modal-menu')
    @include('livewire.components.seguridad.menu.modal-estado-menu')
    @include('livewire.components.seguridad.menu.modal-eliminar-menu')

</div>

@script
<script>

    const select2 = (campo, parametro, modal, multiple = false) => {
        var accion = @this.modo_modal; // Modo de acción del formulario

        // Verificar si es Registrar o Modificar
        if (accion === 1) {
            $('.'+campo).val(null).trigger('change');
        }
        else{
            const value = $('.' + campo).val();
            const esRequerido = $('.' + campo).siblings().hasClass('required');

            if ((!value || (Array.isArray(value) && value.length === 0)) && esRequerido) {
                $('.' + campo).addClass('is-invalid').removeClass('is-valid');
            } else if (!value || (Array.isArray(value) && value.length === 0)) {
                $('.' + campo).removeClass('is-valid');
            } else {
                $('.' + campo).removeClass('is-invalid').addClass('is-valid');
            }
        }

        $(`.${campo}`)
            .prop('multiple', multiple)
            .select2({
                placeholder: 'Abre esta selección',
                minimumResultsForSearch: parametro,
                dropdownParent:$(`#${modal}`).length ? $(`#${modal}`) : $(document.body),
                language: {
                    errorLoading: function () { return 'No se pudieron encontrar los resultados'; },
                    loadingMore: function () { return 'Cargando más recursos...'; },
                    noResults: function() { return "No hay resultado"; },
                    searching: function() { return "Buscando..."; }
                },
                allowClear: true,
            }).on('change', function(){
                const value = $(this).val();
                const esRequerido = $(this).siblings().hasClass('required');

                @this.set(campo, value);
                setTimeout(() => { $(this).next('.select2-container').removeClass('select2-container--focus'); }, 50);

                if ((!value || (Array.isArray(value) && value.length === 0)) && esRequerido) {
                    $(`.${campo}`).addClass('is-invalid').removeClass('is-valid');
                } else if (!value || (Array.isArray(value) && value.length === 0)) {
                    $(`.${campo}`).removeClass('is-valid');
                } else {
                    $(`.${campo}`).removeClass('is-invalid').addClass('is-valid');
                }
            })
            .on('select2:open', function() {
                $('.select2-results__options').addClass('custom-select2-results');
            })
            .each(function () {
                $(this).next().find('.select2-selection--single').addClass(`form-select ${campo}`);
            });
    }

    document.addEventListener('livewire:initialized', () => {
        select2('acciones', 8, 'modal-menu', true);
    });

    document.addEventListener("livewire:updated", () => {
        select2('acciones', 8, 'modal-menu', true);
    });

    window.addEventListener('errores_validacion', (e) => {
        Object.keys(e.detail.validacion).forEach(function(clave) {
            $(`.${clave}`).addClass('is-invalid');
        });
    });

    // Esto es por acción de formulario en modal
    window.addEventListener('autocompletado', (e) => {

        // Datos para autocompletar select
        var datos = {
            acciones: @this.acciones,
        };

        // Autocompletar select
        for (var clave in datos) {
            if (datos.hasOwnProperty(clave)) {
                var select2 = $('.'+clave);
                select2.val(datos[clave]);
                select2.trigger('change');
            }
        }

    });

</script>
@endscript
