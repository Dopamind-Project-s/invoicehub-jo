<?php

use App\Http\Controllers\Admin\CompanyManagementController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\CompanyWorkspace\ActivityController;
use App\Http\Controllers\CompanyWorkspace\CompanyRoleController;
use App\Http\Controllers\CompanyWorkspace\CompanySettingsController;
use App\Http\Controllers\CompanyWorkspace\CompanyUserController;
use App\Http\Controllers\CompanyWorkspace\InvoiceEngineController;
use App\Http\Controllers\CompanyWorkspace\MasterData\ContactController;
use App\Http\Controllers\CompanyWorkspace\MasterData\ProductCategoryController;
use App\Http\Controllers\CompanyWorkspace\MasterData\ProductController;
use App\Http\Controllers\CompanyWorkspace\MasterData\TaxProfileController;
use App\Http\Controllers\CompanyWorkspace\MasterData\UnitController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'super.admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::resource('companies', CompanyManagementController::class);
});

Route::middleware(['auth', 'permission.team'])->prefix('companies/{company}')->name('company.')->group(function (): void {
    Route::get('users', [CompanyUserController::class, 'index'])->middleware('permission:users.manage')->name('users.index');
    Route::get('users/create', [CompanyUserController::class, 'create'])->middleware('permission:users.manage')->name('users.create');
    Route::post('users', [CompanyUserController::class, 'store'])->middleware('permission:users.manage')->name('users.store');
    Route::get('users/{user}', [CompanyUserController::class, 'show'])->middleware('permission:users.manage')->name('users.show');
    Route::get('users/{user}/edit', [CompanyUserController::class, 'edit'])->middleware('permission:users.manage')->name('users.edit');
    Route::put('users/{user}', [CompanyUserController::class, 'update'])->middleware('permission:users.manage')->name('users.update');
    Route::post('users/{user}/activate', [CompanyUserController::class, 'activate'])->middleware('permission:users.manage')->name('users.activate');
    Route::post('users/{user}/suspend', [CompanyUserController::class, 'suspend'])->middleware('permission:users.manage')->name('users.suspend');
    Route::put('users/{user}/password', [CompanyUserController::class, 'resetPassword'])->middleware('permission:users.manage')->name('users.password');

    Route::get('roles', [CompanyRoleController::class, 'index'])->middleware('permission:settings.manage')->name('roles.index');
    Route::put('roles/{role}', [CompanyRoleController::class, 'update'])->middleware('permission:settings.manage')->name('roles.update');

    Route::get('settings', [CompanySettingsController::class, 'edit'])->middleware('permission:settings.manage')->name('settings.edit');
    Route::put('settings', [CompanySettingsController::class, 'update'])->middleware('permission:settings.manage')->name('settings.update');

    Route::get('activity', [ActivityController::class, 'index'])->middleware('permission:reports.view')->name('activity.index');


    Route::middleware('permission:invoices.view')->group(function (): void {
        Route::get('invoices', [InvoiceEngineController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [InvoiceEngineController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/printable', [InvoiceEngineController::class, 'printable'])->name('invoices.printable');
    });
    Route::middleware('permission:invoices.create')->group(function (): void {
        Route::get('invoices/create', [InvoiceEngineController::class, 'create'])->name('invoices.create');
        Route::post('invoices', [InvoiceEngineController::class, 'store'])->name('invoices.store');
        Route::get('invoices/{invoice}/edit', [InvoiceEngineController::class, 'edit'])->name('invoices.edit');
        Route::put('invoices/{invoice}', [InvoiceEngineController::class, 'update'])->name('invoices.update');
        Route::post('invoices/{invoice}/submit', [InvoiceEngineController::class, 'submit'])->name('invoices.submit');
        Route::post('invoices/{invoice}/cancel', [InvoiceEngineController::class, 'cancel'])->name('invoices.cancel');
    });
    Route::post('invoices/{invoice}/approve', [InvoiceEngineController::class, 'approve'])->middleware('permission:invoices.approve')->name('invoices.approve');


    Route::middleware('permission:products.manage')->group(function (): void {
        Route::resource('product-categories', ProductCategoryController::class)->except(['show', 'destroy']);
        Route::post('product-categories/{product_category}/activate', [ProductCategoryController::class, 'activate'])->name('product-categories.activate');
        Route::post('product-categories/{product_category}/deactivate', [ProductCategoryController::class, 'deactivate'])->name('product-categories.deactivate');
        Route::resource('units', UnitController::class)->except(['show', 'destroy']);
        Route::post('units/{unit}/activate', [UnitController::class, 'activate'])->name('units.activate');
        Route::post('units/{unit}/deactivate', [UnitController::class, 'deactivate'])->name('units.deactivate');
        Route::resource('tax-profiles', TaxProfileController::class)->except(['show', 'destroy']);
        Route::post('tax-profiles/{tax_profile}/activate', [TaxProfileController::class, 'activate'])->name('tax-profiles.activate');
        Route::post('tax-profiles/{tax_profile}/deactivate', [TaxProfileController::class, 'deactivate'])->name('tax-profiles.deactivate');
        Route::resource('products', ProductController::class)->except(['show', 'destroy']);
        Route::post('products/{product}/activate', [ProductController::class, 'activate'])->name('products.activate');
        Route::post('products/{product}/deactivate', [ProductController::class, 'deactivate'])->name('products.deactivate');
    });

    Route::middleware('permission:contacts.manage')->group(function (): void {
        Route::resource('contacts', ContactController::class)->except(['show', 'destroy']);
        Route::post('contacts/{contact}/activate', [ContactController::class, 'activate'])->name('contacts.activate');
        Route::post('contacts/{contact}/deactivate', [ContactController::class, 'deactivate'])->name('contacts.deactivate');
    });
});

require __DIR__.'/auth.php';
