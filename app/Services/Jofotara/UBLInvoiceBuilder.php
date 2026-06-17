<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

use App\Models\Invoice;
use DOMDocument;
use DOMElement;

class UBLInvoiceBuilder
{
    private const CBC = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';

    private const CAC = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';

    public function build(Invoice $invoice): string
    {
        $invoice->loadMissing(['supplier', 'customer', 'items']);
        $d = new DOMDocument('1.0', 'UTF-8');
        $d->formatOutput = true;
        $root = $d->createElementNS('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2', 'Invoice');
        $root->setAttribute('xmlns:cac', self::CAC);
        $root->setAttribute('xmlns:cbc', self::CBC);
        $root->setAttribute('xmlns:ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $d->appendChild($root);
        foreach ([['cbc:ProfileID', 'reporting:1.0'], ['cbc:ID', $invoice->invoice_number], ['cbc:UUID', $invoice->uuid], ['cbc:IssueDate', $invoice->issue_date->format('Y-m-d')], ['cbc:IssueTime', (string) $invoice->issue_time], ['cbc:InvoiceTypeCode', $invoice->invoice_type === 'SIMPLIFIED' ? '388' : '388'], ['cbc:DocumentCurrencyCode', $invoice->currency_code]] as [$n,$v]) {
            $this->el($d, $root, $n, (string) $v);
        }
        $this->documentReference($d, $root, 'ICV', (string) $invoice->icv);
        $this->documentReference($d, $root, 'PIH', (string) $invoice->previous_invoice_hash);
        $this->documentReference($d, $root, 'QR', (string) $invoice->qr_code);
        $this->party($d, $root, 'cac:AccountingSupplierParty', $invoice->supplier?->legal_name_ar ?? '', $invoice->supplier?->tax_number, $invoice->supplier?->country_code ?? 'JO', $invoice->supplier?->city);
        $this->party($d, $root, 'cac:AccountingCustomerParty', $invoice->customer?->name ?? 'Cash Customer', $invoice->customer?->tax_number, $invoice->customer?->country_code ?? 'JO', $invoice->customer?->city);
        $taxTotal = $this->el($d, $root, 'cac:TaxTotal');
        $this->el($d, $taxTotal, 'cbc:TaxAmount', $invoice->tax_amount, ['currencyID' => $invoice->currency_code]);
        $legal = $this->el($d, $root, 'cac:LegalMonetaryTotal');
        foreach ([['cbc:LineExtensionAmount', $invoice->taxable_amount], ['cbc:TaxExclusiveAmount', $invoice->taxable_amount], ['cbc:TaxInclusiveAmount', $invoice->total_amount], ['cbc:AllowanceTotalAmount', $invoice->discount_amount], ['cbc:PayableAmount', $invoice->payable_amount]] as [$n,$v]) {
            $this->el($d, $legal, $n, (string) $v, ['currencyID' => $invoice->currency_code]);
        }
        foreach ($invoice->items as $i => $item) {
            $line = $this->el($d, $root, 'cac:InvoiceLine');
            $this->el($d, $line, 'cbc:ID', (string) ($i + 1));
            $this->el($d, $line, 'cbc:InvoicedQuantity', (string) $item->quantity, ['unitCode' => 'PCE']);
            $this->el($d, $line, 'cbc:LineExtensionAmount', (string) $item->line_extension_amount, ['currencyID' => $invoice->currency_code]);
            $tax = $this->el($d, $line, 'cac:TaxTotal');
            $this->el($d, $tax, 'cbc:TaxAmount', (string) $item->tax_amount, ['currencyID' => $invoice->currency_code]);
            $it = $this->el($d, $line, 'cac:Item');
            $this->el($d, $it, 'cbc:Name', $item->description);
            $price = $this->el($d, $line, 'cac:Price');
            $this->el($d, $price, 'cbc:PriceAmount', (string) $item->unit_price, ['currencyID' => $invoice->currency_code]);
        }

        return $d->saveXML() ?: '';
    }

    private function party(DOMDocument $d, DOMElement $root, string $node, string $name, ?string $tax, string $country, ?string $city): void
    {
        $asp = $this->el($d, $root, $node);
        $p = $this->el($d, $asp, 'cac:Party');
        $addr = $this->el($d, $p, 'cac:PostalAddress');
        $this->el($d, $addr, 'cbc:CityName', (string) $city);
        $c = $this->el($d, $addr, 'cac:Country');
        $this->el($d, $c, 'cbc:IdentificationCode', $country);
        if ($tax) {
            $pts = $this->el($d, $p, 'cac:PartyTaxScheme');
            $this->el($d, $pts, 'cbc:CompanyID', $tax);
            $ts = $this->el($d, $pts, 'cac:TaxScheme');
            $this->el($d, $ts, 'cbc:ID', 'VAT');
        } $le = $this->el($d, $p, 'cac:PartyLegalEntity');
        $this->el($d, $le, 'cbc:RegistrationName', $name);
    }

    private function documentReference(DOMDocument $d, DOMElement $root, string $id, string $value): void
    {
        $r = $this->el($d, $root, 'cac:AdditionalDocumentReference');
        $this->el($d, $r, 'cbc:ID', $id);
        $a = $this->el($d, $r, 'cac:Attachment');
        $this->el($d, $a, 'cbc:EmbeddedDocumentBinaryObject', $value, ['mimeCode' => 'text/plain']);
    }

    private function el(DOMDocument $d, DOMElement $p, string $name, ?string $value = null, array $attrs = []): DOMElement
    {
        $e = $d->createElement($name);
        foreach ($attrs as $k => $v) {
            $e->setAttribute($k, (string) $v);
        } if ($value !== null) {
            $e->appendChild($d->createTextNode($value));
        } $p->appendChild($e);

        return $e;
    }
}
