<?php

use App\Http\Controllers\Admin\CompanyManagementController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\CompanyWorkspace\ActivityController;
use App\Http\Controllers\CompanyWorkspace\CompanyRoleController;
use App\Http\Controllers\CompanyWorkspace\CompanySettingsController;
use App\Http\Controllers\CompanyWorkspace\CompanyUserController;
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
});

require __DIR__.'/auth.php';
