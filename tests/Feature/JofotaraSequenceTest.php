<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Invoice;
use App\Services\Jofotara\ICVService;
use App\Services\Jofotara\JoFotaraPreparationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JofotaraSequenceTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_local_and_failed_invoices_do_not_affect_next_jofotara_icv(): void
    {
        $company = Company::where('tax_number', '9578331')->firstOrFail();
        Invoice::where('company_id', $company->id)->update(['jofotara_status' => null, 'jofotara_uuid' => null, 'xml_hash' => null]);

        $this->assertSame(1, app(ICVService::class)->nextForSubmission($company));

        Invoice::where('company_id', $company->id)->firstOrFail()->forceFill([
            'jofotara_status' => 'ERROR',
            'jofotara_uuid' => 'FAILED-UUID',
            'xml_hash' => 'FAILED-HASH',
        ])->save();

        $this->assertSame(1, app(ICVService::class)->nextForSubmission($company));
    }

    public function test_accepted_jofotara_invoice_becomes_previous_pih_source(): void
    {
        $company = Company::where('tax_number', '9578331')->firstOrFail();
        Invoice::where('company_id', $company->id)->delete();

        $accepted = Invoice::create($this->payload($company, 'JF-1', 1, [
            'status' => Invoice::STATUS_SUBMITTED,
            'jofotara_status' => 'ACCEPTED',
            'jofotara_uuid' => 'UUID-1',
            'xml_hash' => 'HASH-1',
            'accepted_at' => now(),
        ]));
        $ready = Invoice::create($this->payload($company, 'JF-2', 99, ['status' => Invoice::STATUS_READY]));

        $service = app(JoFotaraPreparationService::class);
        $service->ensureIdentifiers($ready);
        $pih = $service->resolvePih($ready->refresh());

        $this->assertSame(2, (int) $ready->icv);
        $this->assertSame($accepted->xml_hash, $pih['value']);
        $this->assertSame('previous accepted invoice', $pih['source']);
    }

    public function test_diagnostics_identify_first_jofotara_invoice(): void
    {
        $company = Company::where('tax_number', '9578331')->firstOrFail();
        Invoice::where('company_id', $company->id)->update(['jofotara_status' => null, 'jofotara_uuid' => null, 'xml_hash' => null]);
        $invoice = Invoice::where('company_id', $company->id)->where('status', Invoice::STATUS_READY)->firstOrFail();

        $diagnostic = app(JoFotaraPreparationService::class)->diagnostics($invoice);

        $this->assertSame(1, $diagnostic['current_icv']);
        $this->assertSame('initial', $diagnostic['pih_status']);
        $this->assertSame('هذه أول فاتورة يتم إرسالها إلى نظام الفوترة الوطني.', $diagnostic['next_action']);
    }

    /** @param array<string,mixed> $overrides */
    private function payload(Company $company, string $number, int $icv, array $overrides = []): array
    {
        return array_merge([
            'company_id' => $company->id,
            'supplier_id' => $company->id,
            'invoice_number' => $number,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'icv' => $icv,
            'invoice_type' => Invoice::TYPE_TAX_INVOICE,
            'invoice_subtype' => 'SALE',
            'invoice_scope' => 'local',
            'payment_type' => 'receivable',
            'taxpayer_type' => 'income',
            'status' => Invoice::STATUS_DRAFT,
            'issue_date' => now()->toDateString(),
            'issue_time' => now()->format('H:i:s'),
            'currency' => 'JOD',
            'currency_code' => 'JOD',
            'exchange_rate' => '1.000000',
            'subtotal' => '1.000000',
            'discount_amount' => '0.000000',
            'discount_total' => '0.000000',
            'taxable_amount' => '1.000000',
            'tax_amount' => '0.000000',
            'tax_total' => '0.000000',
            'total_amount' => '1.000000',
            'payable_amount' => '1.000000',
            'grand_total' => '1.000000',
        ], $overrides);
    }
}
