<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Event;
use App\Models\Currency;
use App\Models\ClubPayment;
use Illuminate\Http\Request;
use App\Models\EventMovement;
use App\Models\MethodPayment;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

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
                         'methodPayment.categoryMethodPayment', 'club')
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
                         'expense.categoryExpense', 'expense.subcategoryExpense')
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

    /**
     * Store a newly created resource in storage.
     */
     public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Club::join('events', 'clubs.event_id', '=', 'events.id')
                ->join('currencies', 'clubs.currency_id', '=', 'currencies.id')
                ->select('clubs.*', 'events.name as event_name', 'currencies.name as currency_name');
            return DataTables::of($data)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && $request->search['value'] != '') {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->where('clubs.name', 'like', "%{$search}%")
                                ->orWhere('events.name', 'like', "%{$search}%")
                                ->orWhere('currencies.name', 'like', "%{$search}%");
                        });
                    }

                    # filtrado por evento
                    if ($request->filled('event')) {
                        $query->where('clubs.event_id', $request->get('event'));
                    }
                })
                ->addColumn('pendiente', function ($row) {
                    $saldo = ClubPayment::where('club_id', $row->id)->sum('amount');
                    return $row->total_amount - $saldo;
                })

                ->make(true);
        }

        $paymentMethods = MethodPayment::all();
        // obtener los totales pendientes por moneda y las que no tengan datos declararlas en cero
        $currencies = Currency::all();
        return view('reports.accounts-receivable', compact('paymentMethods', 'currencies'));
    }

    /**
     * Display the specified resource.
     */
   

    public function eventCurrencyStatement(Request $request)
    {
        $events = Event::all();
        $monedas = Currency::all();
        $categorias = DB::table('category_incomes')->select('id', 'name')->get();

        // Totales de ingresos
        $totales = DB::table('category_incomes as ci')
            ->crossJoin('currencies as c')
            ->leftJoin('event_movements as em', function($join) use ($request) {
                $join->on('em.category_income_id', '=', 'ci.id')
                     ->on('em.currency_id', '=', 'c.id')
                     ->where('em.type', 'Ingreso');
                if ($request->filled('event_id')) {
                    $join->where('em.event_id', $request->get('event_id'));
                }
            })
            ->select(
                'ci.name as categoria',
                'c.name as moneda',
                DB::raw('COALESCE(SUM(em.amount), 0) as total')
            )
            ->groupBy('ci.name', 'c.name')
            ->orderBy('ci.name')
            ->orderBy('c.name')
            ->get();

        // Totales de egresos
        $categoriasEgreso = DB::table('category_egresses')->select('id', 'name')->get();
        $totalesEgreso = DB::table('category_egresses as ce')
            ->crossJoin('currencies as c')
            ->leftJoin('event_movements as em', function($join) use ($request) {
                $join->on('em.category_egress_id', '=', 'ce.id')
                     ->on('em.currency_id', '=', 'c.id')
                     ->where('em.type', 'Egreso');
                if ($request->filled('event_id')) {
                    $join->where('em.event_id', $request->get('event_id'));
                }
            })
            ->select(
                'ce.name as categoria',
                'c.name as moneda',
                DB::raw('COALESCE(SUM(em.amount), 0) as total')
            )
            ->groupBy('ce.name', 'c.name')
            ->orderBy('ce.name')
            ->orderBy('c.name')
            ->get();

        return view('reports.event-currency-statement', compact(
            'events', 'totales', 'monedas', 'categorias',
            'categoriasEgreso', 'totalesEgreso'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function totals(Request $request)
    {
        
    }
}
