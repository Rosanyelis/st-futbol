<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Club;
use App\Models\Country;
use App\Models\Province;
use App\Models\Supplier;
use App\Models\ClubPayment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Clubs\StoreClubRequest;
use App\Http\Requests\Clubs\UpdateClubRequest;

class ClubController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Mostrar listado de clubs
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Club::with(['country', 'province', 'city'])
                        ->get();
            return DataTables::of($data)
                ->addColumn('country', function ($data) {
                    return $data->country->name ?? '';
                })
               
                ->addColumn('actions', function ($data) {
                    return view('clubs.actions', ['id' => $data->id, 'club' => $data]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }         
        return view('clubs.index');
    }

    /**
     * Mostrar formulario para crear un nuevo club
     */
    public function create()
    {
        $countries = Country::orderBy('name', 'asc')->get();
        return view('clubs.create', compact('countries'));
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

    /**
     * Obtener proveedores por evento (mantenido para compatibilidad)
     */
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
            DB::beginTransaction();
            
            $data = $request->all();
            if ($request->hasFile('logo')) {
                $logoPath = $this->saveFile($request->file('logo'), 'clubs/logos/');
                $data['logo'] = $logoPath;
            }
            
            $club = Club::create($data);

            DB::commit();
            return redirect()->route('club.index')->with('success', 'Club creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('club.index')->with('error', 'Error al crear el club: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar detalles de un club
     */
    public function show($club)
    {
        $club = Club::with(['events', 'currency', 'supplier', 'country', 'province', 'city'])->find($club);
        return view('clubs.show', compact('club'));
    }

    /**
     * Mostrar formulario para editar un club
     */
    public function edit($id)
    {
        $countries = Country::orderBy('name', 'asc')->get();
        $club = Club::find($id);
        return view('clubs.edit', compact('club', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClubRequest $request, $club)
    {
        try {
            DB::beginTransaction();
            
            $data = $request->all();
            $club = Club::find($club);
            
            if ($request->hasFile('logo')) {
                $logoPath = $this->saveFile($request->file('logo'), 'clubs/logos/');
                $data['logo'] = $logoPath;
            }
            
            $club->update($data);

            DB::commit();
            return redirect()->route('club.index')->with('success', 'Club actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('club.index')->with('error', 'Error al actualizar el club: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar las cuentas por cobrar del club
     */
    private function updateAccountReceivables(Club $club, float $newTotalAmount): void
    {
        // Obtener todas las cuentas por cobrar pendientes del club
        $pendingReceivables = $club->accountReceivables()->pending()->get();
        
        foreach ($pendingReceivables as $receivable) {
            // Calcular la diferencia
            $difference = $newTotalAmount - $receivable->total_amount;
            
            if ($difference != 0) {
                $receivable->total_amount = $newTotalAmount;
                $receivable->calculatePendingAmount();
                $receivable->save();
            }
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
            $fileName =  time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
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
