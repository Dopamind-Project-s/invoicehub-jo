<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;

class CompanyController extends Controller
{
    public function index()
    {
        return view('companies.index', ['companies' => Company::latest()->paginate(15)]);
    }

    public function create()
    {
        return view('companies.create', ['company' => new Company([
            'country_code' => 'JO',
            'default_currency' => 'JOD',
            'icv_prefix' => 'INV',
            'is_active' => true,
        ])]);
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        Company::create($this->payload($request->validated()));

        return redirect()->route('companies.index')->with('success', 'تم حفظ الشركة.');
    }

    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $company->update($this->payload($request->validated(), $company));

        return redirect()->route('companies.index')->with('success', 'تم تحديث الشركة.');
    }

    private function payload(array $data, ?Company $company = null): array
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        foreach (['jofotara_client_id', 'jofotara_secret_key'] as $credential) {
            if (blank($data[$credential] ?? null)) {
                unset($data[$credential]);
            }
            if ($company && ! array_key_exists($credential, $data)) {
                $data[$credential] = $company->{$credential};
            }
        }

        return $data;
    }
}
