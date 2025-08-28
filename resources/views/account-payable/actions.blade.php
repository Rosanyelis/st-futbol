<div class="d-flex gap-2">
    <!-- Botón Editar (solo si no hay pagos) -->
    @if($row->payments->count() == 0)
        <a href="{{ route('account-payable.edit', $row->id) }}" 
           class="btn btn-sm btn-icon btn-text-warning" 
           title="Editar cuenta por pagar">
            <i class="ri-edit-line"></i>
        </a>
    @else
        <button type="button" 
                class="btn btn-sm btn-icon btn-text-secondary" 
                onclick="showCannotEditMessage()"
                title="No se puede editar - Existen pagos registrados">
            <i class="ri-edit-line"></i>
        </button>
    @endif
    
    <!-- Botón Eliminar (solo si no hay pagos) -->
    @if($row->payments->count() == 0)
        <button type="button" 
                class="btn btn-sm btn-icon btn-text-danger" 
                onclick="deleteAccountPayable({{ $row->id }})"
                title="Eliminar cuenta por pagar">
            <i class="ri-delete-bin-line"></i>
        </button>
    @else
        <button type="button" 
                class="btn btn-sm btn-icon btn-text-secondary" 
                onclick="showCannotDeleteMessage()"
                title="No se puede eliminar - Existen pagos registrados">
            <i class="ri-delete-bin-line"></i>
        </button>
    @endif
    
    <!-- Botón Pagar (solo si hay monto pendiente) -->
    @if($row->getPendingAmount() > 0)
        <a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary rounded-pill text-success" 
        onclick="payOrder({{ $row->id }}, {{ $row->getPendingAmount() }})" data-bs-toggle="tooltip" title="Pagar"> 
            <i class="ri-money-cny-circle-line ri-20px"></i>
        </a>
    @endif
</div>