<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

use App\Models\Invoice;

class UBLValidationService
{
    public function validateInvoice(Invoice $invoice): array
    {
        $errors = [];
        foreach (['uuid', 'invoice_number', 'icv', 'issue_date', 'supplier_id', 'currency_code'] as $f) {
            if (blank($invoice->{$f})) {
                $errors[] = "$f is required";
            }
        } if (! $invoice->supplier) {
            $errors[] = 'Supplier is required';
        } if ($invoice->items->isEmpty()) {
            $errors[] = 'At least one invoice line is required';
        } if (bccomp((string) $invoice->payable_amount, '0', 6) < 0) {
            $errors[] = 'Payable amount cannot be negative';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    public function validateXml(string $xml): array
    {
        $errors = [];
        $dom = new \DOMDocument;
        if (! @$dom->loadXML($xml)) {
            $errors[] = 'XML is not well formed';
        } foreach (['ProfileID', 'ID', 'UUID', 'IssueDate', 'InvoiceTypeCode', 'DocumentCurrencyCode'] as $tag) {
            if ($dom->getElementsByTagName($tag)->length === 0) {
                $errors[] = "Missing $tag";
            }
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}
