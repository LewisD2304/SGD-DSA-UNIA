<?php

namespace App\Livewire\Components\Seguridad\Catalogo;

use App\Enums\EstadoEnum;
use App\Services\Configuracion\Catalogo\CatalogoService;
use App\Services\Seguridad\MenuService;
use Illuminate\Support\Facades\Gate;
use Exception;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Lazy()]
class ListaHijos extends Component
{
    use WithPagination;

    #[Reactive]
    public $id_padre;

    #[Url('mostrar', except: 10)]
    public $mostrar_paginate = 10;

    public $permisos = [];
    protected $paginationTheme = 'bootstrap';

    // Variables para el modal de registro, modificación y eliminación
    public $titulo_modal_estado = '';
    public $titulo_modal = '';
    public $modo_modal = 1; // 1: Registrar, 2: Modificar
    public $item_modificar = null;
    public $catalogo_eliminar = null;
    public $catalogo_estado = null;

    // Variables de los campos del formulario
    public $nombre_catalogo_eliminar = '';
    public $nombre_catalogo_estado = '';
    public $modo_catalogo_estado = 1; // 1: Habilitar, 2: Deshabilitar
    public $modo_catalogo = 1; // 1: Padre, 2: Hijo

    #[Validate('required|min:2|max:100', 'descripción larga')]
    public $descripcion_larga = '';
    #[Validate('required|min:2|max:8', 'descripción corta')]
    public $descripcion_corta = '';

    protected CatalogoService $catalogo_service;
    protected MenuService $menu_service;

    public function __construct()
    {
        $this->catalogo_service = resolve(CatalogoService::class);
        $this->menu_service = resolve(MenuService::class);
    }

    public function mount()
    {
        $acciones = $this->menu_service->listarAccionesPorNombreMenu('CATALOGO');

        if ($acciones) {
            foreach ($acciones->acciones as $accion) {
                $nombre_accion = strtoupper(trim($accion->tipoAccion->descripcion_catalogo));

                if ($nombre_accion === 'LISTAR') {
                    continue;
                }

                $this->permisos[$nombre_accion] = Gate::allows('autorizacion', [
                    $nombre_accion,
                    strtoupper(trim($acciones->nombre_menu)),
                ]);
            }
        }
    }

    public function render()
    {
        return view('livewire.components.seguridad.catalogo.lista-hijos');
    }

    #[Computed()]
    public function lista_hijos()
    {
        return $this->catalogo_service->listarHijos($this->id_padre, ['padre'], $this->mostrar_paginate, 'id_catalogo', 'desc');
    }

    #[On('resetear_paginacion')]
    public function resetear_paginacion()
    {
        $this->resetPage();
    }

    // Abrir modal para estado de un catalogo hijo
    public function cargar_modal_estado_hijo($id_catalogo)
    {
        $this->limpiar_modal();
        $this->modo_catalogo = 2; // 1: Padre, 2: Hijo

        $this->titulo_modal_estado = 'Estado de item catálogo';
        $this->catalogo_estado = $this->catalogo_service->obtenerPorId($id_catalogo);
        $this->nombre_catalogo_estado = $this->catalogo_estado->descripcion_catalogo;
        $this->modo_catalogo_estado = $this->catalogo_estado->estado_catalogo === EstadoEnum::HABILITADO ? 1 : 2;

        $this->modal_catalogo('#modal-estado-catalogo', 'show');
    }

    // Abrir modal para estado de un catalogo padre
    public function cargar_modal_estado_padre()
    {
        $this->limpiar_modal();

        $this->titulo_modal_estado = 'Estado de tabla catálogo';
        $this->catalogo_estado = $this->catalogo_service->obtenerPorId($this->id_padre);
        $this->nombre_catalogo_estado = $this->catalogo_estado->descripcion_catalogo;
        $this->modo_catalogo_estado = $this->catalogo_estado->estado_catalogo === EstadoEnum::HABILITADO ? 1 : 2;

        $this->dispatch('cargando', cargando: false);
        $this->modal_catalogo('#modal-estado-catalogo', 'show');
    }


