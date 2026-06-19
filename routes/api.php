<?php

use App\Http\Controllers\Api\InvoiceApiController;
use Illuminate\Support\Facades\Route;

Route::post('/invoices', [InvoiceApiController::class, 'store']);
Route::post('/invoices/{invoice}/generate', [InvoiceApiController::class, 'generate']);
Route::post('/invoices/{invoice}/submit', [InvoiceApiController::class, 'submit']);
Route::get('/invoices/{invoice}', [InvoiceApiController::class, 'show']);
Route::get('/invoices/{invoice}/xml', [InvoiceApiController::class, 'xml']);
Route::get('/invoices/{invoice}/status', [InvoiceApiController::class, 'status']);
Route::get('/invoices/{invoice}/pdf', [InvoiceApiController::class, 'pdf']);
