<?php

namespace App\Exports;

use App\Models\EventMovement;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MovementsStatementExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function array(): array
    {
        $data = $this->prepareData();
        $formattedData = [];

        foreach ($data as $item) {
            $formattedData[] = [
                $item->date ? $item->date->format('d/m/Y') : '-',
                $item->event ? $item->event->name : '-',
                $item->description ?: '-',
                $item->type === 'Ingreso' ? number_format($item->amount, 0, '.', ',') : '-',
                $item->type === 'Egreso' ? number_format($item->amount, 0, '.', ',') : '-',
                $item->currency ? $item->currency->name : '-',
                $item->type === 'Ingreso' && $item->categoryIncome ? $item->categoryIncome->name : '-',
                $item->type === 'Egreso' && $item->categoryEgress ? $item->categoryEgress->name : '-',
                $item->club ? $item->club->name : '-',
                $item->supplier ? $item->supplier->name : '-',
                $item->methodPayment ? $item->methodPayment->account_holder : '-',
            ];
        }

        return $formattedData;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Evento',
            'Movimiento',
            'Ingreso',
            'Egreso',
            'Moneda',
            'Tipo Ingreso',
            'Tipo Egreso',
            'Club',
            'Proveedor',
            'Cuenta'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12, // Fecha
            'B' => 25, // Evento
            'C' => 30, // Movimiento
            'D' => 15, // Ingreso
            'E' => 15, // Egreso
            'F' => 12, // Moneda
            'G' => 20, // Tipo Ingreso
            'H' => 20, // Tipo Egreso
            'I' => 20, // Club
            'J' => 20, // Proveedor
            'K' => 25, // Cuenta
        ];
    }

    public function title(): string
    {
        return 'Estado General de Movimientos';
    }

    protected function prepareData()
    {
        return EventMovement::with([
            'event',
            'currency',
            'categoryIncome',
            'categoryEgress',
            'club',
            'supplier',
            'methodPayment.entity'
        ])
        ->where('status', '!=', 'Cancelado')
        ->whereNotNull('method_payment_id')
        ->when($this->request->filled('event_id'), function ($query) {
            $query->where('event_id', $this->request->get('event_id'));
        })
        ->when($this->request->filled('category_income_id'), function ($query) {
            $query->where('category_income_id', $this->request->get('category_income_id'));
        })
        ->when($this->request->filled('category_egress_id'), function ($query) {
            $query->where('category_egress_id', $this->request->get('category_egress_id'));
        })
        ->when($this->request->filled('start_date'), function ($query) {
            $query->where('date', '>=', $this->request->get('start_date'));
        })
        ->when($this->request->filled('end_date'), function ($query) {
            $query->where('date', '<=', $this->request->get('end_date'));
        })
        ->orderBy('date', 'desc')
        ->get();
    }
}
