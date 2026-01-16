<?php

namespace App\Livewire\Reportes;

use App\Models\Catalogo;
use App\Models\Documento;
use App\Models\Movimiento;
use App\Services\Documento\DocumentoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Estadísticas | GESTIÓN DOCUMENTAL')]
class Estadisticas extends Component
{
    public $totalDocumentos = 0;
    public $documentosPendientes = 0;
    public $documentosRecepcionados = 0;
    public $documentosEnTramite = 0;
    public $documentosArchivados = 0;
    public $documentosObservados = 0;
    public $filtroTiempo = 'anio'; // hoy, semana, mes, anio, personalizado
    public $fechaInicio = null;
    public $fechaFin = null;
    public $idArea = null;

    // Datos para gráficos
    public $datosEstados = ['labels' => [], 'valores' => []];
    public $datosTendencia = ['labels' => [], 'valores' => []];
    public $datosTiposDocumento = ['labels' => [], 'valores' => []];
    public $datosTiempoRespuesta = ['labels' => [], 'valores' => []];
    public $porcentajeCambioMesAnterior = 0;

    protected DocumentoService $documentoService;

    public function boot()
    {
        $this->documentoService = resolve(DocumentoService::class);
    }

    public function mount()
    {
        $this->idArea = Auth::user()->persona->id_area ?? null;
        $this->calcularRangoFechas();
        $this->cargarEstadisticas();
    }

    public function cambiarFiltro($filtro)
    {
        $this->filtroTiempo = $filtro;
        $this->calcularRangoFechas();
        $this->cargarEstadisticas();

        // IMPORTANTE: Despachar evento para actualizar gráficos en el frontend
        $this->dispatch('actualizar-graficos', [
            'estados' => $this->datosEstados,
            'tendencia' => $this->datosTendencia,
            'tipos' => $this->datosTiposDocumento
        ]);
    }

    private function calcularRangoFechas()
    {
        $ahora = Carbon::now();
        switch ($this->filtroTiempo) {
            case 'hoy':
                $this->fechaInicio = $ahora->copy()->startOfDay();
                $this->fechaFin = $ahora->copy()->endOfDay();
                break;
            case 'semana':
                $this->fechaInicio = $ahora->copy()->startOfWeek();
                $this->fechaFin = $ahora->copy()->endOfWeek();
                break;
            case 'mes':
                $this->fechaInicio = $ahora->copy()->startOfMonth();
                $this->fechaFin = $ahora->copy()->endOfMonth();
                break;
            case 'anio':
                $this->fechaInicio = $ahora->copy()->startOfYear();
                $this->fechaFin = $ahora->copy()->endOfYear();
                break;
            default: // Personalizado
                // Si es personalizado, asumimos que fechas vienen de inputs (no implementado aquí, pero preparado)
                if (!$this->fechaInicio) $this->fechaInicio = $ahora->copy()->startOfYear();
                if (!$this->fechaFin) $this->fechaFin = $ahora->copy()->endOfYear();
        }
    }

    public function cargarEstadisticas()
    {
        if (!$this->idArea) return;

        // Estadísticas básicas
        $query = Documento::where(function ($q) {
            $q->where('id_area_remitente', $this->idArea)
                ->orWhere('id_area_destino', $this->idArea);
        });

        if ($this->fechaInicio && $this->fechaFin) {
            $query->whereBetween('au_fechacr', [$this->fechaInicio, $this->fechaFin]);
        }

        $this->totalDocumentos = $query->count();

        $this->documentosPendientes = $this->contarPorEstado('PENDIENTE');
        $this->documentosRecepcionados = $this->contarPorEstado('RECEPCIONADO');
        $this->documentosEnTramite = $this->contarPorEstado('EN TRAMITE'); // OJO: Verifica si en BD es "EN TRAMITE" o "DERIVADO"
        $this->documentosArchivados = $this->contarPorEstado('ARCHIVADO');
        $this->documentosObservados = $this->contarPorEstado('OBSERVADO'); // Corregido nombre estado

        $this->calcularPorcentajeCambio();

        $this->cargarDatosEstados();
        $this->cargarDatosTendencia();
        $this->cargarDatosTiposDocumento();
        $this->cargarDatosTiempoRespuesta();
    }

    private function contarPorEstado($nombreEstado)
    {
        $query = Documento::where(function ($q) {
            $q->where('id_area_remitente', $this->idArea)
                ->orWhere('id_area_destino', $this->idArea);
        })->whereHas('estado', function ($q) use ($nombreEstado) {
            $q->where('nombre_estado', 'LIKE', '%' . $nombreEstado . '%');
        });

        if ($this->fechaInicio && $this->fechaFin) {
            $query->whereBetween('au_fechacr', [$this->fechaInicio, $this->fechaFin]);
        }
        return $query->count();
    }

    private function calcularPorcentajeCambio()
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $inicioMesAnt = Carbon::now()->subMonth()->startOfMonth();
        $finMesAnt = Carbon::now()->subMonth()->endOfMonth();

        $totalMesActual = $this->totalEnRango($inicioMes, Carbon::now());
        $totalMesAnterior = $this->totalEnRango($inicioMesAnt, $finMesAnt);

