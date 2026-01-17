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
        $this->fechaInicio = null;
        $this->fechaFin = null;
    }

    // Resetear página cuando cambia cualquier filtro
    public function updated($propertyName): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['buscar', 'usuarioFiltro', 'tipoAccionFiltro', 'fechaInicio', 'fechaFin']);
        $this->resetPage();
    }

    public function obtenerActividadesUsuarios()
    {
        // Validar que fechaInicio no sea mayor que fechaFin si ambas están seleccionadas
        if ($this->fechaInicio && $this->fechaFin && Carbon::parse($this->fechaInicio)->isAfter(Carbon::parse($this->fechaFin))) {
            $base = DB::table('ta_documento')
                ->select(
                    DB::raw("'documento' as tipo_tabla"),
                    'au_usuariocr as usuario_id',
                    'au_fechacr as fecha_accion',
                    DB::raw("'Crear Documento' as tipo_accion"),
                    DB::raw("CONCAT(COALESCE(numero_documento, 'S/N'), ' - ', COALESCE(asunto_documento, 'Sin asunto')) as descripcion")
                )
                ->whereRaw('1=0');

            return DB::query()->fromSub($base, 't')->paginate(50);
        }

        // Determinar si hay filtro de fechas
        $tieneFiltroFechas = !empty($this->fechaInicio) && !empty($this->fechaFin);

        if ($tieneFiltroFechas) {
            $desde = $this->fechaInicio . ' 00:00:00';
            $hasta = $this->fechaFin . ' 23:59:59';
        }

        // 1. Documentos Creados
        $docCreados = DB::table('ta_documento')
            ->select(
                DB::raw("'documento' as tipo_tabla"),
                'au_usuariocr as usuario_id',
                'au_fechacr as fecha_accion',
                DB::raw("'Crear Documento' as tipo_accion"),
                DB::raw("CONCAT(COALESCE(numero_documento, 'S/N'), ' - ', COALESCE(asunto_documento, 'Sin asunto')) as descripcion")
            )
            ->whereNotNull('au_usuariocr');

        if ($tieneFiltroFechas) {
            $docCreados->whereBetween('au_fechacr', [$desde, $hasta]);
        }

        // 2. Documentos Editados
        $docEditados = DB::table('ta_documento')
            ->select(
                DB::raw("'documento' as tipo_tabla"),
                'au_usuariomd as usuario_id',
                'au_fechamd as fecha_accion',
                DB::raw("'Editar Documento' as tipo_accion"),
                DB::raw("CONCAT(COALESCE(numero_documento, 'S/N'), ' - ', COALESCE(asunto_documento, 'Sin asunto')) as descripcion")
            )
            ->whereNotNull('au_usuariomd');

        if ($tieneFiltroFechas) {
            $docEditados->whereBetween('au_fechamd', [$desde, $hasta]);
        }

        // 3. Movimientos con acciones específicas
        $movimientos = DB::table('ta_movimiento')
            ->join('ta_documento', 'ta_movimiento.id_documento', '=', 'ta_documento.id_documento')
            ->join('ta_estado', 'ta_movimiento.id_estado', '=', 'ta_estado.id_estado')
            ->select(
                DB::raw("'movimiento' as tipo_tabla"),
                'ta_movimiento.au_usuariocr as usuario_id',
                'ta_movimiento.au_fechacr as fecha_accion',
                DB::raw("UPPER(ta_estado.nombre_estado) as tipo_accion"),
                DB::raw("CONCAT(UPPER(ta_estado.nombre_estado), ': ', COALESCE(ta_documento.numero_documento, 'S/N'), ' - ', COALESCE(ta_documento.asunto_documento, 'Sin asunto')) as descripcion")
            )
            ->whereNotNull('ta_movimiento.au_usuariocr');

        if ($tieneFiltroFechas) {
            $movimientos->whereBetween('ta_movimiento.au_fechacr', [$desde, $hasta]);
        }

        // Combinar todas las consultas
        $base = $docCreados->unionAll($docEditados)->unionAll($movimientos);

        $query = DB::query()->fromSub($base, 't');

        // Aplicar filtros adicionales
        if ($this->usuarioFiltro) {
            $query->where('usuario_id', $this->usuarioFiltro);
        }

        if ($this->tipoAccionFiltro) {
            $query->where('tipo_accion', 'like', "%{$this->tipoAccionFiltro}%");
        }

        if ($this->buscar) {
            $query->where('descripcion', 'like', "%{$this->buscar}%");
        }

        return $query->orderBy('fecha_accion', 'desc')->paginate(50);
    }

    // Estadísticas: Usuarios más activos
    public function usuariosMasActivos()
    {
        $query = DB::table('ta_usuario')
            ->select(
                'ta_usuario.id_usuario',
                'ta_usuario.nombre_usuario',
                'ta_persona.nombres_persona',
                'ta_persona.apellido_paterno_persona',
                'ta_persona.apellido_materno_persona'
            );

        // Si hay filtro de fechas, aplicarlo
        if ($this->fechaInicio && $this->fechaFin) {
            $desde = $this->fechaInicio . ' 00:00:00';
            $hasta = $this->fechaFin . ' 23:59:59';

            $query->selectRaw('((
                SELECT COUNT(*) FROM ta_documento
                WHERE ta_documento.au_usuariocr = ta_usuario.id_usuario
                AND ta_documento.au_fechacr BETWEEN ? AND ?
            ) + (
                SELECT COUNT(*) FROM ta_movimiento
                WHERE ta_movimiento.au_usuariocr = ta_usuario.id_usuario
                AND ta_movimiento.au_fechacr BETWEEN ? AND ?
            )) as total_acciones', [$desde, $hasta, $desde, $hasta]);
        } else {
            // Sin filtro de fechas: contar todas las acciones
            $query->selectRaw('((
                SELECT COUNT(*) FROM ta_documento
                WHERE ta_documento.au_usuariocr = ta_usuario.id_usuario
            ) + (
                SELECT COUNT(*) FROM ta_movimiento
                WHERE ta_movimiento.au_usuariocr = ta_usuario.id_usuario
            )) as total_acciones');
        }

        return $query
            ->leftJoin('ta_persona', 'ta_usuario.id_persona', '=', 'ta_persona.id_persona')
            ->having('total_acciones', '>', 0)
            ->orderByDesc('total_acciones')
            ->limit(5)
            ->get();
    }

    public function accionesPorTipo()
    {
        $docsQuery = DB::table('ta_documento')->whereNotNull('au_usuariocr');
        $movsQuery = DB::table('ta_movimiento')->whereNotNull('au_usuariocr');

        // Si hay filtro de fechas, aplicarlo
        if ($this->fechaInicio && $this->fechaFin) {
            $desde = $this->fechaInicio . ' 00:00:00';
            $hasta = $this->fechaFin . ' 23:59:59';

            $documentos = $docsQuery->whereBetween('au_fechacr', [$desde, $hasta])->count();
            $movimientos = $movsQuery->whereBetween('au_fechacr', [$desde, $hasta])->count();
        } else {
            // Sin filtro de fechas: contar todas
            $documentos = $docsQuery->count();
            $movimientos = $movsQuery->count();
        }

        return [
            ['tipo' => 'Documentos Creados', 'cantidad' => $documentos, 'color' => '#009ef7'],
            ['tipo' => 'Movimientos Creados', 'cantidad' => $movimientos, 'color' => '#7239ea'],
        ];
    }

    // Obtener tipos de acción únicos para el filtro
    public function tiposAccionFiltro()
    {
        $tiposMovimientos = DB::table('ta_estado')
            ->select('nombre_estado')
            ->distinct()
            ->pluck('nombre_estado')
            ->map(fn($estado) => strtoupper($estado))
            ->toArray();

        $tiposDocumentos = [
            'Crear Documento',
            'Editar Documento'
        ];

        return array_values(array_unique(array_merge($tiposDocumentos, $tiposMovimientos)));
    }

    public function totalUsuariosActivos()
    {
        $usuariosDocs = DB::table('ta_documento')->select('au_usuariocr');
        $usuariosMovs = DB::table('ta_movimiento')->select('au_usuariocr');

        // Si hay filtro de fechas, aplicarlo
        if ($this->fechaInicio && $this->fechaFin) {
            $desde = $this->fechaInicio . ' 00:00:00';
            $hasta = $this->fechaFin . ' 23:59:59';

            $usuariosDocs->whereBetween('au_fechacr', [$desde, $hasta]);
            $usuariosMovs->whereBetween('au_fechacr', [$desde, $hasta]);
        }

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
            'tiposAccion' => $this->tiposAccionFiltro(),
        ]);
    }
}
