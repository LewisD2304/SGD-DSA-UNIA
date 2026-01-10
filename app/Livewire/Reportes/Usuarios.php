<?php

namespace App\Livewire\Reportes;

use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Reporte de Usuarios | GESTIÓN DOCUMENTAL')]
class Usuarios extends Component
{
    use WithPagination;

    // Filtros
    public $buscar = '';
    public $usuarioFiltro = '';
    public $tipoAccionFiltro = '';
    public $fechaInicio;
    public $fechaFin;

    protected $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        $this->fechaInicio = Carbon::now()->subDays(30)->format('Y-m-d');
        $this->fechaFin = Carbon::now()->format('Y-m-d');
    }

    // Resetear página cuando cambia cualquier filtro
    public function updated($propertyName): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['buscar', 'usuarioFiltro', 'tipoAccionFiltro']);
        $this->fechaInicio = Carbon::now()->subDays(30)->format('Y-m-d');
        $this->fechaFin = Carbon::now()->format('Y-m-d');
        $this->resetPage();
    }

    public function obtenerActividadesUsuarios()
    {
        $desde = $this->fechaInicio . ' 00:00:00';
        $hasta = $this->fechaFin . ' 23:59:59';

        // 1. Documentos Creados
        $base = DB::table('ta_documento')
            ->select(
                DB::raw("'documento' as tipo_tabla"),
                'au_usuariocr as usuario_id',
                'au_fechacr as fecha_accion',
                DB::raw("'Crear Documento' as tipo_accion"),
                // CORREGIDO: asunto_documento
                DB::raw("CONCAT(COALESCE(numero_documento, 'S/N'), ' - ', COALESCE(asunto_documento, 'Sin asunto')) as descripcion")
            )
            ->whereNotNull('au_usuariocr')
            ->whereBetween('au_fechacr', [$desde, $hasta])

            ->unionAll(
                // 2. Documentos Editados
                DB::table('ta_documento')
                    ->select(
                        DB::raw("'documento' as tipo_tabla"),
                        'au_usuariomd as usuario_id',
                        'au_fechamd as fecha_accion',
                        DB::raw("'Editar Documento' as tipo_accion"),
                        // CORREGIDO: asunto_documento
                        DB::raw("CONCAT(COALESCE(numero_documento, 'S/N'), ' - ', COALESCE(asunto_documento, 'Sin asunto')) as descripcion")
                    )
                    ->whereNotNull('au_usuariomd')
                    ->whereBetween('au_fechamd', [$desde, $hasta])
            )
            ->unionAll(
                // 3. Movimientos Creados
                DB::table('ta_movimiento')
                    ->join('ta_documento', 'ta_movimiento.id_documento', '=', 'ta_documento.id_documento')
                    ->select(
                        DB::raw("'movimiento' as tipo_tabla"),
                        'ta_movimiento.au_usuariocr as usuario_id',
                        'ta_movimiento.au_fechacr as fecha_accion',
                        DB::raw("'Crear Movimiento' as tipo_accion"),
                        // CORREGIDO: ta_documento.asunto_documento
                        DB::raw("CONCAT('Derivación: ', COALESCE(ta_documento.numero_documento, 'S/N'), ' - ', COALESCE(ta_documento.asunto_documento, 'Sin asunto')) as descripcion")
                    )
                    ->whereNotNull('ta_movimiento.au_usuariocr')
                    ->whereBetween('ta_movimiento.au_fechacr', [$desde, $hasta])
            )
            ->unionAll(
                // 4. Movimientos Editados
                DB::table('ta_movimiento')
                    ->join('ta_documento', 'ta_movimiento.id_documento', '=', 'ta_documento.id_documento')
                    ->select(
                        DB::raw("'movimiento' as tipo_tabla"),
                        'ta_movimiento.au_usuariomd as usuario_id',
                        'ta_movimiento.au_fechamd as fecha_accion',
                        DB::raw("'Editar Movimiento' as tipo_accion"),
                        // CORREGIDO: ta_documento.asunto_documento
                        DB::raw("CONCAT('Edición Derivación: ', COALESCE(ta_documento.numero_documento, 'S/N'), ' - ', COALESCE(ta_documento.asunto_documento, 'Sin asunto')) as descripcion")
                    )
                    ->whereNotNull('ta_movimiento.au_usuariomd')
                    ->whereBetween('ta_movimiento.au_fechamd', [$desde, $hasta])
            );

        $query = DB::query()->fromSub($base, 't');

        if ($this->usuarioFiltro) {
            $query->where('usuario_id', $this->usuarioFiltro);
        }

        if ($this->tipoAccionFiltro) {
            $query->where('tipo_accion', $this->tipoAccionFiltro);
        }

        if ($this->buscar) {
            $query->where('descripcion', 'like', "%{$this->buscar}%");
        }

        return $query->orderBy('fecha_accion', 'desc')->paginate(50);
    }

    // Estadísticas: Usuarios más activos
    public function usuariosMasActivos()
    {
        $desde = $this->fechaInicio . ' 00:00:00';
        $hasta = $this->fechaFin . ' 23:59:59';

        return DB::table('ta_usuario')
            ->select(
                'ta_usuario.id_usuario',
                'ta_usuario.nombre_usuario',
                'ta_persona.nombres_persona',
                'ta_persona.apellido_paterno_persona', // AGREGRADO
                'ta_persona.apellido_materno_persona'  // AGREGRADO
            )
            ->selectRaw('((
                SELECT COUNT(*) FROM ta_documento
                WHERE ta_documento.au_usuariocr = ta_usuario.id_usuario
                AND ta_documento.au_fechacr BETWEEN ? AND ?
            ) + (
                SELECT COUNT(*) FROM ta_movimiento
                WHERE ta_movimiento.au_usuariocr = ta_usuario.id_usuario
                AND ta_movimiento.au_fechacr BETWEEN ? AND ?
            )) as total_acciones', [$desde, $hasta, $desde, $hasta])
            ->leftJoin('ta_persona', 'ta_usuario.id_persona', '=', 'ta_persona.id_persona')
            ->having('total_acciones', '>', 0)
            ->orderByDesc('total_acciones')
            ->limit(5)
            ->get();
    }

    public function accionesPorTipo()
    {
        $desde = $this->fechaInicio . ' 00:00:00';
        $hasta = $this->fechaFin . ' 23:59:59';

        $documentos = DB::table('ta_documento')->whereBetween('au_fechacr', [$desde, $hasta])->whereNotNull('au_usuariocr')->count();
        $movimientos = DB::table('ta_movimiento')->whereBetween('au_fechacr', [$desde, $hasta])->whereNotNull('au_usuariocr')->count();

        return [
            ['tipo' => 'Documentos Creados', 'cantidad' => $documentos, 'color' => '#009ef7'],
            ['tipo' => 'Movimientos Creados', 'cantidad' => $movimientos, 'color' => '#7239ea'],
        ];
    }

    public function totalUsuariosActivos()
    {
        $desde = $this->fechaInicio . ' 00:00:00';
        $hasta = $this->fechaFin . ' 23:59:59';

        $usuariosDocs = DB::table('ta_documento')->whereBetween('au_fechacr', [$desde, $hasta])->select('au_usuariocr');
        $usuariosMovs = DB::table('ta_movimiento')->whereBetween('au_fechacr', [$desde, $hasta])->select('au_usuariocr');

        return $usuariosDocs->union($usuariosMovs)->distinct()->count('au_usuariocr');
    }

    public function render()
    {
        // MODIFICACIÓN: Hacemos Left Join para traer los datos de la persona asociada al usuario
        $listaUsuarios = Usuario::leftJoin('ta_persona', 'ta_usuario.id_persona', '=', 'ta_persona.id_persona')
            ->select(
                'ta_usuario.id_usuario',
                'ta_usuario.nombre_usuario',
                'ta_persona.nombres_persona',
                'ta_persona.apellido_paterno_persona',
                'ta_persona.apellido_materno_persona'
            )
            ->orderBy('ta_usuario.nombre_usuario')
            ->get();

        return view('livewire.reportes.usuarios', [
            'actividades' => $this->obtenerActividadesUsuarios(),
            'usuariosMasActivos' => $this->usuariosMasActivos(),
            'accionesPorTipo' => $this->accionesPorTipo(),
            'totalUsuariosActivos' => $this->totalUsuariosActivos(),
            'usuarios' => $listaUsuarios,
        ]);
    }
}
