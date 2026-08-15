<?php

use Illuminate\Support\Facades\{Auth, Route};
use App\Http\Controllers\{ClientController, CompanyDetailController, FrontendController, PermissionController, ProductController, QuotationController, RoleController, UserController};

Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);


    Route::get('/', [FrontendController::class, 'index'])->name('index')->middleware('auth');


Route::middleware(['auth'])->group(function () {

    // Administration
    Route::middleware('permission:Administration')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('role', RoleController::class);
        Route::resource('permission', PermissionController::class);
        Route::get('/user/pin', [UserController::class, 'pin'])->name('users.pin');
        Route::post('/user/pin', [UserController::class, 'pinStore'])->name('users.pin_store');
    });

    // Quotations
    Route::middleware('permission:Quotation Management')->group(function () {
        Route::get('quotations/export', [QuotationController::class, 'export'])->name('quotations.export');
        Route::resource('quotations', QuotationController::class);
        Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'generatePDF'])->name('quotations.pdf');
        Route::get('quotations/{quotation}/pdf-preview', [QuotationController::class, 'preview'])->name('quotations.pdf.preview');
        Route::get('/quotations/{quotation}/download', [QuotationController::class, 'download'])->name('quotations.download');
        Route::post('quotations/{quotation}/send', [QuotationController::class, 'sendQuotation'])->name('quotations.send');
        Route::get('quotations-suggestions', [QuotationController::class, 'getSuggestions'])->name('quotations.suggestions');
    });

    // Products
    Route::middleware('permission:Product Management')->group(function () {
        Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
        Route::resource('products', ProductController::class);
        Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    });

    // Clients
    Route::middleware('permission:Client Management')->group(function () {
        Route::get('clients/export', [ClientController::class, 'export'])->name('clients.export');
        Route::resource('clients', ClientController::class);
    });

    // Company
    Route::middleware('permission:Company Management')->group(function () {
        Route::resource('company', CompanyDetailController::class)
            ->parameters(['company' => 'companyDetail']);
        Route::post('company/{companyDetail}/set-default', [CompanyDetailController::class, 'setDefault'])
            ->name('company.set-default');
    });
});
