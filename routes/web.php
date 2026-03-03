<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\SalesStockController;
use App\Http\Controllers\SalesSettlementController;
use App\Http\Controllers\KasbonController;
use App\Http\Controllers\ReportMarginController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCostController;
use App\Http\Controllers\SalesStockSessionController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\CashSaleController;
use App\Http\Controllers\KpiSalesController;
use App\Http\Controllers\VisitScheduleController;
use App\Http\Controllers\SalesFeeController;
use App\Http\Controllers\Master\StoreController;
use App\Http\Controllers\Master\AreaController;
use App\Http\Controllers\SystemBackupController;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USERS (ADMIN + SALES)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | KPI SALES
    |--------------------------------------------------------------------------
    */
    Route::get('/reports/kpi-sales', [KpiSalesController::class, 'index'])
        ->name('reports.kpi.sales');

    /*
    |--------------------------------------------------------------------------
    | JADWAL KUNJUNGAN
    |--------------------------------------------------------------------------
    */
    Route::get('/visit-schedules', [VisitScheduleController::class, 'index'])
        ->name('visit.schedules.index');

    /*
    |--------------------------------------------------------------------------
    | SALES FEES
    |--------------------------------------------------------------------------
    */
    Route::get('/sales-fees', [SalesFeeController::class, 'index'])
        ->name('sales-fees.index');

    /*
    |--------------------------------------------------------------------------
    | CASH SALE
    |--------------------------------------------------------------------------
    */
    Route::get('/cash-sales/create', [CashSaleController::class, 'create'])
        ->name('cash-sales.create');

    Route::post('/cash-sales', [CashSaleController::class, 'store'])
        ->name('cash-sales.store');

    /*
    |--------------------------------------------------------------------------
    | STORES
    |--------------------------------------------------------------------------
    */
    Route::get('/stores', [StoreController::class, 'index'])
        ->name('stores.index');

    Route::get('/stores/create', [StoreController::class, 'create'])
        ->name('stores.create');

    Route::get('/stores/{store}/edit', [StoreController::class, 'edit'])
    ->name('stores.edit');

    Route::put('/stores/{store}', [StoreController::class, 'update'])
    ->name('stores.update');    

    Route::post('/stores', [StoreController::class, 'store'])
        ->name('stores.store');

    /*
    |--------------------------------------------------------------------------
    | AREAS
    |--------------------------------------------------------------------------
    */
    Route::get('/areas', [AreaController::class, 'index'])
        ->name('areas.index');


    /*
    |--------------------------------------------------------------------------
    | SALES STOCK
    |--------------------------------------------------------------------------
    */
    Route::get('/sales-stock', [SalesStockController::class, 'index'])
        ->name('sales.stock');

    Route::get('/sales-stock/{product}', [SalesStockController::class, 'show'])
        ->name('sales.stock.show');

    Route::get('/sales-stock-warehouse-in', [SalesStockController::class, 'createWarehouseIn'])
        ->name('sales.stock.warehouse_in.create');

    Route::post('/sales-stock-warehouse-in', [SalesStockController::class, 'storeWarehouseIn'])
        ->name('sales.stock.warehouse_in.store');

    /*
    |--------------------------------------------------------------------------
    | SALES STOCK SESSION
    |--------------------------------------------------------------------------
    */
    Route::get('/sales-stock-sessions', [SalesStockSessionController::class, 'index'])
        ->name('sales-stock-sessions.index');

    Route::get('/sales-stock-sessions/create', [SalesStockSessionController::class, 'create'])
        ->name('sales-stock-sessions.create');

    Route::post('/sales-stock-sessions', [SalesStockSessionController::class, 'store'])
        ->name('sales-stock-sessions.store');

    Route::get('/sales-stock-sessions/{id}', [SalesStockSessionController::class, 'show'])
        ->name('sales-stock-sessions.show');

    Route::get('/sales-stock-sessions/{id}/edit', [SalesStockSessionController::class, 'edit'])
        ->name('sales-stock-sessions.edit');

    Route::put('/sales-stock-sessions/{id}', [SalesStockSessionController::class, 'update'])
        ->name('sales-stock-sessions.update');

    Route::get('/sales-stock-sessions/{id}/close', [SalesStockSessionController::class, 'closeForm'])
        ->name('sales-stock-sessions.close.form');

    Route::post('/sales-stock-sessions/{id}/close', [SalesStockSessionController::class, 'close'])
        ->name('sales-stock-sessions.close');

    /*
    |--------------------------------------------------------------------------
    | SALES SETTLEMENT
    |--------------------------------------------------------------------------
    */
    Route::get('/sales-settlements', [SalesSettlementController::class, 'index'])
        ->name('sales.settlements.index');

    Route::get('/sales-settlements/{user}/{date}', [SalesSettlementController::class, 'show'])
        ->name('sales.settlements.show');

    Route::post('/sales-settlements/setor', [SalesSettlementController::class, 'setor'])
        ->name('sales.settlements.setor');

    Route::post('/sales-settlements/{settlement}/costs', [SalesSettlementController::class, 'storeCost'])
        ->name('sales.settlements.costs.store');

    /*
    |--------------------------------------------------------------------------
    | KASBON
    |--------------------------------------------------------------------------
    */
    Route::get('/kasbons', [KasbonController::class, 'index'])
        ->name('kasbons.index');

    /*
    |--------------------------------------------------------------------------
    | PIUTANG TOKO
    |--------------------------------------------------------------------------
    */
    Route::get('/receivables', [ReceivableController::class, 'index'])
        ->name('receivables.index');

    Route::post('/receivables/{receivable}/pay', [ReceivableController::class, 'pay'])
        ->name('receivables.pay');

    /*
    |--------------------------------------------------------------------------
    | VISIT FLOW
    |--------------------------------------------------------------------------
    */

    Route::get('/visits/choose-sales', [VisitController::class, 'chooseSales'])
    ->name('visits.choose_sales');
    
    Route::get('/visits', [VisitController::class, 'index'])
        ->name('visits.index');

    Route::get('/stores/{store}/visit/create', [VisitController::class, 'create'])
        ->name('visits.create');

    Route::get('/visits/{visit}/edit', [VisitController::class, 'edit'])
        ->name('visits.edit');

    Route::post('/visits/{visit}/submit', [VisitController::class, 'submit'])
        ->name('visits.submit');

    Route::post('/visits/{visit}/add-product', [VisitController::class, 'addProduct'])
        ->name('visits.add_product');

    Route::get('/visits/{visit}', [VisitController::class, 'show'])
        ->name('visits.show');

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| ADMIN ONLY
|--------------------------------------------------------------------------
*/

