<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/contact', function () {
    return view('contact');
});

Route::get('/rfq', function () {
    return view('rfq');
});

Route::get('/test-roles', function () {
    return response()->json([
        'roles' => auth()->user() ? auth()->user()->getRoleNames() : null,
        'has_agent' => auth()->user() ? auth()->user()->hasRole('agent') : false,
        'has_any' => auth()->user() ? auth()->user()->hasAnyRole(['owner','admin','agent','super_agent']) : false,
    ]);
});

Route::post('/rfq/submit', [\App\Http\Controllers\PublicRfqController::class, 'submit'])->name('rfq.submit');
Route::get('/tracking/{token}', [\App\Http\Controllers\TrackingController::class, 'track'])->name('rfq.track');

Route::get('/sourcing-companies', [\App\Http\Controllers\SourcingCompanyController::class, 'index'])->name('sourcing-companies.index');
Route::get('/sourcing-companies/{sourcingCompany}', [\App\Http\Controllers\SourcingCompanyController::class, 'show'])->name('sourcing-companies.show');

Route::get('/signin', function () {
    return Inertia\Inertia::render('Auth/Signin');
})->middleware('guest')->name('login');

Route::get('/signup', function () {
    return Inertia\Inertia::render('Auth/Signup');
})->middleware('guest')->name('register');

