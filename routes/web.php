<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SellerController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/invoices');
Route::resource('sellers', SellerController::class);
Route::resource('customers', CustomerController::class);
Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
Route::post('/jofotara/create-real-sample', [InvoiceController::class, 'createRealSample'])->name('jofotara.create-real-sample');
Route::post('/invoices/{invoice}/prepare-jofotara', [InvoiceController::class, 'prepare'])->name('invoices.prepare-jofotara');
Route::post('/invoices/{invoice}/submit-real-jofotara', [InvoiceController::class, 'submitReal'])->name('invoices.submit-real-jofotara');
Route::get('/invoices/{invoice}/download-xml', [InvoiceController::class, 'downloadXml'])->name('invoices.download-xml');
Route::get('/invoices/{invoice}/download-payload', [InvoiceController::class, 'downloadPayload'])->name('invoices.download-payload');
Route::get('/invoices/{invoice}/issued-pdf', [InvoiceController::class, 'issuedPdf'])->name('invoices.issued-pdf');
