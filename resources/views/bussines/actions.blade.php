<div class="d-flex align-items-center">
    <a href="javascript:;" class="btn btn-icon btn-text-secondary rounded-pill"
    onclick="historyManager.openEditModal({{ $data }})">
        <i class="icon-base ri ri-edit-2-line icon-md"></i>
    </a>
    <a href="javascript:;" class="btn btn-icon btn-text-danger rounded-pill delete-record"
    onclick="historyManager.deleteMovement({{ $data->id }})">
        <i class="icon-base ri ri-delete-bin-7-line icon-md"></i>
    </a>      
</div>
