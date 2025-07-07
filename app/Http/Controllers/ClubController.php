<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Club;
use App\Models\Event;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Province;
use App\Models\Supplier;
use App\Models\ClubPayment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Clubs\StoreClubRequest;
use App\Http\Requests\Clubs\UpdateClubRequest;

class ClubController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Club::with(['event', 'currency', 'supplier', 'country', 'province', 'city', 'supplier.categorySupplier'])
                        ->get();
            return DataTables::of($data)
                ->addColumn('event', function ($data) {
                    return $data->event->name ?? '';
                })
                ->addColumn('country', function ($data) {
                    return $data->country->name ?? '';
                })
                ->addColumn('currency', function ($data) {
                    return $data->currency->name ?? '';
                })
                ->addColumn('supplier', function ($data) {
                    return $data->supplier->name ?? '';
                })
                ->addColumn('category_supplier', function ($data) {
                    return $data->supplier->categorySupplier->name ?? '';
                })
                ->addColumn('actions', function ($data) {
                    return view('clubs.actions', ['id' => $data->id]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }         return view('clubs.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $events = Event::all();
        $currencies = Currency::all();
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $countries = Country::orderBy('name', 'asc')->get();
        return view('clubs.create', compact('events', 'currencies', 'suppliers', 'countries'));
    }

    public function getProvinces(Request $request)
    {
        $countryId = $request->input('country_id');
        $provinces = Province::where('country_id', $countryId)->orderBy('name', 'asc')->get();
        return response()->json($provinces);
    }

    public function getCities(Request $request)
    {
        $provinceId = $request->input('province_id');
        $cities = City::where('province_id', $provinceId)->orderBy('name', 'asc')->get();
        return response()->json($cities);
    }

    public function getSuppliersByEvent(Request $request)
    {
        $eventId = $request->input('event_id');
        $suppliers = Supplier::where('event_id', $eventId)->orderBy('name', 'asc')->get();
        return response()->json($suppliers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClubRequest $request)
    {
        try {
            $data = $request->all();

            // Eliminar comas de los montos antes de guardar
            $fieldsToClean = [
                'players_quantity', 'teachers_quantity', 'companions_quantity', 'drivers_quantity', 'liberated_quantity',
                'player_price', 'teacher_price', 'companion_price', 'driver_price', 'liberated_price',
                'total_players', 'total_teachers', 'total_companions', 'total_drivers', 'total_liberated',
                'total_people', 'total_amount'
            ];
            foreach ($fieldsToClean as $field) {
                if (isset($data[$field])) {
                    $data[$field] = str_replace(',', '', $data[$field]);
                }
            }

            if ($request->hasFile('logo')) {
                $logoPath = $this->saveFile($request->file('logo'), 'clubs/logos/');
                $data['logo'] = $logoPath;
            }
            $data['category_income_id'] = 1; // Ingreso de clubes
            $club = Club::create($data);

            return redirect()->route('club.index')
                ->with('success', 'Club creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('club.index')->with('error', 'Error al crear el club: ' . $e->getMessage());
        }
        
    }

    /**
     * Display the specified resource.
     */
    public function show($club)
    {
        $club = Club::with(['event', 'currency', 'supplier', 'country', 'province', 'city'])->find($club);
        return view('clubs.show', compact('club'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $events = Event::all();
        $currencies = Currency::all();
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $countries = Country::orderBy('name', 'asc')->get();
        $club = Club::find($id);
        return view('clubs.edit', compact('club', 'events', 'currencies', 'suppliers', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClubRequest $request, $club)
    {
        try {
            $data = $request->all();
            // Eliminar comas de los montos antes de guardar
            $fieldsToClean = [
                'players_quantity', 'teachers_quantity', 'companions_quantity', 'drivers_quantity', 'liberated_quantity',
                'player_price', 'teacher_price', 'companion_price', 'driver_price', 'liberated_price',
                'total_players', 'total_teachers', 'total_companions', 'total_drivers', 'total_liberated',
                'total_people', 'total_amount'
            ];
            foreach ($fieldsToClean as $field) {
                if (isset($data[$field])) {
                    $data[$field] = str_replace(',', '', $data[$field]);
                }
            }
            $club = Club::find($club);
            if ($request->hasFile('logo')) {
                $logoPath = $this->saveFile($request->file('logo'), 'clubs/logos/');
                $data['logo'] = $logoPath;
            }
            $club->update($data);
            return redirect()->route('club.index')->with('success', 'Club actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('club.index')->with('error', 'Error al actualizar el club');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($club)
    {
        try {
            $club = Club::find($club);
            if ($club->logo) {
                Storage::delete('public/' . $club->logo);
            }
            $club->delete();
            return redirect()->route('club.index')->with('success', 'Club eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('club.index')->with('error', 'Error al eliminar el club');
        }
    }

    private function saveFile($file, $path)
    {
        try {
            if (!$file) {
                return null;
            }

            // Generar un nombre único para el archivo
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Crear el directorio si no existe
            $fullPath = storage_path('app/public/' . $path);
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            // Mover el archivo al directorio de almacenamiento
            $file->move($fullPath, $fileName);

            // Retornar la ruta relativa para guardar en la base de datos
            return $path . $fileName;
        } catch (\Exception $e) {
            throw new \Exception('Error al guardar la imagen: ' . $e->getMessage());
        }
    }

    public function showPayment($club, $payment)
    {
        $club = Club::with(['currency', 'payments'])->findOrFail($club);
        $payment = ClubPayment::with(['methodPayment', 'currency'])->findOrFail($payment);
        return Pdf::loadView('clubs.recibo', compact('club', 'payment'))
            // ->setPaper([0,0,150,1000])
            ->stream(''.config('app.name', 'Laravel').' - Recibo de Club ' . $club->name. ' nro ' . $payment->id . '.pdf');
    }
}
