@if($canDelete)
    <a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary
        rounded-pill text-danger"
        data-bs-toggle="tooltip" title="Eliminar Asignación"
        onclick="deleteEventAssignment({{ $supplierId }}, {{ $eventId }})">
        <i class="ri-delete-bin-7-line ri-20px"></i>
    </a>
@else
    <span class="badge bg-warning" data-bs-toggle="tooltip" title="No se puede eliminar porque tiene cuentas por pagar">
        <i class="ri-error-warning-line me-1"></i>Con cuentas
    </span>
@endif
