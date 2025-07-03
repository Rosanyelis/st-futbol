        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
            <div class="app-brand demo">
                <a href="{{ route('dashboard') }}" class="app-brand-link">
                    <span class="app-brand-logo demo">
                        <span style="color: var(--bs-primary)">
                            <img src="{{ asset('assets/img/logo-isotipo.png') }}"  width="3%" alt="" >
                        </span>
                    </span>
                </a>
                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                        d="M8.47365 11.7183C8.11707 12.0749 8.11707 12.6531 8.47365 13.0097L12.071 16.607C12.4615 16.9975 12.4615 17.6305 12.071 18.021C11.6805 18.4115 11.0475 18.4115 10.657 18.021L5.83009 13.1941C5.37164 12.7356 5.37164 11.9924 5.83009 11.5339L10.657 6.707C11.0475 6.31653 11.6805 6.31653 12.071 6.707C12.4615 7.09747 12.4615 7.73053 12.071 8.121L8.47365 11.7183Z"
                        fill-opacity="0.9" />
                        <path
                        d="M14.3584 11.8336C14.0654 12.1266 14.0654 12.6014 14.3584 12.8944L18.071 16.607C18.4615 16.9975 18.4615 17.6305 18.071 18.021C17.6805 18.4115 17.0475 18.4115 16.657 18.021L11.6819 13.0459C11.3053 12.6693 11.3053 12.0587 11.6819 11.6821L16.657 6.707C17.0475 6.31653 17.6805 6.31653 18.071 6.707C18.4615 7.09747 18.4615 7.73053 18.071 8.121L14.3584 11.8336Z"
                        fill-opacity="0.4" />
                    </svg>
                </a>
            </div>
            <div class="menu-inner-shadow"></div>
            <ul class="menu-inner py-1">
                <!-- Dashboards -->
                 <li class="menu-item
                    @if (Route::currentRouteName() == 'bussines.index' ||
                        Route::currentRouteName() == 'bussines.create' ||
                        Route::currentRouteName() == 'bussines.edit' ||
                        Route::currentRouteName() == 'bussines.show' ||
                        Route::currentRouteName() == 'bussines.currencies' ||
                        Route::currentRouteName() == 'bussines.history') active @endif
                    ">
                    <a href="{{ route('bussines.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-calendar-fill"></i>
                        <div data-i18n="Negocios">Negocios</div>
                    </a>
                </li>
                <li class="menu-item
                    @if (Route::currentRouteName() == 'event.index' ||
                        Route::currentRouteName() == 'event.create' ||
                        Route::currentRouteName() == 'event.edit' ||
                        Route::currentRouteName() == 'event.show' ||
                        Route::currentRouteName() == 'event.currencies' ||
                        Route::currentRouteName() == 'event.history') active @endif
                    ">
                    <a href="{{ route('event.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-calendar-fill"></i>
                        <div data-i18n="Eventos">Eventos</div>
                    </a>
                </li>
                <li class="menu-item
                    @if (Route::currentRouteName() == 'club.index' ||   
                        Route::currentRouteName() == 'club.create' ||
                        Route::currentRouteName() == 'club.edit' ||
                        Route::currentRouteName() == 'club.show') active @endif
                    ">
                    <a href="{{ route('club.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-group-line"></i>
                        <div data-i18n="Clubs">Clubs</div>
                    </a>
                </li>
                <li class="menu-item
                    @if (Route::currentRouteName() == 'category-income.index' ||
                        Route::currentRouteName() == 'category-income.create' ||
                        Route::currentRouteName() == 'category-income.edit' ||
                        Route::currentRouteName() == 'category-income.show') active open @endif
                    ">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-money-cny-circle-fill"></i>
                        <div data-i18n="Ingresos">Ingresos</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item 
                            @if (Route::currentRouteName() == 'category-income.index' ||
                                Route::currentRouteName() == 'category-income.create' ||
                                Route::currentRouteName() == 'category-income.edit' ||
                                Route::currentRouteName() == 'category-income.show') active @endif"
                            >
                            <a href="{{ route('category-income.index') }}" class="menu-link">
                                <div data-i18n="Categorias">Categorias</div>
                            </a>
                        </li>
                        
                    </ul>
                </li>
                <li class="menu-item
                    @if (Route::currentRouteName() == 'category-payment-method.index' ||
                        Route::currentRouteName() == 'category-payment-method.create' ||
                        Route::currentRouteName() == 'category-payment-method.edit' ||
                        Route::currentRouteName() == 'category-payment-method.show' ||
                        Route::currentRouteName() == 'entity.index' ||
                        Route::currentRouteName() == 'entity.create' ||
                        Route::currentRouteName() == 'entity.edit' ||
                        Route::currentRouteName() == 'entity.show' ||
                        Route::currentRouteName() == 'method-payment.index' ||
                        Route::currentRouteName() == 'method-payment.create' ||
                        Route::currentRouteName() == 'method-payment.edit' ||
                        Route::currentRouteName() == 'method-payment.show') active open @endif
                    ">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-currency-fill"></i>
                        <div data-i18n="Metodos de Pago">Metodos de Pago</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item
                            @if (Route::currentRouteName() == 'category-payment-method.index' ||
                                Route::currentRouteName() == 'category-payment-method.create' ||
                                Route::currentRouteName() == 'category-payment-method.edit' ||
                                Route::currentRouteName() == 'category-payment-method.show') active @endif
                            ">
                            <a href="{{ route('category-payment-method.index') }}" class="menu-link">
                                <div data-i18n="Categorias">Categorias</div>
                            </a>
                        </li>
                        <li class="menu-item
                            @if (Route::currentRouteName() == 'entity.index' ||
                                Route::currentRouteName() == 'entity.create' ||
                                Route::currentRouteName() == 'entity.edit' ||
                                Route::currentRouteName() == 'entity.show') active @endif
                            ">
                            <a href="{{ route('entity.index') }}" class="menu-link">
                                <div data-i18n="Entidades">Entidades</div>
                            </a>
                        </li>
                        <li class="menu-item
                            @if (Route::currentRouteName() == 'method-payment.index' ||
                                Route::currentRouteName() == 'method-payment.create' ||
                                Route::currentRouteName() == 'method-payment.edit' ||
                                Route::currentRouteName() == 'method-payment.show') active @endif
                            ">
                            <a href="{{ route('method-payment.index') }}" class="menu-link">
                                <div data-i18n="Metodos de Pago">Metodos de Pago</div>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-item
                    @if (Route::currentRouteName() == 'category-supplier.index' ||
                        Route::currentRouteName() == 'category-supplier.create' ||
                        Route::currentRouteName() == 'category-supplier.edit' ||
                        Route::currentRouteName() == 'category-supplier.show' ||
                        Route::currentRouteName() == 'subcategory-supplier.index' ||
                        Route::currentRouteName() == 'subcategory-supplier.create' ||
                        Route::currentRouteName() == 'subcategory-supplier.edit' ||
                        Route::currentRouteName() == 'subcategory-supplier.show' ||
                        Route::currentRouteName() == 'supplier.index' ||
                        Route::currentRouteName() == 'supplier.create' ||
                        Route::currentRouteName() == 'supplier.edit' ||
                        Route::currentRouteName() == 'supplier.show') active open @endif
                    ">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-group-3-fill"></i>
                        <div data-i18n="Proveedores">Proveedores</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item 
                            @if (Route::currentRouteName() == 'category-supplier.index' ||
                                Route::currentRouteName() == 'category-supplier.create' ||
                                Route::currentRouteName() == 'category-supplier.edit' ||
                                Route::currentRouteName() == 'category-supplier.show') active @endif
                            ">
                            <a href="{{ route('category-supplier.index') }}" class="menu-link">
                                <div data-i18n="Categorias">Categorias</div>
                            </a>
                        </li>
                        <li class="menu-item 
                            @if (Route::currentRouteName() == 'subcategory-supplier.index' ||
                                Route::currentRouteName() == 'subcategory-supplier.create' ||
                                Route::currentRouteName() == 'subcategory-supplier.edit' ||
                                Route::currentRouteName() == 'subcategory-supplier.show') active @endif
                            ">
                            <a href="{{ route('subcategory-supplier.index') }}" class="menu-link">
                                <div data-i18n="Subcategorias">Subcategorias</div>
                            </a>
                        </li>
                        <li class="menu-item 
                            @if (Route::currentRouteName() == 'supplier.index' ||
                                Route::currentRouteName() == 'supplier.create' ||
                                Route::currentRouteName() == 'supplier.edit' ||
                                Route::currentRouteName() == 'supplier.show') active @endif
                            "
                            >
                            <a href="{{ route('supplier.index') }}" class="menu-link">
                                <div data-i18n="Proveedores">Proveedores</div>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-item
                    @if (Route::currentRouteName() == 'category-expense.index' ||
                        Route::currentRouteName() == 'category-expense.create' ||
                        Route::currentRouteName() == 'category-expense.edit' ||
                        Route::currentRouteName() == 'category-expense.show' ||
                        Route::currentRouteName() == 'subcategory-expense.index' ||
                        Route::currentRouteName() == 'subcategory-expense.create' ||
                        Route::currentRouteName() == 'subcategory-expense.edit' ||
                        Route::currentRouteName() == 'subcategory-expense.show' ||
                        Route::currentRouteName() == 'expense.index' ||
                        Route::currentRouteName() == 'expense.create' ||
                        Route::currentRouteName() == 'expense.edit' ||
                        Route::currentRouteName() == 'expense.show') active open @endif
                    ">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-exchange-2-fill"></i>
                        <div data-i18n="Gastos">Gastos</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item 
                            @if (Route::currentRouteName() == 'category-expense.index' ||
                                Route::currentRouteName() == 'category-expense.create' ||
                                Route::currentRouteName() == 'category-expense.edit' ||
                                Route::currentRouteName() == 'category-expense.show') active @endif
                            "
                            >
                            <a href="{{ route('category-expense.index') }}" class="menu-link">
                                <div data-i18n="Categorias">Categorias</div>
                            </a>
                        </li>
                        <li class="menu-item 
                            @if (Route::currentRouteName() == 'subcategory-expense.index' ||
                                Route::currentRouteName() == 'subcategory-expense.create' ||
                                Route::currentRouteName() == 'subcategory-expense.edit' ||
                                Route::currentRouteName() == 'subcategory-expense.show') active @endif
                            "
                            >
                            <a href="{{ route('subcategory-expense.index') }}" class="menu-link">
                                <div data-i18n="Subcategorias">Subcategorias</div>
                            </a>
                        </li>
                        <li class="menu-item 
                            @if (Route::currentRouteName() == 'expense.index' ||
                                Route::currentRouteName() == 'expense.create' ||
                                Route::currentRouteName() == 'expense.edit' ||
                                Route::currentRouteName() == 'expense.show') active @endif
                            "
                            >
                                <a href="{{ route('expense.index') }}" class="menu-link">
                                <div data-i18n="Gastos">Gastos</div>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-item
                    @if (Route::currentRouteName() == 'account-receivable.index' ||
                        Route::currentRouteName() == 'account-receivable.create' ||
                        Route::currentRouteName() == 'account-receivable.edit' ||
                        Route::currentRouteName() == 'account-receivable.show') active @endif
                    ">
                    <a href="{{ route('account-receivable.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-coin-fill"></i>
                        <div data-i18n="Cuentas por Cobrar">Cuentas por Cobrar</div>
                    </a>
                </li>
                <li class="menu-item
                    @if (Route::currentRouteName() == 'account-payable.index' ||
                        Route::currentRouteName() == 'account-payable.create' ||
                        Route::currentRouteName() == 'account-payable.edit' ||
                        Route::currentRouteName() == 'account-payable.show') active @endif
                    ">
                    <a href="{{ route('account-payable.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-coin-fill"></i>
                        <div data-i18n="Cuentas por Pagar">Cuentas por Pagar</div>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="javascript:void(0);" class="menu-link">
                        <i class="menu-icon tf-icons ri-swap-3-fill"></i>
                        <div data-i18n="Cambio de Divisas">Cambio de Divisas</div>
                    </a>
                </li>
                <li class="menu-item
                    @if (Route::currentRouteName() == 'currency.index' ||
                    Route::currentRouteName() == 'currency.create' ||
                        Route::currentRouteName() == 'currency.edit' ||
                        Route::currentRouteName() == 'currency.show' ||
                        Route::currentRouteName() == 'country.index' ||
                        Route::currentRouteName() == 'country.create' ||
                        Route::currentRouteName() == 'country.edit' ||
                        Route::currentRouteName() == 'country.show' ||
                        Route::currentRouteName() == 'province.index' ||
                        Route::currentRouteName() == 'province.create' ||
                        Route::currentRouteName() == 'province.edit' ||
                        Route::currentRouteName() == 'province.show' ||
                        Route::currentRouteName() == 'city.index' ||
                        Route::currentRouteName() == 'city.create' ||
                        Route::currentRouteName() == 'city.edit' ||
                        Route::currentRouteName() == 'city.show') active open @endif
                    ">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-settings-2-fill"></i>
                        <div data-i18n="Configuraciones">Configuraciones</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item 
                            @if (Route::currentRouteName() == 'currency.index' ||
                                Route::currentRouteName() == 'currency.create' ||
                                Route::currentRouteName() == 'currency.edit' ||
                                Route::currentRouteName() == 'currency.show') active @endif
                            "
                            >
                            <a href="{{ route('currency.index') }}" class="menu-link">
                                <div data-i18n="Monedas">Monedas</div>
                            </a>
                        </li>
                        <li class="menu-item 
                            @if (Route::currentRouteName() == 'country.index' ||
                                Route::currentRouteName() == 'country.create' ||
                                Route::currentRouteName() == 'country.edit' ||
                                Route::currentRouteName() == 'country.show') active @endif
                            "
                            >
                            <a href="{{ route('country.index') }}" class="menu-link">
                                <div data-i18n="Paises">Paises</div>
                            </a>
                        </li>
                        <li class="menu-item 
                            @if (Route::currentRouteName() == 'province.index' ||
                                Route::currentRouteName() == 'province.create' ||
                                Route::currentRouteName() == 'province.edit' ||
                                Route::currentRouteName() == 'province.show') active @endif
                            "
                            >
                                <a href="{{ route('province.index') }}" class="menu-link">
                                <div data-i18n="Provincias">Provincias</div>
                            </a>
                        </li>
                        <li class="menu-item 
                            @if (Route::currentRouteName() == 'city.index' ||
                                Route::currentRouteName() == 'city.create' ||
                                Route::currentRouteName() == 'city.edit' ||
                                Route::currentRouteName() == 'city.show') active @endif
                            "
                            >
                                <a href="{{ route('city.index') }}" class="menu-link">
                                <div data-i18n="Ciudades">Ciudades</div>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-item
                    ">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-file-pdf-2-fill"></i>
                        <div data-i18n="Informes">Informes</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item ">
                            <a href="javascript:void(0);" class="menu-link">
                                <div data-i18n="Estado de Ingresos">Estado de Ingresos</div>
                            </a>
                        </li>
                        <li class="menu-item ">
                            <a href="javascript:void(0);" class="menu-link">
                                <div data-i18n="Estado de Egresos">Estado de Egresos</div>
                            </a>
                        </li>
                        
                        <li class="menu-item ">
                            <a href="javascript:void(0);" class="menu-link">
                                <div data-i18n="Estado de Pres. Comparativos">Estado de Pres. Comparativos</div>
                            </a>
                        </li>
                        <li class="menu-item ">
                            <a href="javascript:void(0);" class="menu-link">
                                <div data-i18n="Estado de Ctas. por Cobrar">Estado de Ctas. por Cobrar</div>
                            </a>
                        </li>
                        <li class="menu-item ">
                            <a href="javascript:void(0);" class="menu-link">
                                <div data-i18n="Estado de Res. por Evento y Moneda">Estado de Res. por Evento y Moneda</div>
                            </a>
                        </li>
                        
                        <li class="menu-item ">
                            <a href="javascript:void(0);" class="menu-link">
                                <div data-i18n="Estado de Res. General y Moneda">Estado de Res. General y Moneda</div>
                            </a>
                        </li>
                        <li class="menu-item ">
                            <a href="javascript:void(0);" class="menu-link">
                                <div data-i18n="Estado de Res. Cuentas">Estado de Res. Cuentas</div>
                            </a>
                        </li>
                        
                    </ul>
                </li>
            </ul>
            
        </aside>
