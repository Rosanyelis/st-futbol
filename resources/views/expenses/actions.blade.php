<a href="{{ route('expense.edit', $row->id) }}" class="btn btn-sm btn-icon btn-text-secondary
    rounded-pill"
    data-bs-toggle="tooltip" title="Editar Gasto">
    <i class="ri-edit-2-line ri-20px"></i>
</a>

<a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary
    rounded-pill text-danger"
        data-bs-toggle="tooltip" title="Eliminar Gasto"
    onclick="deleteRecord({{ $row->id }})">
    <i class="ri-delete-bin-7-line ri-20px"></i>
</a>