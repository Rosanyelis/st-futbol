<a href="{{ route('club.show', $row->id) }}" class="btn btn-sm btn-icon btn-text-secondary
    rounded-pill"
    data-bs-toggle="tooltip" title="Ver Club">
    <i class="ri-eye-line ri-20px"></i>
</a>
@if ($pendiente > 0)
<a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary
    rounded-pill text-success" onclick="payOrder({{ $row->id }}, {{ $pendiente }})"
    data-bs-toggle="tooltip" title="Cobrar">
    <i class="ri-refund-2-line ri-20px"></i>
</a>
@endif