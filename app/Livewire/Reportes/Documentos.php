<?php

namespace App\Livewire\Reportes;

use App\Models\Area;
use App\Models\Documento;
use App\Models\Estado;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Reporte de Documentos | GESTIÓN DOCUMENTAL')]
class Documentos extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $tipoReporte = 'todos';
    public ?string $fechaInicio = null;
    public ?string $fechaFin = null;
    public ?string $idEstado = null;
    public ?string $idArea = null;
    public ?string $buscar = null;

    public function mount(): void
    {
        $this->fechaInicio = '';
        $this->fechaFin = '';
    }

    public function updated($name): void
    {
        if (in_array($name, ['tipoReporte', 'fechaInicio', 'fechaFin', 'idEstado', 'idArea', 'buscar'], true)) {
            $this->resetPage();
        }
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['tipoReporte', 'fechaInicio', 'fechaFin', 'idEstado', 'idArea', 'buscar']);
        $this->tipoReporte = 'todos';
        $this->resetPage();
    }

    public function exportarPDF()
    {
        $documentos = $this->baseQuery()->get();
        $total = $documentos->count();

        $pdf = Pdf::loadView('reportes.documentos-pdf', [
            'documentos' => $documentos,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'tipoReporte' => $this->tipoReporte,
            'total' => $total,
        ])->setPaper('a4', 'landscape');

        $filename = 'reporte_documentos_' . Carbon::now()->format('YmdHis') . '.pdf';

        return response()->streamDownload(fn () => print($pdf->output()), $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function render()
    {
        $baseQuery = $this->baseQuery();

        $documentos = (clone $baseQuery)->paginate(10);
        $totalDocumentos = (clone $baseQuery)->count();
        $documentosPendientes = (clone $baseQuery)
            ->whereHas('estado', fn ($q) => $q->where('nombre_estado', 'like', '%PENDIENTE%'))
            ->count();
        $documentosAtendidos = (clone $baseQuery)
            ->whereHas('estado', fn ($q) => $q->where(function ($query) {
                $query->where('nombre_estado', 'like', '%RECEPCIONADO%')
                    ->orWhere('nombre_estado', 'like', '%FINALIZADO%');
            }))
            ->count();

        $estados = Estado::orderBy('nombre_estado')->get();
        $areas = Area::orderBy('nombre_area')->get();

        return view('livewire.reportes.documentos', [
            'documentos' => $documentos,
            'estados' => $estados,
            'areas' => $areas,
            'totalDocumentos' => $totalDocumentos,
            'documentosPendientes' => $documentosPendientes,
            'documentosAtendidos' => $documentosAtendidos,
        ]);
    }

    private function baseQuery()
    {
        [$inicio, $fin] = $this->dateRange();

        $query = Documento::with([
            'tipoDocumento',
            'areaRemitente',
            'areaDestino',
            'estado',
        ])->withCount('archivos')
            ->whereBetween('au_fechacr', [$inicio, $fin])
            ->when($this->buscar, fn ($q) => $q->buscar($this->buscar))
            ->when($this->idEstado, fn ($q) => $q->where('id_estado', $this->idEstado));

        if ($this->idArea) {
            $query->where(function ($q) {
                $q->where('id_area_remitente', $this->idArea)
                    ->orWhere('id_area_destino', $this->idArea);
            });
        }

        if ($this->tipoReporte === 'recibidos') {
            $query->whereNotNull('fecha_recepcion_documento');
        } elseif ($this->tipoReporte === 'enviados') {
            $query->whereNotNull('fecha_despacho_documento');
        }

        return $query->orderByDesc('au_fechacr');
    }

    private function dateRange(): array
    {
        $inicio = $this->fechaInicio
            ? Carbon::parse($this->fechaInicio)->startOfDay()
            : Carbon::parse('1900-01-01')->startOfDay(); // Rango muy amplio si no hay fecha inicio

        $fin = $this->fechaFin
            ? Carbon::parse($this->fechaFin)->endOfDay()
            : Carbon::now()->endOfDay(); // Hasta hoy si no hay fecha fin

        return [$inicio, $fin];
    }
}
