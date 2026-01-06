<?php

namespace App\Livewire\Reportes;

use App\Models\Area;
use App\Models\Documento;
use App\Models\Estado;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Reporte de Documentos | GESTIÓN DOCUMENTAL')]
class Documentos extends Component
{
    use WithPagination;

    #[Url]
    public $fechaInicio = null;

    #[Url]
    public $fechaFin = null;

    #[Url]
    public $idEstado = null;

    #[Url]
    public $idArea = null;

    #[Url]
    public $tipoReporte = 'recibidos'; // recibidos, enviados, todos

    #[Url]
    public $buscar = '';

    public $estados = [];
    public $areas = [];
    public $idAreaUsuario = null;

    // Estadísticas del reporte
    public $totalDocumentos = 0;
    public $documentosPendientes = 0;
    public $documentosAtendidos = 0;

    public function mount()
    {
        $this->idAreaUsuario = Auth::user()->persona->id_area ?? null;

        // Cargar estados y áreas
        $this->estados = Estado::orderBy('nombre_estado')->get();
        $this->areas = Area::orderBy('nombre_area')->get();

        // Establecer fechas por defecto (último mes)
        if (!$this->fechaInicio) {
            $this->fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$this->fechaFin) {
            $this->fechaFin = Carbon::now()->format('Y-m-d');
        }
    }

    public function aplicarFiltro()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['fechaInicio', 'fechaFin', 'idEstado', 'idArea', 'buscar', 'tipoReporte']);
        $this->tipoReporte = 'recibidos';
        $this->fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fechaFin = Carbon::now()->format('Y-m-d');
        $this->resetPage();
    }

    public function exportarPDF()
    {
        try {
            $documentos = $this->getBaseQuery()
                ->with(['areaRemitente', 'areaDestino', 'estado', 'tipoDocumento'])
                ->withCount('archivos')
                ->orderBy('au_fechacr', 'desc')
                ->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.documentos-pdf', [
                'documentos' => $documentos,
                'fechaInicio' => $this->fechaInicio,
                'fechaFin' => $this->fechaFin,
                'tipoReporte' => $this->tipoReporte,
                'total' => $documentos->count()
            ])->setPaper('a4', 'landscape');

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, 'reporte_documentos_' . date('YmdHis') . '.pdf');

        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al generar PDF: ' . $e->getMessage()
            ]);
        }
    }

    public function exportarExcel()
    {
        try {
            $documentos = $this->getBaseQuery()
                ->with(['areaRemitente', 'areaDestino', 'estado', 'tipoDocumento'])
                ->withCount('archivos')
                ->orderBy('au_fechacr', 'desc')
                ->get();

            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\DocumentosExport($documentos),
                'reporte_documentos_' . date('YmdHis') . '.xlsx'
            );

        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al generar Excel: ' . $e->getMessage()
            ]);
        }
    }

    private function calcularEstadisticas()
    {
        $query = $this->getBaseQuery();

        $this->totalDocumentos = $query->count();

        $queryPendientes = clone $query;
        $this->documentosPendientes = $queryPendientes->whereHas('estado', function($q) {
            $q->where('nombre_estado', 'LIKE', '%PENDIENTE%');
        })->count();

        $this->documentosAtendidos = $this->totalDocumentos - $this->documentosPendientes;
    }

    private function getBaseQuery()
    {
        $query = Documento::query();

        // Filtro por tipo de reporte
        if ($this->tipoReporte === 'recibidos') {
            $query->where('id_area_destino', $this->idAreaUsuario);
        } elseif ($this->tipoReporte === 'enviados') {
            $query->where('id_area_remitente', $this->idAreaUsuario);
        } else {
            // todos
            $query->where(function($q) {
                $q->where('id_area_destino', $this->idAreaUsuario)
                  ->orWhere('id_area_remitente', $this->idAreaUsuario);
            });
        }

        // Filtro por fecha
        if ($this->fechaInicio && $this->fechaFin) {
            $query->whereBetween('au_fechacr', [
                $this->fechaInicio . ' 00:00:00',
                $this->fechaFin . ' 23:59:59'
            ]);
        }

        // Filtro por estado
        if ($this->idEstado) {
            $query->where('id_estado', $this->idEstado);
        }

        // Filtro por área
        if ($this->idArea) {
            $query->where(function($q) {
                $q->where('id_area_destino', $this->idArea)
                  ->orWhere('id_area_remitente', $this->idArea);
            });
        }

        // Búsqueda por texto
        if ($this->buscar) {
            $query->where(function($q) {
                $q->where('numero_documento', 'LIKE', '%' . $this->buscar . '%')
                  ->orWhere('asunto_documento', 'LIKE', '%' . $this->buscar . '%')
                  ->orWhere('remitente', 'LIKE', '%' . $this->buscar . '%');
            });
        }

        return $query;
    }

    public function render()
    {
        $this->calcularEstadisticas();

        $documentos = $this->getBaseQuery()
            ->with(['areaRemitente', 'areaDestino', 'estado', 'tipoDocumento'])
            ->withCount('archivos')
            ->orderBy('au_fechacr', 'desc')
            ->paginate(15);

        return view('livewire.reportes.Documentos', [
            'documentos' => $documentos
        ]);
    }
}
