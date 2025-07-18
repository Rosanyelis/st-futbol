<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BussinesController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\MethodPaymentController;
use App\Http\Controllers\AccountPayableController;
use App\Http\Controllers\CategoryIncomeController;
use App\Http\Controllers\CategoryExpenseController;
use App\Http\Controllers\CategorySupplierController;
use App\Http\Controllers\AccountReceivableController;
use App\Http\Controllers\SubcategoryExpenseController;
use App\Http\Controllers\SubcategorySupplierController;
use App\Http\Controllers\CategoryMethodPaymentController;
use App\Http\Controllers\CategoryPaymentMethodController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('event.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    # Negocio
    Route::get('/negocio', [BussinesController::class, 'index'])->name('bussines.index');
    Route::get('/negocio/history-json', [BussinesController::class, 'historyJson'])->name('bussines.historyJson');
    Route::post('/negocio/store-transaction', [BussinesController::class, 'storeTransaction'])->name('bussines.storeTransaction');
    Route::get('/negocio/metodo-pago/{currencyId}', [BussinesController::class, 'paymentMethods'])->name('bussines.paymentMethods');
    Route::get('/negocio/currencies', [BussinesController::class, 'currencies'])->name('bussines.currencies');
    Route::get('/negocio/clubs-by-category/{categoryIncomeId}', [BussinesController::class, 'getClubsByCategory'])->name('bussines.getClubsByCategory');
    Route::get('/negocio/expenses-by-category/{categoryEgressId}', [BussinesController::class, 'getExpensesByCategory'])->name('bussines.getExpensesByCategory');
    Route::get('/negocio/suppliers-by-category/{categoryEgressId}', [BussinesController::class, 'getSuppliersByCategory'])->name('bussines.getSuppliersByCategory');
    Route::get('/negocio/{id}/edit-history', [BussinesController::class, 'editHistory'])->name('bussines.history.edit');
    Route::post('/negocio/{id}/update-history', [BussinesController::class, 'updateHistory'])->name('bussines.history.update');
    Route::get('/negocio/{id}/destroy-history', [BussinesController::class, 'destroyHistory'])->name('bussines.history.destroy');
    # Eventos
    Route::get('/eventos', [EventController::class, 'index'])->name('event.index');
    Route::get('/eventos/create', [EventController::class, 'create'])->name('event.create');
    Route::post('/eventos', [EventController::class, 'store'])->name('event.store');
    Route::get('/eventos/{id}/edit', [EventController::class, 'edit'])->name('event.edit');
    Route::put('/eventos/{id}/update', [EventController::class, 'update'])->name('event.update');
    Route::get('/eventos/{id}/destroy', [EventController::class, 'destroy'])->name('event.destroy');
    Route::get('/eventos/{id}/history', [EventController::class, 'history'])->name('event.history');
    Route::post('/eventos/{id}/store-transaction', [EventController::class, 'storeTransaction'])->name('event.storeTransaction');
    Route::get('/eventos/{id}/history-json', [EventController::class, 'historyJson'])->name('event.historyJson');
    Route::get('/eventos/metodo-pago/{currencyId}', [EventController::class, 'paymentMethods'])->name('event.paymentMethods');
    Route::get('/eventos/currencies', [EventController::class, 'currencies'])->name('event.currencies');
    Route::get('/eventos/clubs-by-category/{categoryIncomeId}', [EventController::class, 'getClubsByCategory'])->name('event.getClubsByCategory');
    Route::get('/eventos/expenses-by-category/{categoryEgressId}', [EventController::class, 'getExpensesByCategory'])->name('event.getExpensesByCategory');
    Route::get('/eventos/suppliers-by-category/{categoryEgressId}', [EventController::class, 'getSuppliersByCategory'])->name('event.getSuppliersByCategory');
    Route::get('/eventos/{id}/edit-history', [EventController::class, 'editHistory'])->name('event.history.edit');
    Route::post('/eventos/{id}/update-history', [EventController::class, 'updateHistory'])->name('event.history.update');
    Route::get('/eventos/{id}/destroy-history', [EventController::class, 'destroyHistory'])->name('event.history.destroy');

    # Categorías de proveedores
    Route::get('/categorias-proveedores', [CategorySupplierController::class, 'index'])->name('category-supplier.index');
    Route::get('/categorias-proveedores/create', [CategorySupplierController::class, 'create'])->name('category-supplier.create');
    Route::post('/categorias-proveedores', [CategorySupplierController::class, 'store'])->name('category-supplier.store');
    Route::get('/categorias-proveedores/{id}/edit', [CategorySupplierController::class, 'edit'])->name('category-supplier.edit');
    Route::put('/categorias-proveedores/{id}/update', [CategorySupplierController::class, 'update'])->name('category-supplier.update');
    Route::get('/categorias-proveedores/{id}/destroy', [CategorySupplierController::class, 'destroy'])->name('category-supplier.destroy');
    # Subcategorías de proveedores
    Route::get('/subcategorias-proveedores', [SubcategorySupplierController::class, 'index'])->name('subcategory-supplier.index');
    Route::get('/subcategorias-proveedores/create', [SubcategorySupplierController::class, 'create'])->name('subcategory-supplier.create');
    Route::post('/subcategorias-proveedores', [SubcategorySupplierController::class, 'store'])->name('subcategory-supplier.store');
    Route::get('/subcategorias-proveedores/{id}/edit', [SubcategorySupplierController::class, 'edit'])->name('subcategory-supplier.edit');
    Route::put('/subcategorias-proveedores/{id}/update', [SubcategorySupplierController::class, 'update'])->name('subcategory-supplier.update');
    Route::get('/subcategorias-proveedores/{id}/destroy', [SubcategorySupplierController::class, 'destroy'])->name('subcategory-supplier.destroy');
    # Proveedores
    Route::get('/proveedores', [SupplierController::class, 'index'])->name('supplier.index');
    Route::get('/proveedores/create', [SupplierController::class, 'create'])->name('supplier.create');
    Route::post('/proveedores', [SupplierController::class, 'store'])->name('supplier.store');
    Route::get('/proveedores/{id}/edit', [SupplierController::class, 'edit'])->name('supplier.edit');
    Route::put('/proveedores/{id}/update', [SupplierController::class, 'update'])->name('supplier.update');
    Route::get('/proveedores/{id}/destroy', [SupplierController::class, 'destroy'])->name('supplier.destroy');
    Route::post('/proveedores/get-subcategory-suppliers', [SupplierController::class, 'getSubcategorySuppliers'])->name('supplier.get-subcategory-suppliers');
    # Categorías de ingresos
    Route::get('/categorias-ingresos', [CategoryIncomeController::class, 'index'])->name('category-income.index');
    Route::get('/categorias-ingresos/create', [CategoryIncomeController::class, 'create'])->name('category-income.create');
    Route::post('/categorias-ingresos', [CategoryIncomeController::class, 'store'])->name('category-income.store');
    Route::get('/categorias-ingresos/{id}/edit', [CategoryIncomeController::class, 'edit'])->name('category-income.edit');
    Route::put('/categorias-ingresos/{id}/update', [CategoryIncomeController::class, 'update'])->name('category-income.update');
    Route::get('/categorias-ingresos/{id}/destroy', [CategoryIncomeController::class, 'destroy'])->name('category-income.destroy');
    # categoria de metodo de pago
    Route::get('/categorias-metodo-pago', [CategoryMethodPaymentController::class, 'index'])->name('category-payment-method.index');
    Route::get('/categorias-metodo-pago/create', [CategoryMethodPaymentController::class, 'create'])->name('category-payment-method.create');
    Route::post('/categorias-metodo-pago', [CategoryMethodPaymentController::class, 'store'])->name('category-payment-method.store');
    Route::get('/categorias-metodo-pago/{id}/edit', [CategoryMethodPaymentController::class, 'edit'])->name('category-payment-method.edit');
    Route::put('/categorias-metodo-pago/{id}/update', [CategoryMethodPaymentController::class, 'update'])->name('category-payment-method.update');
    Route::get('/categorias-metodo-pago/{id}/destroy', [CategoryMethodPaymentController::class, 'destroy'])->name('category-payment-method.destroy');
    # entidades
    Route::get('/entidades', [EntityController::class, 'index'])->name('entity.index');
    Route::get('/entidades/create', [EntityController::class, 'create'])->name('entity.create');
    Route::post('/entidades', [EntityController::class, 'store'])->name('entity.store');
    Route::get('/entidades/{id}/edit', [EntityController::class, 'edit'])->name('entity.edit');
    Route::put('/entidades/{id}/update', [EntityController::class, 'update'])->name('entity.update');
    Route::get('/entidades/{id}/destroy', [EntityController::class, 'destroy'])->name('entity.destroy');
    # metodos de pago
    Route::get('/metodos-pago', [MethodPaymentController::class, 'index'])->name('method-payment.index');
    Route::get('/metodos-pago/create', [MethodPaymentController::class, 'create'])->name('method-payment.create');
    Route::post('/metodos-pago', [MethodPaymentController::class, 'store'])->name('method-payment.store');
    Route::get('/metodos-pago/{id}/edit', [MethodPaymentController::class, 'edit'])->name('method-payment.edit');
    Route::put('/metodos-pago/{id}/update', [MethodPaymentController::class, 'update'])->name('method-payment.update');
    Route::get('/metodos-pago/{id}/destroy', [MethodPaymentController::class, 'destroy'])->name('method-payment.destroy');
    # categorias de gastos
    Route::get('/categorias-gastos', [CategoryExpenseController::class, 'index'])->name('category-expense.index');
    Route::get('/categorias-gastos/create', [CategoryExpenseController::class, 'create'])->name('category-expense.create');
    Route::post('/categorias-gastos', [CategoryExpenseController::class, 'store'])->name('category-expense.store');
    Route::get('/categorias-gastos/{id}/edit', [CategoryExpenseController::class, 'edit'])->name('category-expense.edit');
    Route::put('/categorias-gastos/{id}/update', [CategoryExpenseController::class, 'update'])->name('category-expense.update');
    Route::get('/categorias-gastos/{id}/destroy', [CategoryExpenseController::class, 'destroy'])->name('category-expense.destroy');
    # subcategorias de gastos
    Route::get('/subcategorias-gastos', [SubcategoryExpenseController::class, 'index'])->name('subcategory-expense.index');
    Route::get('/subcategorias-gastos/create', [SubcategoryExpenseController::class, 'create'])->name('subcategory-expense.create');
    Route::post('/subcategorias-gastos', [SubcategoryExpenseController::class, 'store'])->name('subcategory-expense.store');
    Route::get('/subcategorias-gastos/{id}/edit', [SubcategoryExpenseController::class, 'edit'])->name('subcategory-expense.edit');
    Route::put('/subcategorias-gastos/{id}/update', [SubcategoryExpenseController::class, 'update'])->name('subcategory-expense.update');
    Route::get('/subcategorias-gastos/{id}/destroy', [SubcategoryExpenseController::class, 'destroy'])->name('subcategory-expense.destroy');
    # gastos
    Route::get('/gastos', [ExpenseController::class, 'index'])->name('expense.index');
    Route::get('/gastos/create', [ExpenseController::class, 'create'])->name('expense.create');
    Route::post('/gastos', [ExpenseController::class, 'store'])->name('expense.store');
    Route::get('/gastos/{id}/edit', [ExpenseController::class, 'edit'])->name('expense.edit');
    Route::put('/gastos/{id}/update', [ExpenseController::class, 'update'])->name('expense.update');
    Route::get('/gastos/{id}/destroy', [ExpenseController::class, 'destroy'])->name('expense.destroy');
    Route::post('/gastos/get-subcategory-expenses', [ExpenseController::class, 'getSubcategoryExpenses'])->name('expense.get-subcategory-expenses');
    # Clubs
    Route::get('/clubs', [ClubController::class, 'index'])->name('club.index');
    Route::get('/clubs/create', [ClubController::class, 'create'])->name('club.create');
    Route::post('/clubs', [ClubController::class, 'store'])->name('club.store');
    Route::get('/clubs/{id}/show', [ClubController::class, 'show'])->name('club.show');
    Route::get('/clubs/{id}/edit', [ClubController::class, 'edit'])->name('club.edit');
    Route::put('/clubs/{id}/update', [ClubController::class, 'update'])->name('club.update');
    Route::get('/clubs/{id}/destroy', [ClubController::class, 'destroy'])->name('club.destroy');
    Route::post('/clubs/get-provinces', [ClubController::class, 'getProvinces'])->name('club.get-provinces');
    Route::post('/clubs/get-cities', [ClubController::class, 'getCities'])->name('club.get-cities');
    Route::post('/clubs/get-suppliers', [ClubController::class, 'getSuppliersByEvent'])->name('club.get-suppliers');
    Route::get('/clubs/{club}/payments/{payment}/show', [ClubController::class, 'showPayment'])->name('club.payments.show');
    # Monedas
    Route::get('/monedas', [CurrencyController::class, 'index'])->name('currency.index');
    Route::get('/monedas/create', [CurrencyController::class, 'create'])->name('currency.create');
    Route::post('/monedas', [CurrencyController::class, 'store'])->name('currency.store');
    Route::get('/monedas/{id}/edit', [CurrencyController::class, 'edit'])->name('currency.edit');
    Route::put('/monedas/{id}/update', [CurrencyController::class, 'update'])->name('currency.update');
    Route::get('/monedas/{id}/destroy', [CurrencyController::class, 'destroy'])->name('type-expense.destroy');

    # Paises
    Route::get('/paises', [CountryController::class, 'index'])->name('country.index');
    Route::get('/paises/create', [CountryController::class, 'create'])->name('country.create');
    Route::post('/paises', [CountryController::class, 'store'])->name('country.store');
    Route::get('/paises/{id}/edit', [CountryController::class, 'edit'])->name('country.edit');
    Route::put('/paises/{id}/update', [CountryController::class, 'update'])->name('country.update');
    Route::get('/paises/{id}/destroy', [CountryController::class, 'destroy'])->name('country.destroy');

    # Provincias
    Route::get('/provincias', [ProvinceController::class, 'index'])->name('province.index');
    Route::get('/provincias/create', [ProvinceController::class, 'create'])->name('province.create');
    Route::post('/provincias', [ProvinceController::class, 'store'])->name('province.store');
    Route::get('/provincias/{id}/edit', [ProvinceController::class, 'edit'])->name('province.edit');
    Route::put('/provincias/{id}/update', [ProvinceController::class, 'update'])->name('province.update');
    Route::get('/provincias/{id}/destroy', [ProvinceController::class, 'destroy'])->name('province.destroy');

    # Ciudades
    Route::get('/ciudades', [CityController::class, 'index'])->name('city.index');
    Route::get('/ciudades/create', [CityController::class, 'create'])->name('city.create');
    Route::post('/ciudades', [CityController::class, 'store'])->name('city.store');
    Route::get('/ciudades/{id}/edit', [CityController::class, 'edit'])->name('city.edit');
    Route::put('/ciudades/{id}/update', [CityController::class, 'update'])->name('city.update');
    Route::get('/ciudades/{id}/destroy', [CityController::class, 'destroy'])->name('city.destroy');

    # Cuenta por pagar
    Route::get('/cuenta-por-pagar', [AccountPayableController::class, 'index'])->name('account-payable.index');
    Route::get('/cuenta-por-pagar/json', [AccountPayableController::class, 'indexJson'])->name('account-payable.indexJson');
    Route::post('/cuenta-por-pagar/procesar-pago', [AccountPayableController::class, 'processPayment'])->name('account-payable.processPayment');

    # Cuenta por cobrar
    Route::get('/cuenta-por-cobrar', [AccountReceivableController::class, 'index'])->name('account-receivable.index');
    Route::get('/cuenta-por-cobrar/json', [AccountReceivableController::class, 'indexJson'])->name('account-receivable.indexJson');
    Route::post('/cuenta-por-cobrar/procesar-pago', [AccountReceivableController::class, 'processPayment'])->name('account-receivable.processPayment');

    # reportes
    #lisya de eventos
    Route::get('/reportes/eventos', [ReportController::class, 'listEvent'])->name('report.events');
    # Estado de Ingresos
    Route::get('/reportes/estado-ingresos', [ReportController::class, 'incomeStatement'])->name('report.incomeStatement');
    # Estado de Egresos
    Route::get('/reportes/estado-egresos', [ReportController::class, 'expenseStatement'])->name('report.expenseStatement');
    # Estado de cuentas por cobrar
    Route::get('/reportes/cuentas-por-cobrar', [ReportController::class, 'index'])->name('report.index');
    # Estado de resultado por evento y moneda
    Route::get('/reportes/estado-evento-moneda', [ReportController::class, 'eventCurrencyStatement'])->name('report.eventCurrencyStatement');
    # Estado de resultado general por moneda
    Route::get('/reportes/estado-general', [ReportController::class, 'generalStatement'])->name('report.generalStatement');
    # Estado de resultado de cuentas
    Route::get('/reportes/estado-cuentas', [ReportController::class, 'accountsStatement'])->name('report.accountsStatement');
    # Estado de movimientos general
    Route::get('/reportes/estado-movimientos', [ReportController::class, 'movementsStatement'])->name('report.movementsStatement');

});

require __DIR__.'/auth.php';
