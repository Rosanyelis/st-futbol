<div class="d-flex gap-2">
    <!-- Botón Ver -->
    <a href="{{ route('account-receivable.show', $row->id) }}" 
       class="btn btn-sm btn-icon btn-text-primary" 
       title="Ver detalles">
        <i class="ri-eye-line"></i>
    </a>
    
    <!-- Botón Editar (siempre habilitado) -->
    <a href="{{ route('account-receivable.edit', $row->id) }}" 
       class="btn btn-sm btn-icon btn-text-warning" 
       title="Editar cuenta por cobrar">
        <i class="ri-edit-line"></i>
    </a>
    
    <!-- Botón Eliminar (solo si no hay pagos) -->
    @if($row->payments->count() == 0)
        <button type="button" 
                class="btn btn-sm btn-icon btn-text-danger" 
                onclick="deleteAccountReceivable({{ $row->id }})"
                title="Eliminar cuenta por cobrar">
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
        <button type="button" 
                class="btn btn-sm btn-icon btn-text-success" 
                onclick="payOrder({{ $row->id }}, {{ $row->getPendingAmount() }})"
                title="Registrar pago">
            <i class="ri-money-dollar-circle-line"></i>
        </button>
    @endif
</div>