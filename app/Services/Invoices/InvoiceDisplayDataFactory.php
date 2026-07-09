<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Services\Jofotara\QRCodeService;
use Stringable;

class InvoiceDisplayDataFactory
{
    public function __construct(private readonly QRCodeService $qr) {}

    /** @return array<string,mixed> */
    public function make(Invoice $invoice): array
    {
        $invoice->loadMissing(['company.settings', 'contact', 'items.product']);
        $company = $invoice->company;
        $contact = $invoice->contact;
        $currency = $this->text($invoice->currency ?: $invoice->currency_code ?: $company?->default_currency ?: 'JOD') ?: 'JOD';

        return [
            'invoice' => [
                'number' => $this->text($invoice->invoice_number),
                'uuid' => $this->text($invoice->jofotara_uuid ?: $invoice->uuid),
                'issue_date' => $invoice->issue_date?->format('Y-m-d') ?: $this->text($invoice->issue_date),
                'issue_time' => $this->text($invoice->issue_time),
                'due_date' => $invoice->due_date?->format('Y-m-d'),
                'type' => $this->invoiceType($invoice->invoice_type),
                'status' => $this->status($invoice->status),
                'jofotara_status' => $this->jofotaraStatus($invoice->jofotara_status),
                'validation' => $this->jofotaraStatus($invoice->jofotara_validation_result),
                'payment' => $this->paymentLabel($invoice),
                'currency' => $currency,
                'notes' => $this->notes($invoice->notes),
            ],
            'company' => [
                'name' => $this->text($company?->name_ar ?: $company?->trade_name ?: $company?->legal_name_ar ?: $company?->name_en),
                'legal_name' => $this->text($company?->legal_name_ar),
                'tax_number' => $this->text($company?->tax_number),
                'national_number' => $this->text($company?->national_number ?: $company?->registration_number),
                'source_id' => $this->text($company?->jofotara_source_id),
                'address' => $this->join([$company?->city, $company?->street, $company?->building_no, $company?->postal_code]),
                'phone' => $this->text($company?->phone),
                'email' => $this->text($company?->email),
                'logo' => $this->assetPath($company?->logo_path),
            ],
            'customer' => [
                'name' => $this->text($contact?->name_ar ?: $contact?->name_en) ?: 'عميل نقدي',
                'tax_number' => $this->text($contact?->tax_number),
                'national_number' => $this->text($contact?->national_number),
                'phone' => $this->text($contact?->phone),
                'address' => $this->join([$contact?->city, $contact?->address, $contact?->country]),
            ],
            'items' => $invoice->items->map(fn ($item): array => [
                'description' => $this->text($item->description),
                'product' => $this->text($item->product?->name_ar ?: $item->product?->name_en),
                'quantity' => $this->decimal($item->quantity, 3),
                'unit_price' => $this->money($item->unit_price, $currency),
                'discount' => $this->money($item->discount_amount ?: $item->discount, $currency),
                'tax_percent' => $this->decimal($item->tax_percent, 2).'%',
                'tax' => $this->money($item->tax_amount, $currency),
                'total' => $this->money($item->line_total, $currency),
            ]),
            'totals' => [
                'subtotal' => $this->money($invoice->subtotal ?: $invoice->total_amount, $currency),
                'discount' => $this->money($invoice->discount_total ?: $invoice->discount_amount, $currency),
                'taxable' => $this->money($invoice->taxable_amount, $currency),
                'tax' => $this->money($invoice->tax_total ?: $invoice->tax_amount, $currency),
                'grand' => $this->money($invoice->grand_total ?: $invoice->payable_amount, $currency),
                'payable' => $this->money($invoice->payable_amount ?: $invoice->grand_total, $currency),
            ],
            'qr' => [
                'official_value' => $this->qr->officialValue($invoice),
                'data_uri' => $this->qr->dataUri($invoice),
                'available' => $this->qr->hasOfficialQr($invoice),
            ],
        ];
    }

    private function text(mixed $value): ?string
    {
        if ($value instanceof Stringable) {
            $value = (string) $value;
        }
        if (! is_scalar($value)) {
            return null;
        }
        $text = trim((string) $value);
        if ($text === '' || $this->looksStructured($text)) {
            return null;
        }

        return $text;
    }

    private function notes(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = data_get($value, 'note') ?: data_get($value, 'notes') ?: data_get($value, 'text');
        }
        if (is_string($value) && $this->looksStructured($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = data_get($decoded, 'note') ?: data_get($decoded, 'notes') ?: data_get($decoded, 'text');
            }
        }

        return $this->text($value);
    }

    private function looksStructured(string $text): bool
    {
        $trim = trim($text);

        return $trim === 'Array' || $trim === '[object Object]' || str_starts_with($trim, '<Invoice') || str_starts_with($trim, '<?xml') || ((str_starts_with($trim, '{') && str_ends_with($trim, '}')) || (str_starts_with($trim, '[') && str_ends_with($trim, ']')));
    }

    private function join(array $parts): ?string
    {
        return $this->text(collect($parts)->map(fn ($v) => $this->text($v))->filter()->implode(' - '));
    }

    private function decimal(mixed $value, int $decimals = 3): string
    {
        return number_format((float) $value, $decimals, '.', ',');
    }

    private function money(mixed $value, string $currency): string
    {
        return $this->decimal($value, 3).' '.$currency;
    }

    private function assetPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $path = ltrim($path, '/');

        return str_starts_with($path, 'public/') ? substr($path, 7) : $path;
    }

    private function invoiceType(?string $type): string
    {
        return ['tax_invoice' => 'فاتورة ضريبية', 'simplified_invoice' => 'فاتورة مبسطة', 'credit_note' => 'إشعار دائن', 'debit_note' => 'إشعار مدين'][$type] ?? 'فاتورة';
    }

    private function status(?string $status): string
    {
        return ['draft' => 'مسودة', 'ready' => 'جاهزة للإرسال', 'submitted' => 'مرسلة', 'cancelled' => 'ملغاة', 'pending' => 'مراجعة داخلية', 'approved' => 'معتمدة'][$status] ?? ($this->text($status) ?: '—');
    }

    private function jofotaraStatus(?string $status): string
    {
        return ['NOT_SUBMITTED' => 'غير مرسلة', 'ERROR' => 'فشل', 'REJECTED' => 'مرفوضة', 'SUBMITTED' => 'مرسلة', 'ACCEPTED' => 'مقبولة', 'PASS' => 'ناجحة'][$status] ?? ($this->text($status) ?: 'غير مرسلة');
    }

    private function paymentLabel(Invoice $invoice): string
    {
        return ['cash' => 'نقدي', 'receivable' => 'ذمم', 'credit' => 'ذمم'][$invoice->payment_type] ?? 'ذمم';
    }
}
