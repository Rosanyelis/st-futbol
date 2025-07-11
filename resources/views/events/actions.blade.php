
<a href="{{ route('event.history', $id) }}" class="btn btn-sm btn-icon btn-text-secondary
    rounded-pill"
    data-bs-toggle="tooltip" title="Historial de Movimientos">
    <i class="ri-history-line ri-20px"></i>
</a>

<a href="{{ route('event.edit', $id) }}" class="btn btn-sm btn-icon btn-text-secondary
    rounded-pill"
    data-bs-toggle="tooltip" title="Editar Evento">
    <i class="ri-edit-2-line ri-20px"></i>
</a>

<a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary
    rounded-pill text-danger"
    data-bs-toggle="tooltip" title="Eliminar Evento"
    onclick="deleteRecord({{ $id }})">
    <i class="ri-delete-bin-7-line ri-20px"></i>
</a>
