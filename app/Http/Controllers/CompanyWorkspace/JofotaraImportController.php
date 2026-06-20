<?php

declare(strict_types=1);

namespace App\Http\Controllers\CompanyWorkspace;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class JofotaraImportController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Company $company)
    {
        $this->authorizeSyncFeature($company);
        $imports = Invoice::where('company_id', $company->id)->where('source', 'jofotara_import')->latest()->paginate(15);

        return view('company.invoices.import', compact('company', 'imports'));
    }

    public function store(Request $request, Company $company): RedirectResponse
    {
        $this->authorizeSyncFeature($company);

        $data = $request->validate([
            'import_file' => ['required', 'file', 'mimes:json,csv,txt', 'max:2048'],
        ]);

        $rows = $this->rows($request->file('import_file')->get(), $request->file('import_file')->getClientOriginalExtension());
        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if ($this->exists($company, $row)) {
                $skipped++;
                continue;
            }

            $invoice = Invoice::create([
                'company_id' => $company->id,
                'supplier_id' => $company->id,
                'invoice_number' => (string) ($row['invoice_number'] ?? 'JF-'.Str::upper(Str::random(8))),
                'uuid' => (string) Str::uuid(),
                'icv' => ((int) Invoice::max('icv')) + 1,
                'invoice_type' => Invoice::TYPE_TAX_INVOICE,
                'invoice_subtype' => 'SALE',
                'invoice_scope' => 'local',
                'payment_type' => 'receivable',
                'taxpayer_type' => 'income',
                'status' => Invoice::STATUS_APPROVED,
                'source' => 'jofotara_import',
                'issue_date' => $row['issue_date'] ?? now()->toDateString(),
                'issue_time' => $row['issue_time'] ?? now()->format('H:i:s'),
                'currency' => $row['currency'] ?? 'JOD',
                'currency_code' => $row['currency'] ?? 'JOD',
                'exchange_rate' => '1.000000',
                'subtotal' => $row['total'] ?? 0,
                'discount_amount' => 0,
                'discount_total' => 0,
                'taxable_amount' => $row['total'] ?? 0,
                'tax_amount' => 0,
                'tax_total' => 0,
                'total_amount' => $row['total'] ?? 0,
                'payable_amount' => $row['total'] ?? 0,
                'grand_total' => $row['total'] ?? 0,
                'created_by' => Auth::id(),
                'jofotara_status' => $row['jofotara_status'] ?? $row['status'] ?? 'IMPORTED',
                'jofotara_uuid' => $row['jofotara_uuid'] ?? $row['uuid'] ?? null,
                'jofotara_qr' => $row['jofotara_qr'] ?? $row['qr'] ?? null,
                'jofotara_response' => json_encode(['imported_row' => $row], JSON_UNESCAPED_UNICODE),
            ]);

            $this->audit->record('invoice.jofotara.imported', $invoice, [], $invoice->only('invoice_number', 'jofotara_uuid', 'jofotara_status'), $request);
            $created++;
        }

        return back()->with('status', "تم استيراد {$created} فاتورة وتجاوز {$skipped} فاتورة مكررة.");
    }

    private function authorizeSyncFeature(Company $company): void
    {
        $company->loadMissing('featureKeys');
        abort_unless($company->featureKeys->contains('code', 'JOFOTARA_SYNC'), 403, 'ميزة مزامنة جوفوتارا غير مفعلة لهذه المنشأة.');
    }

    /** @return array<int,array<string,mixed>> */
    private function rows(string $contents, string $extension): array
    {
        if ($extension === 'json') {
            $decoded = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
            return array_is_list($decoded) ? $decoded : [$decoded];
        }

        $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $contents) ?: [])));
        if ($lines === []) {
            return [];
        }

        $headers = str_getcsv(array_shift($lines));
        return array_map(fn (string $line): array => array_combine($headers, str_getcsv($line)) ?: [], $lines);
    }

    /** @param array<string,mixed> $row */
    private function exists(Company $company, array $row): bool
    {
        return Invoice::where('company_id', $company->id)
            ->where(function ($query) use ($row): void {
                $query->when(filled($row['jofotara_uuid'] ?? $row['uuid'] ?? null), fn ($q) => $q->orWhere('jofotara_uuid', $row['jofotara_uuid'] ?? $row['uuid']))
                    ->orWhere(function ($q) use ($row): void {
                        $q->where('invoice_number', $row['invoice_number'] ?? null)
                            ->whereDate('issue_date', $row['issue_date'] ?? now()->toDateString());
                    });
            })->exists();
    }
}