    // Estado catalogo
    public function estado_catalogo()
    {
        $mensaje_t = null;

        try {

            switch ($this->modo_catalogo_estado) {
                case 1:
                    $this->catalogo_service->cambiarEstado($this->catalogo_estado, EstadoEnum::DESHABILITADO);
                    break;
                case 2:
                    $this->catalogo_service->cambiarEstado($this->catalogo_estado, EstadoEnum::HABILITADO);
                    break;
            }

            if ($this->modo_catalogo == 1) {
                $mensaje_t = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Tabla modificado correctamente', 'top', 'right');
                $this->dispatch('refrescar_tabla');
            } else {
                $mensaje_t = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Item modificado correctamente', 'top', 'right');
            }
        } catch (Exception $e) {
            // Emitir un evento para mostrar el Toastr con el mensaje de error
            $mensaje_t = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modal_catalogo('#modal-estado-catalogo', 'hide');
        $this->limpiar_modal();

        if ($mensaje_t != NULL) {
            $this->dispatch(
                'toastr',
                boton_cerrar: $mensaje_t['boton_cerrar'],
                progreso_avance: $mensaje_t['progreso_avance'],
                duracion: $mensaje_t['duracion'],
                titulo: $mensaje_t['titulo'],
                tipo: $mensaje_t['tipo'],
                mensaje: $mensaje_t['mensaje'],
                posicion_y: $mensaje_t['posicion_y'],
                posicion_x: $mensaje_t['posicion_x']
            );
        }
    }

    // Cerrar modal
    public function modal_catalogo($nombre, $accion)
    {
        $this->dispatch(
            'modal',
            nombre: $nombre,
            accion: $accion
        );
    }

    public function cargar_modal_item($id_item = null)
    {
        $this->limpiar_modal();

        if (!is_null($id_item)) {
            $this->titulo_modal = 'Modificar item tabla';
            $this->modo_modal = 2; // Modificar
            $this->item_modificar = $this->catalogo_service->obtenerPorId($id_item);
            $this->descripcion_larga = $this->item_modificar->descripcion_catalogo;
            $this->descripcion_corta = $this->item_modificar->abreviatura_catalogo;
            $this->validate();
        } else {
            $this->titulo_modal = 'Registrar nueva item tabla';
            $this->modo_modal = 1;
        }

        $this->dispatch('cargando', cargando: false);
        $this->modal_catalogo('#modal-item', 'show');
    }

    // Guardar una item de tabla nuevo o modificado
    public function guardar_item()
    {
        $mensaje_t = null;

        try {

            // Limpiar de cadena
            $this->descripcion_larga = limpiarCadena($this->descripcion_larga, false, false);
            $this->descripcion_corta = limpiarCadena($this->descripcion_corta, false, false);

            $this->validate([
                'descripcion_larga' => [
                    'required',
                    'min:2',
                    'max:100',
                    Rule::unique('ta_catalogo', 'descripcion_catalogo')
                        ->ignore($this->item_modificar ?? null, 'id_catalogo')
                        ->where('id_padre', $this->id_padre)
                        ->whereNull('au_fechael')
                ],
                'descripcion_corta' => [
                    'required',
                    'min:2',
                    'max:8',
                    Rule::unique('ta_catalogo', 'abreviatura_catalogo')
                        ->ignore($this->item_modificar ?? null, 'id_catalogo')
                        ->where('id_padre', $this->id_padre)
                        ->whereNull('au_fechael')
                ]
            ], [
                'descripcion_larga.unique' => 'La descripción larga del item ya está en uso.',
                'descripcion_corta.unique' => 'La descripción corta del item ya está en uso.'
            ]);

            if ($this->modo_modal == 1) { // Registrar
                $this->catalogo_service->registrar([
                    'id_padre' => $this->id_padre,
                    'descripcion_catalogo' => $this->descripcion_larga,
                    'abreviatura_catalogo' => $this->descripcion_corta,
                    'estado_catalogo' => 'HAB'
                ]);

                $mensaje_t = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Item registrado correctamente', 'top', 'right');
            } else { // Modificar
                $this->catalogo_service->modificar([
                    'descripcion_catalogo' => $this->descripcion_larga,
                    'abreviatura_catalogo' => $this->descripcion_corta
                ], $this->item_modificar);

                $mensaje_t = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Item modificado correctamente', 'top', 'right');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {

            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }

            $this->dispatch('errores_validacion', validacion: $this->getErrorBag()->messages());
            return;
        } catch (Exception $e) {

            // Emitir un evento para mostrar el Toastr con el mensaje de error
            $mensaje_t = mensajeToastr(false, true, '5000', 'Error', 'error', $e->getMessage(), 'top', 'right');
        }

        $this->modal_catalogo('#modal-item', 'hide');
        $this->limpiar_modal();

        if ($mensaje_t != NULL) {
            $this->dispatch(
                'toastr',
                boton_cerrar: $mensaje_t['boton_cerrar'],
                progreso_avance: $mensaje_t['progreso_avance'],
                duracion: $mensaje_t['duracion'],
                titulo: $mensaje_t['titulo'],
                tipo: $mensaje_t['tipo'],
                mensaje: $mensaje_t['mensaje'],
                posicion_y: $mensaje_t['posicion_y'],
                posicion_x: $mensaje_t['posicion_x']
            );
        }
    }

    public function limpiar_modal()
    {
        $this->titulo_modal = '';
        $this->modo_modal = 1;
        $this->reset(['descripcion_larga', 'descripcion_corta', 'item_modificar', 'catalogo_eliminar', 'catalogo_estado', 'nombre_catalogo_eliminar', 'nombre_catalogo_estado', 'modo_catalogo_estado', 'modo_catalogo', 'modo_modal']);
        $this->resetErrorBag();
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="text-center text-muted py-6">
            <i class="ki-outline ki-information fs-2hx mb-2"></i>
            <div>Selecciona una tabla para ver sus ítems</div>
        </div>
        HTML;
    }
}
