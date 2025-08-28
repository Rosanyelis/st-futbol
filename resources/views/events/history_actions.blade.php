<div class="d-flex gap-2">
    <a href="javascript:;" class="btn btn-icon text-secondary rounded-pill"
    onclick="historyManager.openEditModal({{ $data }})">
        <i class="icon-base ri ri-edit-2-line icon-md"></i>
    </a>
    <a href="javascript:;" class="btn btn-icon text-danger rounded-pill delete-record"
    onclick="historyManager.deleteMovement({{ $data->id }})">
        <i class="icon-base ri ri-delete-bin-7-line icon-md"></i>
    </a>      
</div>
