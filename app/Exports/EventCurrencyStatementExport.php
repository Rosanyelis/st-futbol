<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;
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

class EventCurrencyStatementExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $request;
    protected $events;
    protected $monedas;
    protected $categorias;
    protected $categoriasEgreso;
    protected $totales;
    protected $totalesEgreso;

    public function __construct($request)
    {
        $this->request = $request;
        $this->events = Event::all();
        $this->monedas = Currency::all();
        $this->categorias = DB::table('category_incomes')->select('id', 'name')->get();
        $this->categoriasEgreso = DB::table('category_egresses')->select('id', 'name')->get();
        
        $this->prepareData();
    }

    protected function prepareData()
    {
        // Totales de ingresos - Consulta corregida
        $this->totales = DB::table('event_movements as em')
            ->join('currencies as c', 'em.currency_id', '=', 'c.id')
            ->leftJoin('category_incomes as ci', 'em.category_income_id', '=', 'ci.id')
            ->where('em.type', 'Ingreso')
            ->where('em.status', 'Activo')
            ->when($this->request->filled('event_id'), function($query) {
                return $query->where('em.event_id', $this->request->get('event_id'));
            })
            ->when($this->request->filled('start_date'), function($query) {
                return $query->where('em.date', '>=', $this->request->get('start_date'));
            })
            ->when($this->request->filled('end_date'), function($query) {
                return $query->where('em.date', '<=', $this->request->get('end_date'));
            })
            ->select(
                DB::raw('COALESCE(ci.name, "Sin categoría") as categoria'),
                'c.name as moneda',
                DB::raw('SUM(em.amount) as total')
            )
            ->groupBy('categoria', 'c.name')
            ->orderBy('categoria')
            ->orderBy('c.name')
            ->get();

        // Totales de egresos - Consulta corregida
        $this->totalesEgreso = DB::table('event_movements as em')
            ->join('currencies as c', 'em.currency_id', '=', 'c.id')
            ->leftJoin('category_egresses as ce', 'em.category_egress_id', '=', 'ce.id')
            ->where('em.type', 'Egreso')
            ->where('em.status', 'Activo')
            ->when($this->request->filled('event_id'), function($query) {
                return $query->where('em.event_id', $this->request->get('event_id'));
            })
            ->when($this->request->filled('start_date'), function($query) {
                return $query->where('em.date', '>=', $this->request->get('start_date'));
            })
            ->when($this->request->filled('end_date'), function($query) {
                return $query->where('em.date', '<=', $this->request->get('end_date'));
            })
            ->select(
                DB::raw('COALESCE(ce.name, "Sin categoría") as categoria'),
                'c.name as moneda',
                DB::raw('SUM(em.amount) as total')
            )
            ->groupBy('categoria', 'c.name')
            ->orderBy('categoria')
            ->orderBy('c.name')
            ->get();
    }

    public function array(): array
    {
        $data = [];
        
        // Título del reporte
        $data[] = ['ESTADO DE RESULTADOS POR EVENTO Y POR MONEDA'];
        $data[] = [];
        
        // Información del filtro
        if ($this->request->filled('event_id')) {
            $event = $this->events->find($this->request->get('event_id'));
            $data[] = ['Evento:', $event ? $event->name . ' - ' . $event->year : 'N/A'];
        }
        
        if ($this->request->filled('start_date') || $this->request->filled('end_date')) {
            $fechaInicio = $this->request->get('start_date') ? date('d/m/Y', strtotime($this->request->get('start_date'))) : 'N/A';
            $fechaFin = $this->request->get('end_date') ? date('d/m/Y', strtotime($this->request->get('end_date'))) : 'N/A';
            $data[] = ['Período:', $fechaInicio . ' - ' . $fechaFin];
        }
        
        $data[] = [];
        
        // Encabezados de monedas
        $headers = ['Concepto'];
        foreach ($this->monedas as $moneda) {
            $headers[] = strtoupper($moneda->name);
        }
        $data[] = $headers;
        
        // Sección de INGRESOS
        $data[] = ['INGRESOS'];
        
        foreach ($this->categorias as $categoria) {
            $row = [$categoria->name];
            foreach ($this->monedas as $moneda) {
                $total = $this->totales->first(function($t) use ($categoria, $moneda) {
                    return $t->categoria === $categoria->name && $t->moneda === $moneda->name;
                });
                $row[] = $total ? number_format($total->total, 0, '.', ',') : '0';
            }
            $data[] = $row;
        }
        
        // Total Ingresos
        $totalIngresosRow = ['TOTAL INGRESOS'];
        foreach ($this->monedas as $moneda) {
            $total = $this->totales->where('moneda', $moneda->name)->sum('total');
            $totalIngresosRow[] = number_format($total, 0, '.', ',');
        }
        $data[] = $totalIngresosRow;
        
        $data[] = [];
        
        // Sección de EGRESOS
        $data[] = ['EGRESOS'];
        
        foreach ($this->categoriasEgreso as $categoria) {
            $row = [$categoria->name];
            foreach ($this->monedas as $moneda) {
                $total = $this->totalesEgreso->first(function($t) use ($categoria, $moneda) {
                    return $t->categoria === $categoria->name && $t->moneda === $moneda->name;
                });
                $row[] = $total ? number_format($total->total, 0, '.', ',') : '0';
            }
            $data[] = $row;
        }
        
        // Total Egresos
        $totalEgresosRow = ['TOTAL EGRESOS'];
        foreach ($this->monedas as $moneda) {
            $total = $this->totalesEgreso->where('moneda', $moneda->name)->sum('total');
            $totalEgresosRow[] = number_format($total, 0, '.', ',');
        }
        $data[] = $totalEgresosRow;
        
        $data[] = [];
        
        // RESULTADO
        $resultadoRow = ['RESULTADO'];
        foreach ($this->monedas as $moneda) {
            $ingresos = $this->totales->where('moneda', $moneda->name)->sum('total');
            $egresos = $this->totalesEgreso->where('moneda', $moneda->name)->sum('total');
            $resultado = $ingresos - $egresos;
            $resultadoRow[] = number_format($resultado, 0, '.', ',');
        }
        $data[] = $resultadoRow;
        
        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        
        // Estilo para el título principal
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Estilo para los encabezados de sección
        $sheet->getStyle('A1:A' . $lastRow)->getFont()->setBold(true);
        
        // Estilo para los totales
        $sheet->getStyle('A1:A' . $lastRow)->getFont()->setBold(true);
        
        // Colorear las filas de totales
        foreach (range(1, $lastRow) as $row) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            if (str_contains(strtoupper($cellValue), 'TOTAL') || str_contains(strtoupper($cellValue), 'RESULTADO')) {
                $sheet->getStyle('A' . $row . ':' . $sheet->getHighestColumn() . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E3F2FD');
                $sheet->getStyle('A' . $row . ':' . $sheet->getHighestColumn() . $row)->getFont()->setBold(true);
            }
        }
        
        // Estilo para los encabezados de monedas
        $headerRow = 0;
        foreach (range(1, $lastRow) as $row) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            if (str_contains(strtoupper($cellValue), 'CONCEPTO')) {
                $headerRow = $row;
                break;
            }
        }
        
        if ($headerRow > 0) {
            $sheet->getStyle('A' . $headerRow . ':' . $sheet->getHighestColumn() . $headerRow)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2196F3');
            $sheet->getStyle('A' . $headerRow . ':' . $sheet->getHighestColumn() . $headerRow)->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        }
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 30];
        $columns = ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        
        foreach ($columns as $column) {
            $widths[$column] = 15;
        }
        
        return $widths;
    }

    public function title(): string
    {
        return 'Estado de Resultados';
    }
}
