@if ($pendiente > 0)
<a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary rounded-pill text-success" 
onclick="payOrder({{ $row->id }}, {{ $pendiente }})" data-bs-toggle="tooltip" title="Pagar"> 
    <i class="ri-money-cny-circle-line ri-20px"></i>
</a>
@endif