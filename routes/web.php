<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SellerController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/invoices');
Route::resource('sellers', SellerController::class);
Route::resource('customers', CustomerController::class);
Route::resource('invoices', InvoiceController::class);
Route::post('/invoices/{invoice}/submit-to-jofotara', [InvoiceController::class, 'submitToJofotara'])->name('invoices.submit-to-jofotara');
Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
Route::get('/invoices/{invoice}/preview', [InvoiceController::class, 'preview'])->name('invoices.preview');
