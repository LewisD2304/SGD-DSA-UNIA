<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use ParaTest\WrapperRunner\WorkerCrashedException;

class DocumentosExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $documentos;

    public function __construct($documentos)
    {
        $this->documentos = $documentos;
    }

    public function collection()
    {
        return $this->documentos;
    }

    public function headings(): array
    {
        return [
            'N° Documento',
            'Expediente',
            'Tipo Documento',
            'Asunto',
            'Remitente',
            'Área Origen',
            'Área Destino',
            'Estado',
            'Folios',
            'Archivos',
            'Fecha Creación',
        ];
    }

    public function map($documento): array
    {
        return [
            $documento->numero_documento,
            $documento->expediente_documento ?? 'S/N',
            $documento->tipoDocumento->descripcion_catalogo ?? 'N/A',
            $documento->asunto_documento ?? $documento->asunto,
            $documento->remitente ?? 'N/A',
            $documento->areaRemitente->nombre_area ?? 'Externo',
            $documento->areaDestino->nombre_area ?? 'Sin asignar',
            $documento->estado->nombre_estado ?? 'N/A',
            $documento->folio_documento ?? 0,
            $documento->archivos_count ?? 0,
            Carbon::parse($documento->au_fechacr)->format('d/m/Y H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }
}
