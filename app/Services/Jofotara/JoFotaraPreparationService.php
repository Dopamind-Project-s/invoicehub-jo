<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

use App\Models\Invoice;
use App\Models\InvoiceXmlLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class JoFotaraPreparationService
{
    public function __construct(
        private readonly ICVService $icv,
        private readonly TaxCalculationService $tax,
        private readonly UBLInvoiceBuilder $builder,
        private readonly InvoiceHashService $hash,
        private readonly InvoiceTypeCodeService $typeCodes,
        private readonly UBLValidationService $validator,
    ) {}

    public function prepare(Invoice $invoice): array
    {
        $invoice->loadMissing(['supplier', 'customer', 'items']);
        $this->ensureIdentifiers($invoice);
        $this->recalculateTotals($invoice);
        $pih = $this->resolvePih($invoice);
        $invoice->previous_invoice_hash = $pih['value'];
        $invoice->save();

        $xml = $this->builder->build($invoice->fresh(['supplier', 'customer', 'items']));
        $canonical = $this->hash->canonicalize($xml);
        $sha = $this->hash->hash($canonical);
        $invoice->forceFill(['xml_hash' => $sha, 'status' => $invoice->status === 'DRAFT' ? 'GENERATED' : $invoice->status])->save();

        $payload = ['invoice' => base64_encode($xml)];
        $dir = "jofotara/invoice-{$invoice->id}";
        $disk = Storage::build(['driver' => 'local', 'root' => storage_path('app')]);
        $disk->put("{$dir}/invoice.xml", $xml);
        $disk->put("{$dir}/canonical.xml", $canonical);
        $disk->put("{$dir}/payload.json", json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $validation = $this->validator->validateXml($xml);
        $checks = $this->checks($invoice->fresh(['supplier', 'customer', 'items']), $xml);
        InvoiceXmlLog::create([
            'invoice_id' => $invoice->id,
            'generated_xml' => $xml,
            'canonical_xml' => $canonical,
            'hash' => $sha,
            'validation_result' => ['xml' => $validation, 'checks' => $checks],
        ]);
        $this->failIfInvalid($validation, $checks);

        return [
            'invoice' => $invoice->fresh(['supplier', 'customer', 'items']),
            'xml' => $xml,
            'canonical' => $canonical,
            'payload' => $payload,
            'xml_path' => storage_path("app/{$dir}/invoice.xml"),
            'canonical_path' => storage_path("app/{$dir}/canonical.xml"),
            'payload_path' => storage_path("app/{$dir}/payload.json"),
            'xml_length' => strlen($xml),
            'base64_length' => strlen($payload['invoice']),
            'xml_sha256' => $sha,
            'previous_invoice_hash' => $invoice->previous_invoice_hash,
            'invoice_type_code_name' => $this->typeCodes->nameFor($invoice),
            'checks' => $checks,
            'pih' => $pih,
        ];
    }

    public function resolvePreviousHash(Invoice $invoice): string
    {
        return $this->resolvePih($invoice)['value'];
    }

    public function resolvePih(Invoice $invoice): array
    {
        $invoice->loadMissing('supplier');
        $icv = (int) $invoice->icv;
        if ($icv <= 0) {
            throw new RuntimeException('ICV is required before resolving PIH.');
        }

        if ($icv === 1) {
            $initialPih = (string) config('services.jofotara.initial_pih', '');

            return [
                'value' => $initialPih,
                'source' => 'initial',
                'previous_accepted_invoice_found' => false,
                'warning' => blank($initialPih) ? 'No previous accepted invoice and JOFOTARA_INITIAL_PIH is empty.' : null,
            ];
        }

        $previous = $this->icv->previousAccepted($invoice->supplier, $icv);

        if (! $previous) {
            throw new RuntimeException('PIH is missing: previous accepted JoFotara invoice with ICV '.($icv - 1).' was not found for this company. Local, draft, ready, cancelled, failed, and unsubmitted invoices are ignored.');
        }
        if (blank($previous->xml_hash)) {
            throw new RuntimeException('PIH is missing: previous accepted JoFotara invoice '.$previous->invoice_number.' has no xml_hash.');
        }

        return [
            'value' => (string) $previous->xml_hash,
            'source' => 'previous accepted invoice',
            'previous_accepted_invoice_found' => true,
            'previous_invoice_id' => $previous->id,
            'previous_invoice_number' => $previous->invoice_number,
            'warning' => null,
        ];
    }

    /** @return array<string, mixed> */
    public function diagnostics(Invoice $invoice): array
    {
        $invoice->loadMissing('supplier');
        $lastAccepted = $this->icv->lastAccepted($invoice->supplier);
        $recommendedIcv = $invoice->jofotara_status === 'ACCEPTED' ? (int) $invoice->icv : $this->icv->nextForSubmission($invoice->supplier);
        $previous = $recommendedIcv > 1 ? $this->icv->previousAccepted($invoice->supplier, $recommendedIcv) : null;

        $pihStatus = match (true) {
            $recommendedIcv === 1 => 'initial',
            $previous !== null && filled($previous->xml_hash) => 'ready',
            default => 'missing_previous',
        };

        $nextAction = match ($pihStatus) {
            'initial' => 'هذه أول فاتورة يتم إرسالها إلى نظام الفوترة الوطني.',
            'ready' => 'السلسلة جاهزة للإرسال.',
            default => 'الفاتورة السابقة المطلوبة غير موجودة.',
        };

        return [
            'current_invoice_number' => $invoice->invoice_number,
            'current_status' => $invoice->status,
            'jofotara_status' => $invoice->jofotara_status ?: 'غير مرسلة',
            'current_icv' => $recommendedIcv,
            'previous_invoice_number' => $previous?->invoice_number,
            'previous_uuid' => $previous?->jofotara_uuid ?: $previous?->submission_uuid,
            'previous_icv' => $previous?->icv,
            'last_accepted_invoice_number' => $lastAccepted?->invoice_number,
            'last_accepted_icv' => $lastAccepted?->icv,
            'pih_status' => $pihStatus,
            'next_action' => $nextAction,
        ];
    }

    public function ensureIdentifiers(Invoice $invoice): void
    {
        $updates = [];
        if (blank($invoice->invoice_number)) {
            $next = Invoice::query()->whereYear('created_at', now()->year)->count() + 1;
            $updates['invoice_number'] = 'INV_'.now()->year.'_'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
        }
        if (blank($invoice->uuid)) {
            $updates['uuid'] = (string) Str::uuid();
        }
        $invoice->loadMissing('supplier');
        $recommendedIcv = $this->icv->nextForSubmission($invoice->supplier);
        if ((int) $invoice->icv !== $recommendedIcv && $invoice->jofotara_status !== 'ACCEPTED') {
            $updates['icv'] = $recommendedIcv;
        }
        if ($updates !== []) {
            $invoice->forceFill($updates)->save();
            $invoice->refresh();
        }
    }

    private function recalculateTotals(Invoice $invoice): void
    {
        $expected = $this->tax->calculateInvoice($invoice->items->map(fn ($item): array => [
            'quantity' => (string) $item->quantity,
            'unit_price' => (string) $item->unit_price,
            'discount' => (string) $item->discount,
            'tax_percent' => (string) $item->tax_percent,
        ])->all());
        foreach ($invoice->items as $item) {
            $line = $this->tax->calculateLine((string) $item->quantity, (string) $item->unit_price, (string) $item->discount, (string) $item->tax_percent);
            $item->forceFill([
                'line_extension_amount' => $line['line_extension_amount'],
                'tax_amount' => $line['tax_amount'],
                'line_total' => $line['line_total'],
            ])->save();
        }
        $invoice->forceFill($expected)->save();
        $invoice->refresh();
    }

    private function checks(Invoice $invoice, string $xml): array
    {
        return [
            'source_id_exists' => filled($invoice->supplier?->jofotara_source_id),
            'seller_tax_number_exists' => filled($invoice->supplier?->tax_number),
            'icv_exists' => filled($invoice->icv),
            'uuid_exists' => filled($invoice->uuid),
            'pih_exists' => filled($invoice->previous_invoice_hash),
            'seller_supplier_party_exists' => str_contains($xml, '<cac:SellerSupplierParty>'),
            'buyer_fake_id_exists' => (bool) preg_match('/<cbc:ID>0+<\/cbc:ID>/', $xml),
            'invoice_type_code_name' => $this->typeCodes->nameFor($invoice),
            'invoice_type_code_name_valid' => $this->typeCodes->nameFor($invoice) === '021',
            'taxpayer_type' => $invoice->taxpayer_type,
            'payment_type' => $invoice->payment_type,
            'invoice_scope' => $invoice->invoice_scope,
        ];
    }

    private function failIfInvalid(array $validation, array $checks): void
    {
        $errors = $validation['errors'] ?? [];
        foreach (['source_id_exists', 'seller_tax_number_exists', 'icv_exists', 'uuid_exists', 'seller_supplier_party_exists', 'invoice_type_code_name_valid'] as $required) {
            if (! $checks[$required]) {
                $errors[] = str_replace('_', ' ', $required).' failed';
            }
        }
        if ($checks['buyer_fake_id_exists']) {
            $errors[] = 'Fake customer tax/national number detected in XML.';
        }
        if ($errors !== []) {
            throw new RuntimeException(implode(PHP_EOL, $errors));
        }
    }
}
