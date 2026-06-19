<?php

use App\Http\Controllers\Admin\CompanyManagementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/invoices');

Route::middleware('super.admin')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/companies', [CompanyManagementController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [CompanyManagementController::class, 'create'])->name('companies.create');
    Route::post('/companies', [CompanyManagementController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}', [CompanyManagementController::class, 'show'])->name('companies.show');
    Route::get('/companies/{company}/edit', [CompanyManagementController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{company}', [CompanyManagementController::class, 'update'])->name('companies.update');
    Route::post('/companies/{company}/activate', [CompanyManagementController::class, 'activate'])->name('companies.activate');
    Route::post('/companies/{company}/suspend', [CompanyManagementController::class, 'suspend'])->name('companies.suspend');
});

Route::resource('companies', CompanyController::class)->except(['show', 'destroy']);
Route::resource('customers', CustomerController::class);
Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
Route::post('/jofotara/create-real-sample', [InvoiceController::class, 'createRealSample'])->name('jofotara.create-real-sample');
Route::post('/invoices/{invoice}/prepare', [InvoiceController::class, 'prepare'])->name('invoices.prepare');
Route::post('/invoices/{invoice}/prepare-jofotara', [InvoiceController::class, 'prepare'])->name('invoices.prepare-jofotara');
Route::post('/invoices/{invoice}/submit-to-jofotara', [InvoiceController::class, 'submitReal'])->name('invoices.submit-to-jofotara');
Route::post('/invoices/{invoice}/submit-real-jofotara', [InvoiceController::class, 'submitReal'])->name('invoices.submit-real-jofotara');
Route::post('/invoices/{invoice}/qr', [InvoiceController::class, 'updateQr'])->name('invoices.update-qr');
Route::get('/invoices/{invoice}/qr', [InvoiceController::class, 'qr'])->name('invoices.qr');
Route::get('/invoices/{invoice}/download-xml', [InvoiceController::class, 'downloadXml'])->name('invoices.download-xml');
Route::get('/invoices/{invoice}/download-payload', [InvoiceController::class, 'downloadPayload'])->name('invoices.download-payload');
Route::get('/invoices/{invoice}/issued-pdf', [InvoiceController::class, 'issuedPdf'])->name('invoices.issued-pdf');
