<div class="d-flex gap-2">
    <!-- Botón Editar -->
    <a href="{{ route('history-change-currency.edit', $row->id) }}" 
       class="btn btn-sm btn-icon btn-text-warning" 
       title="Editar cambio de moneda">
        <i class="ri-edit-line"></i>
    </a>
    
    <!-- Botón Cancelar -->
    <button type="button" 
            class="btn btn-sm btn-icon btn-text-danger" 
            onclick="cancelChangeCurrency({{ $row->id }})"
            title="Cancelar cambio de moneda">
        <i class="ri-close-circle-line"></i>
    </button>
</div>
