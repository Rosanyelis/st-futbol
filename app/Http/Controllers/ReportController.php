<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Event;
use App\Models\Currency;
use App\Models\ClubPayment;
use App\Models\AccountReceivable;
use Illuminate\Http\Request;
use App\Models\EventMovement;
use App\Models\MethodPayment;
use App\Models\CategoryIncome;
use App\Models\CategoryEgress;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Exports\EventCurrencyStatementExport;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     public function incomeStatement(Request $request)
    {
        if ($request->ajax()) {
            $data = EventMovement::with('categoryIncome', 'currency', 
                        'methodPayment', 'methodPayment.entity',
                         'methodPayment.categoryMethodPayment', 'club',
                         'accountReceivable')
                            ->where('type', 'Ingreso');

            return DataTables::of($data)
                ->filter(function ($query) use ($request) {
                    // Filtro por moneda desde el selector
                    if ($request->filled('currency_id')) {
                        $query->where('currency_id', $request->get('currency_id'));
                    }

                    if ($request->filled('start_date')) {
                        $query->where('date', '>=', $request->get('start_date'));
                    }
                    if ($request->filled('end_date')) {
                        $query->where('date', '<=', $request->get('end_date'));
                    }

                    // Búsqueda global
                    if ($request->has('search') && !empty($request->get('search')['value'])) {
                        $searchValue = $request->get('search')['value'];

                        $query->where(function ($subQuery) use ($searchValue) {
                            // Búsqueda en columnas directas de event_movements
                            $subQuery->where('date', 'like', "%{$searchValue}%")
                                     ->orWhere('type', 'like', "%{$searchValue}%")
                                     ->orWhere('amount', 'like', "%{$searchValue}%")
                                     ->orWhere('description', 'like', "%{$searchValue}%");

                            // Búsqueda en la relación 'currency'
                            $subQuery->orWhereHas('currency', function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%");
                            });

                            // Búsqueda en la relación 'club'
                            $subQuery->orWhereHas('club', function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%");
                            });

                            // Búsqueda en la relación 'methodPayment' y su anidada 'entity'
                            $subQuery->orWhereHas('methodPayment', function ($q) use ($searchValue) {
                                $q->where('account_holder', 'like', "%{$searchValue}%")
                                  ->orWhere('type_account', 'like', "%{$searchValue}%")
                                  ->orWhereHas('entity', function ($nested_q) use ($searchValue) {
                                      $nested_q->where('name', 'like', "%{$searchValue}%");
                                  });
                            });
                        });
                    }
                })
                ->make(true);
        }

        $paymentMethods = MethodPayment::all();
        // obtener los totales pendientes por moneda y las que no tengan datos declararlas en cero
        $currencies = Currency::all();
        return view('reports.income-report', compact('paymentMethods', 'currencies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function expenseStatement(Request $request)
    {
        if ($request->ajax()) {
            $data = EventMovement::with('categoryEgress', 'currency',  
                        'methodPayment', 'methodPayment.entity',
                         'methodPayment.categoryMethodPayment', 'supplier', 'expense',
                         'expense.categoryExpense')
                            ->where('type', 'Egreso');

            return DataTables::of($data)
                ->filter(function ($query) use ($request) {
                    // Filtro por moneda desde el selector
                    if ($request->filled('currency_id')) {
                        $query->where('currency_id', $request->get('currency_id'));
                    }

                    if ($request->filled('start_date')) {
                        $query->where('date', '>=', $request->get('start_date'));
                    }
                    if ($request->filled('end_date')) {
                        $query->where('date', '<=', $request->get('end_date'));
                    }

                    // Búsqueda global
                    if ($request->has('search') && !empty($request->get('search')['value'])) {
                        $searchValue = $request->get('search')['value'];

                        $query->where(function ($subQuery) use ($searchValue) {
                            // Búsqueda en columnas directas de event_movements
                            $subQuery->where('date', 'like', "%{$searchValue}%")
                                     ->orWhere('type', 'like', "%{$searchValue}%")
                                     ->orWhere('amount', 'like', "%{$searchValue}%")
                                     ->orWhere('description', 'like', "%{$searchValue}%");

                            // Búsqueda en la relación 'currency'
                            $subQuery->orWhereHas('currency', function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%");
                            });

                            // Búsqueda en la relación 'supplier'
                            $subQuery->orWhereHas('supplier', function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%");
                            });

                            // Búsqueda en la relación 'methodPayment' y su anidada 'entity'
                            $subQuery->orWhereHas('methodPayment', function ($q) use ($searchValue) {
                                $q->where('account_holder', 'like', "%{$searchValue}%")
                                  ->orWhere('type_account', 'like', "%{$searchValue}%")
                                  ->orWhereHas('entity', function ($nested_q) use ($searchValue) {
                                      $nested_q->where('name', 'like', "%{$searchValue}%");
                                  });
                            });
                        });
                    }
                })
                ->make(true);
        }

        $paymentMethods = MethodPayment::all();
        // obtener los totales pendientes por moneda y las que no tengan datos declararlas en cero
        $currencies = Currency::all();
        return view('reports.expense-report', compact('paymentMethods', 'currencies'));
    }

    public function listEvent()
    {
        $events = Event::orderBy('name', 'asc')->get();

        return response()->json($events);
    }

    public function listCategoryIncomes()
    {
        $categoryIncomes = CategoryIncome::orderBy('name', 'asc')->get();

        return response()->json($categoryIncomes);
    }

    public function listCategoryEgress()
    {
        $categoryEgress = CategoryEgress::orderBy('name', 'asc')->get();

        return response()->json($categoryEgress);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            \Log::info('Accounts Receivable Report AJAX request', [
                'filters' => $request->all()
            ]);
            
            $data = AccountReceivable::with(['club', 'event', 'currency', 'payments'])
                ->select('account_receivables.*');
                
            return DataTables::of($data)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && $request->search['value'] != '') {
                        $search = $request->search['value'];
                        $query->whereHas('club', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })->orWhereHas('event', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })->orWhereHas('currency', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    }

                    // Filtro por evento
                    if ($request->filled('event')) {
                        $query->where('event_id', $request->get('event'));
                    }

                    // Filtro por estado
                    if ($request->filled('status')) {
                        $query->where('status', $request->get('status'));
                    }
                })
                ->addColumn('event_name', function ($row) {
                    return $row->event->name ?? '';
                })
                ->addColumn('club_name', function ($row) {
                    return $row->club->name ?? '';
                })
                ->addColumn('currency_name', function ($row) {
                    return $row->currency->name ?? '';
                })
                ->addColumn('total_amount', function ($row) {
                    \Log::info('Total amount for row', [
                        'id' => $row->id,
                        'total_amount' => $row->total_amount,
                        'type' => gettype($row->total_amount)
                    ]);
                    return $row->total_amount;
                })
                ->addColumn('pending_amount', function ($row) {
                    $pending = $row->getPendingAmount();
                    \Log::info('Pending amount for row', [
                        'id' => $row->id,
                        'pending_amount' => $pending,
                        'type' => gettype($pending)
                    ]);
                    return $pending;
                })
                ->addColumn('pending_amount_raw', function ($row) {
                    return $row->getPendingAmount();
                })
                ->make(true);
                
            \Log::info('Accounts Receivable Report data sample', [
                'total_records' => $data->count(),
                'sample_data' => $data->take(2)->get()->map(function($item) {
                    return [
                        'id' => $item->id,
                        'total_amount' => $item->total_amount,
                        'pending_amount' => $item->getPendingAmount(),
                        'club_name' => $item->club->name ?? 'N/A',
                        'event_name' => $item->event->name ?? 'N/A'
                    ];
                })
            ]);
        }

        $paymentMethods = MethodPayment::all();
        $currencies = Currency::all();
        $events = Event::orderBy('name', 'asc')->get();
        $statuses = ['Pendiente', 'Parcial', 'Pagado', 'Vencido'];
        
        return view('reports.accounts-receivable', compact('paymentMethods', 'currencies', 'events', 'statuses'));
    }

    /**
     * Display the specified resource.
     */
    public function eventCurrencyStatement(Request $request)
    {
        // dd($request->all());
        $events = Event::all();
        $monedas = Currency::all();
        $categorias = DB::table('category_incomes')->select('id', 'name')->get();

        // Totales de ingresos - Consulta corregida
        $totales = DB::table('event_movements as em')
            ->join('currencies as c', 'em.currency_id', '=', 'c.id')
            ->leftJoin('category_incomes as ci', 'em.category_income_id', '=', 'ci.id')
            ->where('em.type', 'Ingreso')
            ->where('em.status', 'Activo')
            ->when($request->filled('event_id'), function($query) use ($request) {
                return $query->where('em.event_id', $request->get('event_id'));
            })
            ->when($request->filled('start_date'), function($query) use ($request) {
                return $query->where('em.date', '>=', $request->get('start_date'));
            })
            ->when($request->filled('end_date'), function($query) use ($request) {
                return $query->where('em.date', '<=', $request->get('end_date'));
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
        $categoriasEgreso = DB::table('category_egresses')->select('id', 'name')->get();
        $totalesEgreso = DB::table('event_movements as em')
            ->join('currencies as c', 'em.currency_id', '=', 'c.id')
            ->leftJoin('category_egresses as ce', 'em.category_egress_id', '=', 'ce.id')
            ->where('em.type', 'Egreso')
            ->where('em.status', 'Activo')
            ->when($request->filled('event_id'), function($query) use ($request) {
                return $query->where('em.event_id', $request->get('event_id'));
            })
            ->when($request->filled('start_date'), function($query) use ($request) {
                return $query->where('em.date', '>=', $request->get('start_date'));
            })
            ->when($request->filled('end_date'), function($query) use ($request) {
                return $query->where('em.date', '<=', $request->get('end_date'));
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

        return view('reports.event-currency-statement', compact(
            'events', 'totales', 'monedas', 'categorias',
            'categoriasEgreso', 'totalesEgreso'
        ));
    }

    /**
     * Export the event currency statement to PDF.
     */
    public function eventCurrencyStatementPdf(Request $request)
    {
        $events = Event::all();
        $monedas = Currency::all();
        $categorias = DB::table('category_incomes')->select('id', 'name')->get();

        // Totales de ingresos - Consulta corregida
        $totales = DB::table('event_movements as em')
            ->join('currencies as c', 'em.currency_id', '=', 'c.id')
            ->leftJoin('category_incomes as ci', 'em.category_income_id', '=', 'ci.id')
            ->where('em.type', 'Ingreso')
            ->where('em.status', 'Activo')
            ->when($request->filled('event_id'), function($query) use ($request) {
                return $query->where('em.event_id', $request->get('event_id'));
            })
            ->when($request->filled('start_date'), function($query) use ($request) {
                return $query->where('em.date', '>=', $request->get('start_date'));
            })
            ->when($request->filled('end_date'), function($query) use ($request) {
                return $query->where('em.date', '<=', $request->get('end_date'));
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
        $categoriasEgreso = DB::table('category_egresses')->select('id', 'name')->get();
        $totalesEgreso = DB::table('event_movements as em')
            ->join('currencies as c', 'em.currency_id', '=', 'c.id')
            ->leftJoin('category_egresses as ce', 'em.category_egress_id', '=', 'ce.id')
            ->where('em.type', 'Egreso')
            ->where('em.status', 'Activo')
            ->when($request->filled('event_id'), function($query) use ($request) {
                return $query->where('em.event_id', $request->get('event_id'));
            })
            ->when($request->filled('start_date'), function($query) use ($request) {
                return $query->where('em.date', '>=', $request->get('start_date'));
            })
            ->when($request->filled('end_date'), function($query) use ($request) {
                return $query->where('em.date', '<=', $request->get('end_date'));
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

        $pdf = \PDF::loadView('reports.event-currency-statement-pdf', compact(
            'events', 'totales', 'monedas', 'categorias',
            'categoriasEgreso', 'totalesEgreso'
        ));

        $filename = 'estado-resultados-evento-moneda-' . date('Y-m-d-H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export the event currency statement to Excel.
     */
    public function eventCurrencyStatementExcel(Request $request)
    {
        return \Excel::download(
            new \App\Exports\EventCurrencyStatementExport($request),
            'estado-resultados-evento-moneda-' . date('Y-m-d-H-i-s') . '.xlsx'
        );
    }

    /**
     * Display general statement report.
     */
    public function generalStatement(Request $request)
    {
        if ($request->ajax()) {
            $data = EventMovement::with([
                'event',
                'club', 
                'currency', 
                'methodPayment', 
                'methodPayment.entity', 
                'supplier',
                'categoryIncome',
                'categoryEgress',
                'accountReceivablePayment',
                'accountPayablePayment'
            ])
            ->where('status', '!=', 'Cancelado'); // Excluir movimientos cancelados

            // Log para depuración (comentado para producción)
            // $sampleRecord = $data->first();
            // \Log::info('General Statement Data', [
            //     'total_records' => $data->count(),
            //     'sample_record_method_payment' => $sampleRecord?->methodPayment?->toArray(),
            //     'sample_record_method_payment_entity' => $sampleRecord?->methodPayment?->entity?->toArray()
            // ]);

            return DataTables::of($data)
                ->filter(function ($query) use ($request) {
                    // Filtro por evento
                    if ($request->filled('event_id')) {
                        $query->where('event_id', $request->get('event_id'));
                    }

                    // Filtro por moneda
                    if ($request->filled('currency_id')) {
                        $query->where('currency_id', $request->get('currency_id'));
                    }

                    // Filtro por tipo de ingreso
                    if ($request->filled('category_income_id')) {
                        $query->where('category_income_id', $request->get('category_income_id'));
                    }

                    // Filtro por tipo de egreso
                    if ($request->filled('category_egress_id')) {
                        $query->where('category_egress_id', $request->get('category_egress_id'));
                    }

                    // Filtro por rango de fechas
                    if ($request->filled('start_date')) {
                        $query->where('date', '>=', $request->get('start_date'));
                    }
                    if ($request->filled('end_date')) {
                        $query->where('date', '<=', $request->get('end_date'));
                    }

                    // Búsqueda global
                    if ($request->has('search') && !empty($request->get('search')['value'])) {
                        $searchValue = $request->get('search')['value'];

                        $query->where(function ($subQuery) use ($searchValue) {
                            // Búsqueda en columnas directas de event_movements
                            $subQuery->where('date', 'like', "%{$searchValue}%")
                                     ->orWhere('type', 'like', "%{$searchValue}%")
                                     ->orWhere('amount', 'like', "%{$searchValue}%")
                                     ->orWhere('description', 'like', "%{$searchValue}%");

                            // Búsqueda en la relación 'event'
                            $subQuery->orWhereHas('event', function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%");
                            });

                            // Búsqueda en la relación 'currency'
                            $subQuery->orWhereHas('currency', function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%");
                            });

                            // Búsqueda en la relación 'club'
                            $subQuery->orWhereHas('club', function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%");
                            });

                            // Búsqueda en la relación 'supplier'
                            $subQuery->orWhereHas('supplier', function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%");
                            });

                            // Búsqueda en la relación 'categoryIncome'
                            $subQuery->orWhereHas('categoryIncome', function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%");
                            });

                            // Búsqueda en la relación 'categoryEgress'
                            $subQuery->orWhereHas('categoryEgress', function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%");
                            });

                            // Búsqueda en la relación 'methodPayment' y su anidada 'entity'
                            $subQuery->orWhereHas('methodPayment', function ($q) use ($searchValue) {
                                $q->where('account_holder', 'like', "%{$searchValue}%")
                                  ->orWhere('type_account', 'like', "%{$searchValue}%")
                                  ->orWhereHas('entity', function ($nested_q) use ($searchValue) {
                                      $nested_q->where('name', 'like', "%{$searchValue}%");
                                  });
                            });
                        });
                    }
                })
                ->make(true);
        }

        $events = Event::all();
        $currencies = Currency::all();
        $categoryIncomes = CategoryIncome::all();
        $categoryEgress = CategoryEgress::all();

        return view('reports.general-statement', compact(
            'events', 
            'currencies', 
            'categoryIncomes', 
            'categoryEgress'
        ));
    }

    public function totals(Request $request)
    {
        
    }

    /**
     * Display the accounts statement report.
     */
    public function accountsStatement(Request $request)
    {
        if ($request->ajax()) {
            $data = EventMovement::with([
                'event',
                'currency',
                'categoryIncome',
                'categoryEgress',
                'club',
                'supplier',
                'methodPayment.entity'
            ])
            ->where('status', '!=', 'Cancelado') // Excluir movimientos cancelados
            ->whereNotNull('method_payment_id') // Solo movimientos con método de pago
            ->when($request->filled('event_id'), function ($query) use ($request) {
                $query->where('event_id', $request->get('event_id'));
            })
            ->when($request->filled('currency_id'), function ($query) use ($request) {
                $query->where('currency_id', $request->get('currency_id'));
            })
            ->when($request->filled('method_payment_id'), function ($query) use ($request) {
                $query->where('method_payment_id', $request->get('method_payment_id'));
            })
            ->when($request->filled('category_income_id'), function ($query) use ($request) {
                $query->where('category_income_id', $request->get('category_income_id'));
            })
            ->when($request->filled('category_egress_id'), function ($query) use ($request) {
                $query->where('category_egress_id', $request->get('category_egress_id'));
            })
            ->when($request->filled('start_date'), function ($query) use ($request) {
                $query->where('date', '>=', $request->get('start_date'));
            })
            ->when($request->filled('end_date'), function ($query) use ($request) {
                $query->where('date', '<=', $request->get('end_date'));
            })
            ->when($request->has('search') && !empty($request->get('search')['value']), function ($query) use ($request) {
                $searchValue = $request->get('search')['value'];
                $query->where(function ($subQuery) use ($searchValue) {
                    $subQuery->where('date', 'like', "%{$searchValue}%")
                             ->orWhere('type', 'like', "%{$searchValue}%")
                             ->orWhere('amount', 'like', "%{$searchValue}%")
                             ->orWhere('description', 'like', "%{$searchValue}%")
                             ->orWhereHas('event', function ($q) use ($searchValue) {
                                 $q->where('name', 'like', "%{$searchValue}%");
                             })
                             ->orWhereHas('currency', function ($q) use ($searchValue) {
                                 $q->where('name', 'like', "%{$searchValue}%");
                             })
                             ->orWhereHas('methodPayment', function ($q) use ($searchValue) {
                                 $q->where('account_holder', 'like', "%{$searchValue}%")
                                   ->orWhere('type_account', 'like', "%{$searchValue}%")
                                   ->orWhereHas('entity', function ($nested_q) use ($searchValue) {
                                       $nested_q->where('name', 'like', "%{$searchValue}%");
                                   });
                             });
                });
            });

            $dataTable = DataTables::of($data)
                ->addColumn('formatted_date', function ($row) {
                    return $row->date ? $row->date->format('d/m/Y') : '-';
                })
                ->addColumn('formatted_amount', function ($row) {
                    return number_format($row->amount, 0, ',', '.');
                })
                ->addColumn('account_info', function ($row) {
                    if ($row->methodPayment) {
                        return $row->methodPayment->account_holder . ' - ' . 
                               ($row->methodPayment->entity ? $row->methodPayment->entity->name : '') . ' - ' . 
                               $row->methodPayment->type_account;
                    }
                    return '-';
                })
                ->rawColumns(['formatted_date', 'formatted_amount', 'account_info']);

            return $dataTable->make(true);
        }

        $events = Event::all();
        $currencies = Currency::all();
        $methodPayments = MethodPayment::with('entity')->get();
        $categoryIncomes = CategoryIncome::all();
        $categoryEgress = CategoryEgress::all();

        return view('reports.accounts-statement', compact(
            'events', 
            'currencies', 
            'methodPayments',
            'categoryIncomes',
            'categoryEgress'
        ));
    }

    /**
     * Mostrar el reporte de movimientos por cuentas/métodos de pago
     */
    public function movementsStatement(Request $request)
    {
        if ($request->ajax()) {
            $data = EventMovement::with([
                'event',
                'currency',
                'categoryIncome',
                'categoryEgress',
                'club',
                'supplier',
                'methodPayment.entity'
            ])
            ->where('status', '!=', 'Cancelado') // Excluir movimientos cancelados
            ->whereNotNull('method_payment_id') // Solo movimientos con método de pago
            ->when($request->filled('event_id'), function ($query) use ($request) {
                $query->where('event_id', $request->get('event_id'));
            })
            ->when($request->filled('currency_id'), function ($query) use ($request) {
                $query->where('currency_id', $request->get('currency_id'));
            })
            ->when($request->filled('method_payment_id'), function ($query) use ($request) {
                $query->where('method_payment_id', $request->get('method_payment_id'));
            })
            ->when($request->filled('category_income_id'), function ($query) use ($request) {
                $query->where('category_income_id', $request->get('category_income_id'));
            })
            ->when($request->filled('category_egress_id'), function ($query) use ($request) {
                $query->where('category_egress_id', $request->get('category_egress_id'));
            })
            ->when($request->filled('start_date'), function ($query) use ($request) {
                $query->where('date', '>=', $request->get('start_date'));
            })
            ->when($request->filled('end_date'), function ($query) use ($request) {
                $query->where('date', '<=', $request->get('end_date'));
            })
            ->when($request->has('search') && !empty($request->get('search')['value']), function ($query) use ($request) {
                $searchValue = $request->get('search')['value'];
                $query->where(function ($subQuery) use ($searchValue) {
                    $subQuery->where('date', 'like', "%{$searchValue}%")
                             ->orWhere('type', 'like', "%{$searchValue}%")
                             ->orWhere('amount', 'like', "%{$searchValue}%")
                             ->orWhere('description', 'like', "%{$searchValue}%")
                             ->orWhereHas('event', function ($q) use ($searchValue) {
                                 $q->where('name', 'like', "%{$searchValue}%");
                             })
                             ->orWhereHas('currency', function ($q) use ($searchValue) {
                                 $q->where('name', 'like', "%{$searchValue}%");
                             })
                             ->orWhereHas('methodPayment', function ($q) use ($searchValue) {
                                 $q->where('account_holder', 'like', "%{$searchValue}%")
                                   ->orWhere('type_account', 'like', "%{$searchValue}%")
                                   ->orWhereHas('entity', function ($nested_q) use ($searchValue) {
                                       $nested_q->where('name', 'like', "%{$searchValue}%");
                                   });
                             });
                });
            });

            $dataTable = DataTables::of($data)
                ->addColumn('formatted_date', function ($row) {
                    return $row->date ? $row->date->format('d/m/Y') : '-';
                })
                ->addColumn('formatted_amount', function ($row) {
                    return number_format($row->amount, 0, ',', '.');
                })
                ->addColumn('account_info', function ($row) {
                    if ($row->methodPayment) {
                        return $row->methodPayment->account_holder . ' - ' . 
                               ($row->methodPayment->entity ? $row->methodPayment->entity->name : '') . ' - ' . 
                               $row->methodPayment->type_account;
                    }
                    return '-';
                })
                ->rawColumns(['formatted_date', 'formatted_amount', 'account_info']);

            return $dataTable->make(true);
        }

        $events = Event::all();
        $currencies = Currency::all();
        $methodPayments = MethodPayment::with('entity')->get();
        $categoryIncomes = CategoryIncome::all();
        $categoryEgress = CategoryEgress::all();

        return view('reports.movements-statement', compact(
            'events', 
            'currencies', 
            'methodPayments',
            'categoryIncomes',
            'categoryEgress'
        ));
    }

    /**
     * Exportar reporte de movimientos a PDF
     */
    public function movementsStatementPdf(Request $request)
    {
        $data = EventMovement::with([
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
        ->when($request->filled('event_id'), function ($query) use ($request) {
            $query->where('event_id', $request->get('event_id'));
        })
        ->when($request->filled('category_income_id'), function ($query) use ($request) {
            $query->where('category_income_id', $request->get('category_income_id'));
        })
        ->when($request->filled('category_egress_id'), function ($query) use ($request) {
            $query->where('category_egress_id', $request->get('category_egress_id'));
        })
        ->when($request->filled('start_date'), function ($query) use ($request) {
            $query->where('date', '>=', $request->get('start_date'));
        })
        ->when($request->filled('end_date'), function ($query) use ($request) {
            $query->where('date', '<=', $request->get('end_date'));
        })
        ->orderBy('date', 'desc')
        ->get();

        $pdf = \PDF::loadView('reports.movements-statement-pdf', compact('data'));
        return $pdf->download('estado-general-movimientos.pdf');
    }

    /**
     * Exportar reporte de movimientos a Excel
     */
    public function movementsStatementExcel(Request $request)
    {
        return \Excel::download(new \App\Exports\MovementsStatementExport($request), 'estado-general-movimientos.xlsx');
    }
}
