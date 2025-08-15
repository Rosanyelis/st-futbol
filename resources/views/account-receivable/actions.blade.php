<div class="d-flex align-items-center">
    <a href="{{ route('club.show', $row->club_id) }}" class="btn btn-sm btn-icon btn-text-secondary
        rounded-pill"
        data-bs-toggle="tooltip" title="Ver Club">
        <i class="ri-eye-line ri-20px"></i>
    </a>
    
    @if ($row->pending_amount > 0)
    <a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary
        rounded-pill text-success" onclick="payOrder({{ $row->id }}, {{ $row->pending_amount }})"
        data-bs-toggle="tooltip" title="Cobrar">
        <i class="ri-refund-2-line ri-20px"></i>
    </a>
    @endif
    
    <a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary
        rounded-pill text-info" onclick="viewPayments({{ $row->id }})"
        data-bs-toggle="tooltip" title="Ver Pagos">
        <i class="ri-history-line ri-20px"></i>
    </a>
</div>