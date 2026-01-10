<?php

namespace App\Livewire\Components\Seguridad\Catalogo;

use App\Services\Configuracion\Catalogo\CatalogoService;
use App\Services\Seguridad\MenuService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Exception;
use Livewire\Attributes\On;

#[Lazy()]
class ListaPadre extends Component
{
    #[Url('buscar', except: '')]
    public $buscar = '';

    #[Url('tabla', except: '')]
    public $tabla = '';

    public $permisos_padre = [];

    // Variables para el modal de registro, modificación y eliminación
    public $titulo_modal = 'Registrar nueva tabla';
    public $modo_modal = 1; // 1: Registrar, 2: Modificar
    public $tabla_modificar = null;
    public $tabla_eliminar = null;
    public $tabla_estado = null;

    #[Validate('required|min:2|max:100' , 'nombre de tabla')]
    public $nombre_tabla = '';
    #[Validate('required|min:2|max:8' , 'abreviatura')]
    public $nombre_corto='';

    protected CatalogoService $catalogo_service;
    protected MenuService $menu_service;
    protected $paginationTheme = 'bootstrap';

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
                $nombre_accion = $accion->tipoAccion->descripcion_catalogo;

                if ($nombre_accion !== 'LISTAR') {
                    $this->permisos_padre[$nombre_accion] = Gate::allows('autorizacion', [$nombre_accion,
                    $acciones->nombre_menu,
                ]);
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.components.seguridad.catalogo.lista-padre');
    }

    #[Computed()]
    #[On('refrescar_tabla')]
    public function catalogos()
    {
        return $this->catalogo_service->listarPadre([], $this->buscar, 'id_catalogo', 'desc');
    }

    #[On('refrescar_tabla')]
    public function cargando_padre()
    {
        $this->dispatch('cargando_padre', cargando: false);
    }

    // Abrir modal para registrar o modificar una tabla
    #[On('cargar_modal_tabla')]
    public function cargar_modal_tabla($id_padre = null)
    {
        $this->limpiar_modal();

        if (!is_null($id_padre)) {
            $this->titulo_modal = 'Modificar tabla';
            $this->modo_modal = 2; // Modificar
            $this->tabla_modificar = $this->catalogo_service->obtenerPorId($id_padre);
            $this->nombre_tabla = $this->tabla_modificar->descripcion_catalogo;
            $this->nombre_corto = $this->tabla_modificar->abreviatura_catalogo;
            $this->validate();
        } else {
            $this->titulo_modal = 'Registrar nueva tabla';
            $this->modo_modal = 1;
        }

        $this->dispatch('cargando', cargando: false);
        $this->modal_tabla('#modal-tabla', 'show');
    }

    // Guardar una tabla nuevo o modificado
    public function guardar_tabla()
    {
        $mensaje_t = null;

        try {

            // Limpiar de cadena
            $this->nombre_tabla = limpiarCadena($this->nombre_tabla, false, false);
            $this->nombre_corto = limpiarCadena($this->nombre_corto, false, false);

            $this->validate([
                'nombre_tabla' => [
                    'required',
                    'min:2',
                    'max:100',
                    Rule::unique('ta_catalogo', 'descripcion_catalogo')
                        ->ignore($this->tabla_modificar ?? null, 'id_catalogo')
                        ->whereNull('id_padre')
                        ->whereNull('au_fechael'),

                ],
                'nombre_corto' => [
                    'required',
                    'min:2',
                    'max:8',
                    Rule::unique('ta_catalogo', 'abreviatura_catalogo')
                        ->ignore($this->tabla_modificar ?? null, 'id_catalogo')
                        ->whereNull('id_padre')
                        ->whereNull('au_fechael'),
                ]
            ], [
                'nombre_tabla.unique' => 'El nombre de la tabla ya está en uso.',
                'nombre_corto.unique' => 'La abreviatura ya está en uso.'
            ]);

            if ($this->modo_modal == 1) { // Registrar
                $this->catalogo_service->registrar([
                    'descripcion_catalogo' => $this->nombre_tabla,
                    'abreviatura_catalogo' => $this->nombre_corto,
                    'estado_catalogo' => 'HAB'
                ]);

                $mensaje_t = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Tabla registrada correctamente', 'top', 'right');
            }
            else { // Modificar
                $this->catalogo_service->modificar([
                    'descripcion_catalogo' => $this->nombre_tabla,
                    'abreviatura_catalogo' => $this->nombre_corto,
                ], $this->tabla_modificar);

                $mensaje_t = mensajeToastr(false, true, '3000', 'Éxito', 'success', 'Tabla modificada correctamente', 'top', 'right');
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

        $this->modal_tabla('#modal-tabla', 'hide');
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

    // Acción para abrir o cerrar el modal
    public function modal_tabla($nombre, $accion)
    {
        $this->dispatch(
            'modal',
            nombre: $nombre,
            accion: $accion
        );
    }

    public function limpiar_modal()
    {
        $this->reset(['nombre_tabla','nombre_corto', 'titulo_modal', 'modo_modal', 'tabla_modificar', 'tabla_eliminar', 'tabla_estado']);
        $this->resetValidation();
        $this->resetErrorBag();
    }

    // Seleccionar una tabla para mostrar sus hijos
    public function seleccionar_tabla($id_padre)
    {
        $this->dispatch('resetear_paginacion');
        $this->tabla = $id_padre;
        $this->dispatch('obtener_id_padre', id_padre: $id_padre);
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="card">
            <div class="card-body">
                <div class="text-center">
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </div>
            </div>
        </div>
        HTML;
    }
}
