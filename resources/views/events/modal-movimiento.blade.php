<div class="modal fade" id="MovimientoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="formMovimiento" action="{{ route('event.storeTransaction', ['id' => $event->id]) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCenterTitle">Crear Movimiento de Ingreso o Egreso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row gy-3">
                        <div class="mb-3"> 
                            <div class="form-floating form-floating-outline">
                                <textarea name="description" class="form-control h-px-100"
                                    id="description"
                                    cols="30" rows="10"
                                    required
                                    ></textarea>
                                <label for="code">Descripción del Movimiento</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-floating form-floating-outline">
                                <select id="type" name="type"
                                    class="form-select "
                                    placeholder="Selecione Tipo de Movimiento"
                                    required
                                    >
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Ingreso">Ingreso</option>
                                    <option value="Egreso">Egreso</option>
                                </select>
                                <label for="code">Tipo de Movimiento</label>
                            </div>
                        </div>
                        <div class="col-md-12" id="type_income_div">
                            <div class="form-floating form-floating-outline">
                                <select id="type_income" name="type_income"
                                    class="form-select "
                                    placeholder="Selecione Tipo de Ingreso">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Club">Club</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <label for="code">Tipo de Ingreso</label>
                            </div>
                        </div>
                        <div class="col-md-12" id="type_expense_div">
                            <div class="form-floating form-floating-outline">
                                <select id="type_expense" name="type_expense"
                                    class="form-select "
                                    placeholder="Selecione Tipo de Egreso">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Proveedor">Proveedor</option>
                                    <option value="Gasto">Gasto</option>
                                </select>
                                <label for="code">Tipo de Egreso</label>
                            </div>
                        </div>
                        <div class="col-md-12" id="club_id_div" >
                            <div class="form-floating form-floating-outline">
                                <select id="club_id" name="club_id"
                                    class="form-select select2"
                                    placeholder="Selecione Club">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach ($clubs as $club)
                                        <option value="{{ $club->id }}">{{ $club->name }}</option>
                                    @endforeach
                                </select>
                                <label for="code">Clubs</label>
                            </div>
                        </div>
                        <div class="col-md-12" id="supplier_id_div" >
                            <div class="form-floating form-floating-outline">
                                <select id="supplier_id" name="supplier_id"
                                    class="form-select select2"
                                    placeholder="Selecione Proveedor">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }} - {{ $supplier->representant }}</option>
                                    @endforeach
                                </select>
                                <label for="code">Proveedor</label>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-floating form-floating-outline">
                                <select id="currency_id" name="currency_id"
                                    class="form-select select2"
                                    placeholder="Selecione Moneda">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} - {{ $currency->symbol }}</option>
                                    @endforeach
                                </select>
                                <label for="code">Moneda</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-floating form-floating-outline">
                                <select id="method_payment_id" name="method_payment_id"
                                    class="form-select select2"
                                    placeholder="Selecione Metodo de Pago">
                                    <option value="">-- Seleccionar --</option>
                                </select>
                                <label for="code">Método de pago</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" id="amount" name="amount" class="form-control" value="" />
                                <label for="amount">Monto</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="event_id" id="event_id" value="{{ $event->id }}">
                    <button type="submit" class="btn btn-primary" id="btnPayOrder">Pagar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