        if ($totalMesAnterior > 0) {
            $this->porcentajeCambioMesAnterior = round((($totalMesActual - $totalMesAnterior) / $totalMesAnterior) * 100, 1);
        } else {
            $this->porcentajeCambioMesAnterior = $totalMesActual > 0 ? 100 : 0;
        }
    }

    private function totalEnRango($inicio, $fin)
    {
        return Documento::where(function ($q) {
            $q->where('id_area_remitente', $this->idArea)
                ->orWhere('id_area_destino', $this->idArea);
        })->whereBetween('au_fechacr', [$inicio, $fin])->count();
    }

    private function cargarDatosEstados()
    {
        // Se asegura que los datos sean consistentes para el gráfico
        $labels = ['Archivados', 'Recepcionados', 'Pendientes', 'Observados', 'En Trámite'];
        $valores = [
            $this->documentosArchivados,
            $this->documentosRecepcionados,
            $this->documentosPendientes,
            $this->documentosObservados,
            $this->documentosEnTramite
        ];

        $this->datosEstados = ['labels' => $labels, 'valores' => $valores];
    }

    private function cargarDatosTendencia()
    {
        // ... (Tu lógica de tendencia mensual está bien) ...
        $inicio = $this->filtroTiempo === 'anio' ? Carbon::now()->startOfYear() : Carbon::now()->subMonths(11)->startOfMonth();

        $documentos = Documento::where(function ($q) {
            $q->where('id_area_remitente', $this->idArea)
                ->orWhere('id_area_destino', $this->idArea);
        })
            ->where('au_fechacr', '>=', $inicio)
            ->selectRaw('MONTH(au_fechacr) as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        $meses = [];
        $valores = [];
        // Generar etiquetas
        for ($i = 1; $i <= 12; $i++) {
            $meses[] = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'][$i - 1];
            $valores[] = $documentos[$i] ?? 0;
        }

        $this->datosTendencia = ['labels' => $meses, 'valores' => $valores];
    }

    private function cargarDatosTiposDocumento()
    {
        $tipos = Documento::where(function ($q) {
            $q->where('ta_documento.id_area_remitente', $this->idArea)
                ->orWhere('ta_documento.id_area_destino', $this->idArea);
        })
            ->when($this->fechaInicio && $this->fechaFin, function ($q) {
                // CORRECCIÓN AQUÍ: Se agrega 'ta_documento.' antes de 'au_fechacr'
                $q->whereBetween('ta_documento.au_fechacr', [$this->fechaInicio, $this->fechaFin]);
            })
            ->join('ta_catalogo', 'ta_documento.tipo_documento_catalogo', '=', 'ta_catalogo.id_catalogo')
            ->selectRaw('ta_catalogo.descripcion_catalogo, COUNT(*) as total')
            ->groupBy('ta_catalogo.descripcion_catalogo')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $this->datosTiposDocumento = [
            'labels' => $tipos->pluck('descripcion_catalogo')->toArray(),
            'valores' => $tipos->pluck('total')->toArray()
        ];
    }

    private function cargarDatosTiempoRespuesta()
    {
        $tiempos = Movimiento::join('ta_documento', 'ta_movimiento.id_documento', '=', 'ta_documento.id_documento')
            ->join('ta_area as area_origen', 'ta_movimiento.id_area_origen', '=', 'area_origen.id_area')
            ->whereNotNull('ta_movimiento.fecha_recepcion')
            ->where(function($q) {
                // Considerar movimientos donde el área actual es origen o destino
                $q->where('ta_movimiento.id_area_origen', $this->idArea)
                  ->orWhere('ta_movimiento.id_area_destino', $this->idArea);
            })
            ->when($this->fechaInicio && $this->fechaFin, function ($q) {
                $q->whereBetween('ta_movimiento.au_fechacr', [$this->fechaInicio, $this->fechaFin]);
            })
            ->selectRaw('area_origen.nombre_area, AVG(TIMESTAMPDIFF(HOUR, ta_movimiento.au_fechacr, ta_movimiento.fecha_recepcion) / 24) as promedio_dias')
            ->groupBy('area_origen.nombre_area')
            ->havingRaw('promedio_dias > 0')
            ->orderByDesc('promedio_dias')
            ->limit(5)
            ->get();

        if ($tiempos->isEmpty()) {
            $this->datosTiempoRespuesta = ['areas' => [], 'valores' => []];
            return;
        }

        $areas = [];
        $valores = [];
        $maxDias = $tiempos->max('promedio_dias') ?: 1;

        foreach ($tiempos as $tiempo) {
            $areas[] = $tiempo->nombre_area;
            $dias = max(0, round($tiempo->promedio_dias, 1));
            $valores[] = [
                'dias' => $dias,
                'porcentaje' => min(($dias / $maxDias) * 100, 100)
            ];
        }

        $this->datosTiempoRespuesta = ['areas' => $areas, 'valores' => $valores];
    }

    public function render()
    {
        return view('livewire.reportes.estadisticas');
    }
}
