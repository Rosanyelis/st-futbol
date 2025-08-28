<?php

namespace App\Http\Controllers;

use App\Models\EventMovement;
use App\Models\MethodPayment;
use App\Models\AccountReceivablePayment;
use App\Models\AccountPayablePayment;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EventMovementController extends Controller
{
    /**
     * Cancelar un movimiento de evento y restaurar el dinero
     */
    public function cancelMovement($id)
    {
        DB::beginTransaction();
        
        try {
            $movement = EventMovement::findOrFail($id);
            
            // Verificar que el movimiento esté activo
            if ($movement->status !== 'Activo') {
                throw new \Exception('El movimiento ya ha sido cancelado');
            }
            
            // Restaurar el dinero del método de pago si existe
            if ($movement->method_payment_id) {
                $methodPayment = MethodPayment::find($movement->method_payment_id);
                if ($methodPayment) {
                    $amount = floatval($movement->amount);
                    
                    if ($movement->type === 'Ingreso') {
                        // Si era un ingreso, restar del balance
                        $methodPayment->current_balance -= $amount;
                    } elseif ($movement->type === 'Egreso') {
                        // Si era un egreso, sumar al balance
                        $methodPayment->current_balance += $amount;
                    }
                    
                    $methodPayment->save();
                }
            }
            
            // Si es un pago de cuenta por cobrar, eliminar el pago usando la relación directa
            if ($movement->account_receivable_id) {
                $payment = AccountReceivablePayment::where('account_receivable_id', $movement->account_receivable_id)
                    ->where('amount', $movement->amount)
                    ->where('date', $movement->date)
                    ->first();
                
                if ($payment) {
                    $payment->delete();
                }
            }
            
            // Si es un pago a proveedor (cuenta por pagar), eliminar el pago usando la relación directa
            if ($movement->account_payable_id) {
                $payment = AccountPayablePayment::where('account_payable_id', $movement->account_payable_id)
                    ->where('amount', $movement->amount)
                    ->where('date', $movement->date)
                    ->first();
                
                if ($payment) {
                    $payment->delete();
                }
            }
            
            // Marcar el movimiento como cancelado
            $movement->update([
                'status' => 'Cancelado',
                'user_id' => Auth::id() // Usuario que cancela
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Movimiento cancelado correctamente. El dinero ha sido restaurado.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar el movimiento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Actualizar un movimiento de evento
     */
    public function updateMovement(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $movement = EventMovement::findOrFail($id);
            
            // Verificar que el movimiento esté activo
            if ($movement->status !== 'Activo') {
                throw new \Exception('No se puede editar un movimiento cancelado');
            }
            
            $oldAmount = $movement->amount;
            $newAmount = floatval($request->amount);
            
            // Actualizar el movimiento
            $movement->update([
                'amount' => $newAmount,
                'date' => $request->date ?? $movement->date,
                'description' => $request->description ?? $movement->description,
                'user_id' => Auth::id() // Usuario que realiza la edición
            ]);
            
            // Actualizar el balance del método de pago si existe
            if ($movement->method_payment_id) {
                $methodPayment = MethodPayment::find($movement->method_payment_id);
                if ($methodPayment) {
                    $difference = $newAmount - $oldAmount;
                    
                    if ($movement->type === 'Ingreso') {
                        $methodPayment->current_balance += $difference;
                    } elseif ($movement->type === 'Egreso') {
                        $methodPayment->current_balance -= $difference;
                    }
                    
                    $methodPayment->save();
                }
            }
            
            // Actualizar el pago correspondiente en la cuenta por cobrar
            if ($movement->account_receivable_id) {
                $payment = AccountReceivablePayment::where('account_receivable_id', $movement->account_receivable_id)
                    ->where('amount', $oldAmount)
                    ->where('date', $movement->date)
                    ->first();
                
                if ($payment) {
                    $payment->update([
                        'amount' => $newAmount,
                        'date' => $request->date ?? $movement->date
                    ]);
                }
            }
            
            // Actualizar el pago correspondiente en la cuenta por pagar
            if ($movement->account_payable_id) {
                $payment = AccountPayablePayment::where('account_payable_id', $movement->account_payable_id)
                    ->where('amount', $oldAmount)
                    ->where('date', $movement->date)
                    ->first();
                
                if ($payment) {
                    $payment->update([
                        'amount' => $newAmount,
                        'date' => $request->date ?? $movement->date
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Movimiento actualizado correctamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el movimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener movimientos de un evento específico
     */
    public function getMovementsByEvent($eventId)
    {
        $movements = EventMovement::with(['club', 'methodPayment', 'currency', 'user', 'supplier', 'accountPayable', 'accountReceivable'])
            ->where('event_id', $eventId)
            ->where('status', 'Activo')
            ->orderBy('date', 'desc')
            ->get();
            
        return response()->json($movements);
    }
    
    /**
     * Obtener movimientos de un club específico
     */
    public function getMovementsByClub($clubId)
    {
        $movements = EventMovement::with(['event', 'methodPayment', 'currency', 'user', 'supplier', 'accountPayable', 'accountReceivable'])
            ->where('club_id', $clubId)
            ->where('status', 'Activo')
            ->orderBy('date', 'desc')
            ->get();
            
        return response()->json($movements);
    }
}