Route::middleware(['auth','admin'])->group(function () {

    Route::post('/sales-fees/pay', [SalesFeeController::class, 'pay'])
        ->name('sales-fees.pay');

    Route::post('/sales-settlements/{settlement}/reopen', [SalesSettlementController::class, 'reopen'])
    ->name('sales-settlements.reopen');

    Route::post('/visits/{visit}/approve', [VisitController::class, 'approve'])
        ->name('visits.approve');

    Route::post('/visits/{visit}/reopen', [VisitController::class, 'reopen'])
    ->name('visits.reopen');    

    Route::delete('/visits/{visit}', [VisitController::class, 'destroy'])
        ->name('visits.destroy');

    Route::get('/stores/{store}/prices', [StoreController::class, 'editPrices'])
        ->name('stores.prices.edit');

    Route::post('/stores/{store}/prices', [StoreController::class, 'updatePrices'])
        ->name('stores.prices.update');

    Route::delete('/stores/{store}', [StoreController::class, 'destroy'])
        ->name('stores.destroy');

    Route::get('/areas/create', [AreaController::class, 'create'])
        ->name('areas.create');

    Route::get('/areas/{area}/edit', [AreaController::class, 'edit'])
    ->name('areas.edit');

    Route::put('/areas/{area}', [AreaController::class, 'update'])
    ->name('areas.update');    

    Route::post('/areas', [AreaController::class, 'store'])
    ->name('areas.store');
    
    Route::delete('/areas/{area}', [AreaController::class, 'destroy'])
    ->name('areas.destroy');

    Route::resource('products', ProductController::class);

    Route::get('/products/{product}/costs', [ProductCostController::class, 'index'])
        ->name('products.costs.index');

    Route::post('/products/{product}/costs', [ProductCostController::class, 'store'])
        ->name('products.costs.store');

    Route::get('/kasbons/create', [KasbonController::class, 'create'])
        ->name('kasbons.create');

    Route::post('/kasbons', [KasbonController::class, 'store'])
        ->name('kasbons.store');

    Route::get('/kasbons/{kasbon}/edit', [KasbonController::class, 'edit'])
        ->name('kasbons.edit');

    Route::put('/kasbons/{kasbon}', [KasbonController::class, 'update'])
        ->name('kasbons.update');

    Route::delete('/kasbons/{kasbon}', [KasbonController::class, 'destroy'])
        ->name('kasbons.destroy');

    Route::get('/reports/margin', [ReportMarginController::class, 'index'])
        ->name('reports.margin.index');

    Route::get('/reports/margin-products', [ReportMarginController::class, 'products'])
        ->name('reports.margin.products');

    Route::get('/reports/margin-stores', [ReportMarginController::class, 'stores'])
        ->name('reports.margin.stores');

    Route::get('/reports/kpi', [ReportMarginController::class, 'kpi'])
        ->name('reports.kpi');
        /*
|--------------------------------------------------------------------------
| STOCK OPNAME (ADMIN ONLY)
|--------------------------------------------------------------------------
*/

Route::get('/stores/{store}/stock-opname', [\App\Http\Controllers\StockOpnameController::class, 'create'])
    ->name('stock-opnames.create');

Route::post('/stores/{store}/stock-opname', [\App\Http\Controllers\StockOpnameController::class, 'store'])
    ->name('stock-opnames.store');

Route::get('/stock-opnames/{stockOpname}', [\App\Http\Controllers\StockOpnameController::class, 'show'])
    ->name('stock-opnames.show');


    /*
    |--------------------------------------------------------------------------
    | SYSTEM BACKUP
    |--------------------------------------------------------------------------
    */
    Route::get('/system/backups', [SystemBackupController::class, 'index'])
        ->name('system.backups.index');

    Route::post('/system/backups', [SystemBackupController::class, 'store'])
        ->name('system.backups.store');

    Route::get('/system/backups/download/{filename}', [SystemBackupController::class, 'download'])
        ->name('system.backups.download');

    Route::post('/system/backups/restore/{filename}', [SystemBackupController::class, 'restore'])
    ->name('system.backups.restore');
        
});

require __DIR__.'/auth.php';