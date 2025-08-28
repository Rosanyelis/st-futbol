<a href="{{ route('club.show', $id) }}" class="btn btn-sm btn-icon btn-text-secondary
    rounded-pill"
    data-bs-toggle="tooltip" title="Ver Club">
    <i class="ri-eye-line ri-20px"></i>
</a>

<a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary
    rounded-pill"
    data-bs-toggle="tooltip" title="Asignar Eventos"
    onclick="openAssignEventsModal({{ $id }})">
    <i class="ri-calendar-event-line ri-20px"></i>
</a>

<a href="{{ route('club.edit', $id) }}" class="btn btn-sm btn-icon btn-text-secondary
    rounded-pill"
    data-bs-toggle="tooltip" title="Editar Club">
    <i class="ri-edit-2-line ri-20px"></i>
</a>

<a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary
    rounded-pill text-danger"
    data-bs-toggle="tooltip" title="Eliminar Club"
    onclick="deleteRecord({{ $id }})">
    <i class="ri-delete-bin-7-line ri-20px"></i>
</a>

<a href="storage/{{ $club->logo }}" target="_blank" class="btn btn-sm btn-icon btn-text-secondary
    rounded-pill text-success"
    data-bs-toggle="tooltip" title="Descargar Logo">
    <i class="ri-download-2-line ri-20px"></i>
</a>