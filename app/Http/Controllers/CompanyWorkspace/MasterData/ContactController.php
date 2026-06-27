<?php

declare(strict_types=1);

namespace App\Http\Controllers\CompanyWorkspace\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Contact;
use App\Services\Audit\AuditLogger;
use App\Services\CompanyWorkspace\CompanyDashboardStatsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request, Company $company)
    {
        $contacts = Contact::where('company_id', $company->id)
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($s) => $s->where('name_ar', 'like', '%'.$request->search.'%')->orWhere('name_en', 'like', '%'.$request->search.'%')->orWhere('phone', 'like', '%'.$request->search.'%')->orWhere('email', 'like', '%'.$request->search.'%')))
            ->when($request->filled('tax_number'), fn ($q) => $q->where(fn ($s) => $s->where('tax_number', 'like', '%'.$request->tax_number.'%')->orWhere('national_number', 'like', '%'.$request->tax_number.'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->latest()->paginate(15)->withQueryString();
        return view('company.master-data.contacts.index', compact('company', 'contacts'));
    }

    public function create(Company $company) { return view('company.master-data.contacts.create', ['company' => $company, 'contact' => new Contact(['is_active' => true, 'type' => Contact::TYPE_CUSTOMER, 'country' => 'JO'])]); }

    public function store(Request $request, Company $company)
    {
        $data = $this->validated($request);
        $contact = Contact::where('company_id', $company->id)->where(function ($q) use ($data): void { $q->when($data['tax_number'] ?? null, fn ($s, $v) => $s->orWhere('tax_number', $v))->when($data['national_number'] ?? null, fn ($s, $v) => $s->orWhere('national_number', $v)); })->first();
        if ($contact) {
            $before = $contact->toArray(); $contact->update($data);
            $this->audit->record('master_data.contact.updated', $contact, $before, $contact->toArray(), $request);
        } else {
            $contact = Contact::create($data + ['company_id' => $company->id]);
            $this->audit->record('master_data.contact.created', $contact, [], $contact->toArray(), $request);
        }
        CompanyDashboardStatsService::forget($company);

        if ($request->input('save_action') === 'save_another') {
            return redirect()->route('company.contacts.create', $company)->with('status', 'تم حفظ جهة الاتصال، يمكنك إضافة جهة أخرى.');
        }

        return redirect()->route('company.contacts.index', $company)->with('status', 'تم حفظ جهة الاتصال.');
    }

    public function edit(Company $company, Contact $contact) { abort_unless((int) $contact->company_id === (int) $company->id, 404); return view('company.master-data.contacts.edit', compact('company', 'contact')); }

    public function update(Request $request, Company $company, Contact $contact)
    {
        abort_unless((int) $contact->company_id === (int) $company->id, 404);
        $data = $this->validated($request); $duplicate = Contact::where('company_id', $company->id)->whereKeyNot($contact->id)->where(function ($q) use ($data): void { $q->when($data['tax_number'] ?? null, fn ($s, $v) => $s->orWhere('tax_number', $v))->when($data['national_number'] ?? null, fn ($s, $v) => $s->orWhere('national_number', $v)); })->exists();
        if ($duplicate) return back()->withErrors(['tax_number' => 'يوجد كيان قانوني بنفس الرقم الضريبي أو الوطني داخل الشركة.'])->withInput();
        $before = $contact->toArray(); $contact->update($data);
        $this->audit->record('master_data.contact.updated', $contact, $before, $contact->toArray(), $request);
        CompanyDashboardStatsService::forget($company);

        return redirect()->route('company.contacts.index', $company)->with('status', 'تم تحديث جهة الاتصال.');
    }

    public function activate(Request $request, Company $company, Contact $contact) { return $this->setActive($request, $company, $contact, true); }
    public function deactivate(Request $request, Company $company, Contact $contact) { return $this->setActive($request, $company, $contact, false); }
    private function setActive(Request $request, Company $company, Contact $contact, bool $active) { abort_unless((int) $contact->company_id === (int) $company->id, 404); $before = $contact->only('is_active'); $contact->update(['is_active' => $active]); $this->audit->record('master_data.contact.'.($active ? 'activated' : 'deactivated'), $contact, $before, $contact->only('is_active'), $request); return back()->with('status', $active ? 'تم تفعيل جهة الاتصال.' : 'تم تعطيل جهة الاتصال.'); }

    private function validated(Request $request): array
    {
        return $request->validate(['type' => ['required', Rule::in([Contact::TYPE_CUSTOMER, Contact::TYPE_SUPPLIER, Contact::TYPE_BOTH])], 'name_ar' => ['required', 'string', 'max:255', 'not_regex:/^\s*$/u'], 'name_en' => ['nullable', 'string', 'max:255'], 'tax_number' => ['nullable', 'string', 'max:50'], 'national_number' => ['nullable', 'string', 'max:50'], 'phone' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+()\-\s]*$/'], 'email' => ['nullable', 'email', 'max:255'], 'address' => ['nullable', 'string'], 'city' => ['nullable', 'string', 'max:100'], 'country' => ['nullable', 'string', 'size:2'], 'is_active' => ['nullable', 'boolean'], 'save_action' => ['nullable', Rule::in(['save', 'save_another'])]]) + ['country' => $request->input('country', 'JO'), 'is_active' => $request->boolean('is_active')];
    }
}