// This must be defined AFTER requiring auth.php is overridden
// We register it first so it takes priority over Breeze's forgot-password
Route::get('/forgot-password', function () {
    return Inertia\Inertia::render('Auth/ForgotPassword');
})->middleware('guest')->name('password.request');

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::post('/notifications/{id}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');

    Route::prefix('admin')->group(function () {
        Route::middleware(['role:admin|owner'])->group(function () {
            Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('admin.users');
            Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('admin.users.store');
            Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('admin.users.update');
            Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('admin.users.destroy');
            
            Route::get('/roles', [\App\Http\Controllers\RoleController::class, 'index'])->name('admin.roles');
            
            Route::post('/roles', [\App\Http\Controllers\RoleController::class, 'store'])->name('admin.roles.store');
            Route::put('/roles/{role:id}', [\App\Http\Controllers\RoleController::class, 'update'])->name('admin.roles.update');
            Route::delete('/roles/{role:id}', [\App\Http\Controllers\RoleController::class, 'destroy'])->name('admin.roles.destroy');

            Route::get('/permissions', [\App\Http\Controllers\PermissionController::class, 'index'])->name('admin.permissions');
            Route::post('/permissions', [\App\Http\Controllers\PermissionController::class, 'store'])->name('admin.permissions.store');
            Route::put('/permissions/{permission:id}', [\App\Http\Controllers\PermissionController::class, 'update'])->name('admin.permissions.update');
            Route::delete('/permissions/{permission:id}', [\App\Http\Controllers\PermissionController::class, 'destroy'])->name('admin.permissions.destroy');

            Route::get('/rfqs', [\App\Http\Controllers\Admin\RfqController::class, 'index'])->name('admin.rfqs.index');
            Route::get('/rfqs/{rfq}', [\App\Http\Controllers\Admin\RfqController::class, 'show'])->name('admin.rfqs.show');
            Route::put('/rfqs/{rfq}', [\App\Http\Controllers\Admin\RfqController::class, 'update'])->name('admin.rfqs.update');
            Route::delete('/rfqs/{rfq}', [\App\Http\Controllers\Admin\RfqController::class, 'destroy'])->name('admin.rfqs.destroy');

            Route::get('/sourcing-companies', function () {
                $companies = \App\Models\SourcingCompany::all();
                return Inertia\Inertia::render('Admin/SourcingCompanies/Index', ['companies' => $companies]);
            })->name('admin.sourcing-companies');

            Route::post('/sourcing-companies', [\App\Http\Controllers\SourcingCompanyController::class, 'store'])->name('admin.sourcing-companies.store');
            Route::put('/sourcing-companies/{sourcingCompany}', [\App\Http\Controllers\SourcingCompanyController::class, 'update'])->name('admin.sourcing-companies.update');
            Route::delete('/sourcing-companies/{sourcingCompany}', [\App\Http\Controllers\SourcingCompanyController::class, 'destroy'])->name('admin.sourcing-companies.destroy');

            Route::get('/categories', function () {
                $categories = \App\Models\Category::all();
                return Inertia\Inertia::render('Admin/Categories/Index', ['categories' => $categories]);
            })->name('admin.categories');

            Route::post('/categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('admin.categories.store');
            Route::put('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('admin.categories.update');
            Route::delete('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('admin.categories.destroy');
        });
        Route::prefix('agent')->middleware('role:agent|super_agent')->group(function () {
            Route::patch('/rfqs/{rfq}/status', [\App\Http\Controllers\AgentRfqController::class, 'updateStatus'])->name('agent.rfqs.update-status');
            Route::patch('/profile', [\App\Http\Controllers\AgentRfqController::class, 'updateProfile'])->name('agent.profile.update');
        });
    });

    // Owner, Admin, and Agent Finance Routes
    Route::middleware(['role:owner|admin|agent|super_agent'])->group(function () {
        Route::prefix('finance')->group(function () {
            // Items (Agents can view/manage items based on requirement "should be able to... Items")
            Route::get('/items', [\App\Http\Controllers\Finance\ItemController::class, 'index'])->name('finance.items.index');
            Route::post('/items', [\App\Http\Controllers\Finance\ItemController::class, 'store'])->name('finance.items.store');
            Route::put('/items/{item}', [\App\Http\Controllers\Finance\ItemController::class, 'update'])->name('finance.items.update');
            Route::delete('/items/{item}', [\App\Http\Controllers\Finance\ItemController::class, 'destroy'])->name('finance.items.destroy');
            Route::post('/items/{item}/active', [\App\Http\Controllers\Finance\ItemController::class, 'markActive'])->name('finance.items.active');
            Route::post('/items/{item}/inactive', [\App\Http\Controllers\Finance\ItemController::class, 'markInactive'])->name('finance.items.inactive');
            Route::post('/items/{item}/sync', [\App\Http\Controllers\Finance\ItemController::class, 'sync'])->name('finance.items.sync');

            // Estimates
            Route::get('/estimates', [\App\Http\Controllers\Finance\EstimateController::class, 'index'])->name('finance.estimates.index');
            Route::get('/estimates/create', [\App\Http\Controllers\Finance\EstimateController::class, 'create'])->name('finance.estimates.create');
            Route::post('/estimates', [\App\Http\Controllers\Finance\EstimateController::class, 'store'])->name('finance.estimates.store');
            Route::get('/estimates/{estimate}', [\App\Http\Controllers\Finance\EstimateController::class, 'show'])->name('finance.estimates.show');
            Route::get('/estimates/{estimate}/pdf', [\App\Http\Controllers\Finance\EstimateController::class, 'downloadPdf'])->name('finance.estimates.pdf');
            Route::post('/estimates/{estimate}/send', [\App\Http\Controllers\Finance\EstimateController::class, 'send'])->name('finance.estimates.send');
            Route::post('/estimates/{estimate}/accept', [\App\Http\Controllers\Finance\EstimateController::class, 'accept'])->name('finance.estimates.accept');
            Route::post('/estimates/{estimate}/decline', [\App\Http\Controllers\Finance\EstimateController::class, 'decline'])->name('finance.estimates.decline');
            Route::delete('/estimates/{estimate}', [\App\Http\Controllers\Finance\EstimateController::class, 'destroy'])->name('finance.estimates.destroy')->middleware('role:owner|admin');

            // Invoices (Agent can only View, Send)
            Route::get('/invoices', [\App\Http\Controllers\Finance\InvoiceController::class, 'index'])->name('finance.invoices.index');
            Route::get('/invoices/create', [\App\Http\Controllers\Finance\InvoiceController::class, 'create'])->name('finance.invoices.create')->middleware('role:owner|admin');
            Route::post('/invoices', [\App\Http\Controllers\Finance\InvoiceController::class, 'store'])->name('finance.invoices.store')->middleware('role:owner|admin');
            Route::post('/invoices/{estimate}/convert', [\App\Http\Controllers\Finance\InvoiceController::class, 'createFromEstimate'])->name('finance.invoices.convert')->middleware('role:owner|admin|agent|super_agent');
            Route::get('/invoices/{invoice}', [\App\Http\Controllers\Finance\InvoiceController::class, 'show'])->name('finance.invoices.show');
            Route::get('/invoices/{invoice}/pdf', [\App\Http\Controllers\Finance\InvoiceController::class, 'downloadPdf'])->name('finance.invoices.pdf');
            Route::post('/invoices/{invoice}/send', [\App\Http\Controllers\Finance\InvoiceController::class, 'send'])->name('finance.invoices.send');
            Route::post('/invoices/{invoice}/void', [\App\Http\Controllers\Finance\InvoiceController::class, 'void'])->name('finance.invoices.void')->middleware('role:owner|admin');
            Route::post('/invoices/{invoice}/payment', [\App\Http\Controllers\Finance\InvoiceController::class, 'recordPayment'])->name('finance.invoices.payment')->middleware('role:owner|admin');
            Route::delete('/invoices/{invoice}', [\App\Http\Controllers\Finance\InvoiceController::class, 'destroy'])->name('finance.invoices.destroy')->middleware('role:owner|admin');

            // Reports
            Route::get('/reports', [\App\Http\Controllers\Finance\ReportController::class, 'index'])->name('finance.reports.index')->middleware('role:owner|admin');
            Route::get('/reports/invoice-summary', [\App\Http\Controllers\Finance\ReportController::class, 'invoiceSummary'])->name('finance.reports.invoice-summary')->middleware('role:owner|admin');
            Route::get('/reports/payments-received', [\App\Http\Controllers\Finance\ReportController::class, 'paymentReceived'])->name('finance.reports.payments-received')->middleware('role:owner|admin');
            Route::get('/reports/outstanding-receivables', [\App\Http\Controllers\Finance\ReportController::class, 'outstandingReceivables'])->name('finance.reports.outstanding-receivables')->middleware('role:owner|admin');
        });
    });

    // Admin Only Sync Route
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::post('/customers/{user}/sync-zoho', [\App\Http\Controllers\Finance\CustomerSyncController::class, 'sync'])->name('admin.customers.sync-zoho');
    });

    // Agent Specific Routes
    Route::middleware(['role:agent|super_agent'])->prefix('agent')->group(function () {
        Route::post('/rfq/{rfq}/create-estimate', [\App\Http\Controllers\Finance\EstimateController::class, 'createFromRfq'])->name('agent.rfq.create-estimate');
        Route::post('/rfq/{rfq}/create-invoice', [\App\Http\Controllers\Finance\InvoiceController::class, 'createFromRfq'])->name('agent.rfq.create-invoice');
    });
});
