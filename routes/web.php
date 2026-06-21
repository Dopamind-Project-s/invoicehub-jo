<?php

use App\Http\Controllers\Admin\CompanyManagementController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FeatureKeyController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\CompanyWorkspace\ActivityController;
use App\Http\Controllers\CompanyWorkspace\CompanyRoleController;
use App\Http\Controllers\CompanyWorkspace\CompanySettingsController;
use App\Http\Controllers\CompanyWorkspace\CompanyUserController;
use App\Http\Controllers\CompanyWorkspace\InvoiceEngineController;
use App\Http\Controllers\CompanyWorkspace\InvoiceShareController;
use App\Http\Controllers\CompanyWorkspace\InvoiceTemplateController;
use App\Http\Controllers\CompanyWorkspace\JofotaraImportController;
use App\Http\Controllers\CompanyWorkspace\WorkspaceDashboardController;
use App\Http\Controllers\PublicInvoiceShareController;
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
Route::get('/shared/invoices/{token}', PublicInvoiceShareController::class)->name('invoices.shared.show');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user?->isSuperAdmin()) { return redirect()->route('admin.dashboard.show'); }
    if ($user?->company_id && ($company = \App\Models\Company::with('featureKeys')->find($user->company_id))) {
        return view('company.dashboard', [
            'company' => $company,
            'productCount' => \App\Models\Product::where('company_id', $company->id)->count(),
            'contactCount' => \App\Models\Contact::where('company_id', $company->id)->count(),
            'invoiceCount' => \App\Models\Invoice::where('company_id', $company->id)->count(),
            'pendingInvoices' => \App\Models\Invoice::where('company_id', $company->id)->where('status', \App\Models\Invoice::STATUS_READY)->count(),
            'approvedInvoices' => \App\Models\Invoice::where('company_id', $company->id)->where('status', \App\Models\Invoice::STATUS_SUBMITTED)->count(),
            'jofotaraSubmittedCount' => \App\Models\Invoice::where('company_id', $company->id)->whereNotNull('jofotara_submitted_at')->count(),
            'pendingJofotaraCount' => \App\Models\Invoice::where('company_id', $company->id)->where('status', \App\Models\Invoice::STATUS_READY)->whereNull('jofotara_status')->count(),
            'importedInvoiceCount' => \App\Models\Invoice::where('company_id', $company->id)->where('source', 'jofotara_import')->count(),
            'recentInvoices' => \App\Models\Invoice::where('company_id', $company->id)->latest()->limit(5)->get(),
        ]);
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'super.admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('dashboard', AdminDashboardController::class)->name('dashboard.show');
    Route::resource('companies', CompanyManagementController::class);
    Route::post('companies/{company}/activate', [CompanyManagementController::class, 'activate'])->name('companies.activate');
    Route::post('companies/{company}/suspend', [CompanyManagementController::class, 'suspend'])->name('companies.suspend');
    Route::get('feature-keys', [FeatureKeyController::class, 'index'])->name('feature-keys.index');
    Route::resource('plans', PlanController::class)->except(['show', 'destroy']);
    Route::post('plans/{plan}/activate', [PlanController::class, 'activate'])->name('plans.activate');
    Route::post('plans/{plan}/deactivate', [PlanController::class, 'deactivate'])->name('plans.deactivate');
});

Route::middleware(['auth', 'permission.team'])->prefix('companies/{company}')->name('company.')->group(function (): void {
    Route::get('/', WorkspaceDashboardController::class)->name('dashboard');
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
    Route::get('invoice-templates', [InvoiceTemplateController::class, 'index'])->middleware('permission:settings.manage')->name('invoice-templates.index');
    Route::get('invoice-templates/{template}/preview', [InvoiceTemplateController::class, 'preview'])->middleware('permission:settings.manage')->whereNumber('template')->name('invoice-templates.preview');
    Route::put('invoice-templates', [InvoiceTemplateController::class, 'update'])->middleware('permission:settings.manage')->name('invoice-templates.update');
    Route::put('settings', [CompanySettingsController::class, 'update'])->middleware('permission:settings.manage')->name('settings.update');

    Route::get('activity', [ActivityController::class, 'index'])->middleware('permission:reports.view')->name('activity.index');


    Route::middleware('permission:invoices.create')->group(function (): void {
        Route::get('invoices/create', [InvoiceEngineController::class, 'create'])->name('invoices.create');
        Route::post('invoices', [InvoiceEngineController::class, 'store'])->name('invoices.store');
        Route::get('invoices/{invoice}/edit', [InvoiceEngineController::class, 'edit'])->whereNumber('invoice')->name('invoices.edit');
        Route::put('invoices/{invoice}', [InvoiceEngineController::class, 'update'])->whereNumber('invoice')->name('invoices.update');
        Route::post('invoices/{invoice}/submit', [InvoiceEngineController::class, 'submit'])->whereNumber('invoice')->name('invoices.submit');
        Route::post('invoices/{invoice}/cancel', [InvoiceEngineController::class, 'cancel'])->whereNumber('invoice')->name('invoices.cancel');
        Route::post('invoices/{invoice}/draft', [InvoiceEngineController::class, 'returnToDraft'])->whereNumber('invoice')->name('invoices.draft');
    });
    Route::middleware('permission:invoices.view')->group(function (): void {
        Route::get('invoices', [InvoiceEngineController::class, 'index'])->name('invoices.index');
        Route::get('invoices/import', [JofotaraImportController::class, 'index'])->name('invoices.import.index');
        Route::get('invoices/jofotara-uat', [InvoiceEngineController::class, 'jofotaraUat'])->name('invoices.jofotara.uat');
        Route::post('invoices/import', [JofotaraImportController::class, 'store'])->name('invoices.import.store');
        Route::get('invoices/{invoice}', [InvoiceEngineController::class, 'show'])->whereNumber('invoice')->name('invoices.show');
        Route::get('invoices/{invoice}/printable', [InvoiceEngineController::class, 'printable'])->whereNumber('invoice')->name('invoices.printable');
        Route::post('invoices/{invoice}/shares', [InvoiceShareController::class, 'store'])->whereNumber('invoice')->name('invoices.shares.store');
    });
    Route::post('invoices/{invoice}/approve', [InvoiceEngineController::class, 'approve'])->middleware('permission:invoices.approve')->whereNumber('invoice')->name('invoices.approve');
    Route::post('invoices/{invoice}/jofotara-submit', [InvoiceEngineController::class, 'submitToJofotara'])->middleware('permission:invoices.submit')->whereNumber('invoice')->name('invoices.jofotara.submit');


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

Route::middleware(['auth', 'permission.team'])->prefix('workspace/companies/{company}')->name('workspace.companies.')->group(function (): void {
    Route::get('/', WorkspaceDashboardController::class)->name('show');
});

require __DIR__.'/auth.php';
