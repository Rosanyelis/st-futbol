<div class="modal fade" id="PayOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="formPayOrder" action="{{ route('account-receivable.processPayment') }}" method="POST">
                @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalCenterTitle">Cobrar la Cuenta Nº <span id="modalpreorden_id"></span> </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row gy-3">
                    
                    <div class="col-md-12">
                        <h3>Monto a cobrar: <span id="modalamount" ></span></h3>
                    </div>
                    <div class="col-md-12">
                        <div class="form-floating form-floating-outline">
                            <input type="text" id="amount" name="amount" class="form-control" value="" />
                            <label for="amount">Monto</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-floating form-floating-outline">
                            <input type="date" id="date" name="date" class="form-control" value="" />
                            <label for="date">Fecha de Pago</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-floating form-floating-outline">
                            <select id="method_payment_id" name="method_payment_id"
                                class="form-select select2"
                                placeholder="Selecione Método de pago">
                                <option value="">-- Seleccionar --</option>
                                @foreach($paymentMethods as $paymentMethod)
                                <option value="{{ $paymentMethod->id }}">
                                    {{ $paymentMethod->account_holder }} - 
                                    Entidad: {{ $paymentMethod->entity->name }} - 
                                    Moneda: {{ $paymentMethod->currency->name }} - 
                                    Saldo: {{ $paymentMethod->current_balance }} 
                                </option>
                                @endforeach
                            </select>
                            <label for="code">Método de pago</label>
                        </div>
                    </div>
                    
                    
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="club_id" id="club_id">
                <button type="submit" class="btn btn-primary" id="btnPayOrder">Pagar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
            </form>
        </div>
    </div>
</div>
