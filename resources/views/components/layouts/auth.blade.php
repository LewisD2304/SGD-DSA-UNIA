<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ $title ?? 'Login | Sistema de Gestión Documental | SGD' }}</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('media/logo-unia.webp') }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
</head>

<body id="kt_body" class="app-blank">
    <div id="kt_app_root" class="d-flex flex-column flex-root">
        {{ $slot }}
    </div>

    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/custom/authentication/sign-in/general.js') }}"></script>

</body>
<script data-navigate-track>
    window.addEventListener('toastr', event => {
        toastr.options = {
            "closeButton": event.detail.boton_cerrar
            , "debug": false
            , "newestOnTop": false
            , "progressBar": event.detail.progreso_avance
            , "positionClass": "toastr-" + event.detail.posicion_y + "-" + event.detail.posicion_x
            , "preventDuplicates": false
            , "onclick": null
            , "showDuration": "300"
            , "hideDuration": "1000"
            , "timeOut": event.detail.duracion
            , "extendedTimeOut": "1000"
            , "showEasing": "swing"
            , "hideEasing": "linear"
            , "showMethod": "fadeIn"
            , "hideMethod": "fadeOut"
        };

        // Obtener el tipo de toastr del evento
        const tipo = event.detail.tipo;

        // Mostrar el toastr según el tipo
        switch (tipo) {
            case 'success':
                toastr.success(event.detail.mensaje, event.detail.titulo);
                break;
            case 'warning':
                toastr.warning(event.detail.mensaje, event.detail.titulo);
                break;
            case 'error':
                toastr.error(event.detail.mensaje, event.detail.titulo);
                break;
            case 'info':
                toastr.info(event.detail.mensaje, event.detail.titulo);
                break;
        }
    })
</script>
</html>
