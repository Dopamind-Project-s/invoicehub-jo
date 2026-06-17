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

    public function __construct(private readonly InvoiceTypeCodeService $typeCodes) {}

    public function build(Invoice $invoice): string
    {
        $invoice->loadMissing(['supplier', 'customer', 'items']);
        $currencyId = (string) config('services.jofotara.amount_currency_id', 'JO');
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;

        $root = $document->createElementNS('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2', 'Invoice');
        $root->setAttribute('xmlns:cac', self::CAC);
        $root->setAttribute('xmlns:cbc', self::CBC);
        $root->setAttribute('xmlns:ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $document->appendChild($root);

        $this->el($document, $root, 'cbc:ProfileID', 'reporting:1.0');
        $this->el($document, $root, 'cbc:ID', $invoice->invoice_number);
        $this->el($document, $root, 'cbc:UUID', $invoice->uuid);
        $this->el($document, $root, 'cbc:IssueDate', $invoice->issue_date->format('Y-m-d'));
        $this->el($document, $root, 'cbc:IssueTime', (string) $invoice->issue_time);
        $this->el($document, $root, 'cbc:InvoiceTypeCode', '388', ['name' => $this->typeCodes->nameFor($invoice)]);
        $this->el($document, $root, 'cbc:DocumentCurrencyCode', $invoice->currency_code ?: 'JOD');
        $this->el($document, $root, 'cbc:TaxCurrencyCode', $invoice->currency_code ?: 'JOD');

        $this->icvReference($document, $root, (string) $invoice->icv);
        $this->binaryReference($document, $root, 'PIH', $this->previousHash($invoice));
        if (filter_var(config('services.jofotara.include_qr_in_xml', false), FILTER_VALIDATE_BOOLEAN) || filled($invoice->qr_code)) {
            $this->binaryReference($document, $root, 'QR', (string) $invoice->qr_code);
        }

        $this->supplierParty($document, $root, $invoice);
        $this->customerParty($document, $root, $invoice);
        $this->sellerSupplierParty($document, $root, (string) $invoice->supplier?->jofotara_source_id);
        $this->allowanceCharge($document, $root, (string) $invoice->discount_amount, $currencyId);
        $this->taxTotal($document, $root, (string) $invoice->tax_amount, $currencyId);
        $this->legalMonetaryTotal($document, $root, $invoice, $currencyId);
        $this->invoiceLines($document, $root, $invoice, $currencyId);

        return $document->saveXML() ?: '';
    }

    private function supplierParty(DOMDocument $document, DOMElement $root, Invoice $invoice): void
    {
        $supplier = $invoice->supplier;
        $node = $this->el($document, $root, 'cac:AccountingSupplierParty');
        $party = $this->el($document, $node, 'cac:Party');
        $address = $this->el($document, $party, 'cac:PostalAddress');
        $this->el($document, $address, 'cbc:StreetName', $supplier?->street ?: $supplier?->city ?: 'Jordan');
        $this->el($document, $address, 'cbc:BuildingNumber', $supplier?->building_no ?: '-');
        $country = $this->el($document, $address, 'cac:Country');
        $this->el($document, $country, 'cbc:IdentificationCode', $supplier?->country_code ?: 'JO');
        $tax = $this->el($document, $party, 'cac:PartyTaxScheme');
        $this->el($document, $tax, 'cbc:CompanyID', (string) $supplier?->tax_number);
        $scheme = $this->el($document, $tax, 'cac:TaxScheme');
        $this->el($document, $scheme, 'cbc:ID', 'VAT');
        $legal = $this->el($document, $party, 'cac:PartyLegalEntity');
        $this->el($document, $legal, 'cbc:RegistrationName', $supplier?->legal_name_ar ?: $supplier?->legal_name_en ?: '');
    }

    private function sellerSupplierParty(DOMDocument $document, DOMElement $root, string $sourceId): void
    {
        $seller = $this->el($document, $root, 'cac:SellerSupplierParty');
        $party = $this->el($document, $seller, 'cac:Party');
        $identification = $this->el($document, $party, 'cac:PartyIdentification');
        $this->el($document, $identification, 'cbc:ID', $sourceId);
    }

    private function customerParty(DOMDocument $document, DOMElement $root, Invoice $invoice): void
    {
        $customer = $invoice->customer;
        $node = $this->el($document, $root, 'cac:AccountingCustomerParty');
        $party = $this->el($document, $node, 'cac:Party');
        $buyerId = $this->realBuyerId($customer?->tax_number, $customer?->national_number);
        if ($buyerId !== null) {
            $identification = $this->el($document, $party, 'cac:PartyIdentification');
            $this->el($document, $identification, 'cbc:ID', $buyerId);
        }
        $address = $this->el($document, $party, 'cac:PostalAddress');
        $this->el($document, $address, 'cbc:PostalZone', $customer?->postal_code ?: '-');
        $country = $this->el($document, $address, 'cac:Country');
        $this->el($document, $country, 'cbc:IdentificationCode', $customer?->country_code ?: 'JO');
        $tax = $this->el($document, $party, 'cac:PartyTaxScheme');
        $scheme = $this->el($document, $tax, 'cac:TaxScheme');
        $this->el($document, $scheme, 'cbc:ID', 'VAT');
        $legal = $this->el($document, $party, 'cac:PartyLegalEntity');
        $this->el($document, $legal, 'cbc:RegistrationName', $customer?->name ?: 'عميل نقدي');
        $contact = $this->el($document, $node, 'cac:AccountingContact');
        $this->el($document, $contact, 'cbc:Telephone', $customer?->phone ?: '-');
    }

    private function invoiceLines(DOMDocument $document, DOMElement $root, Invoice $invoice, string $currencyId): void
    {
        foreach ($invoice->items as $index => $item) {
            $line = $this->el($document, $root, 'cac:InvoiceLine');
            $this->el($document, $line, 'cbc:ID', (string) ($index + 1));
            $this->el($document, $line, 'cbc:InvoicedQuantity', (string) $item->quantity);
            $this->el($document, $line, 'cbc:LineExtensionAmount', (string) $item->line_extension_amount, ['currencyID' => $currencyId]);
            $taxTotal = $this->el($document, $line, 'cac:TaxTotal');
            $this->el($document, $taxTotal, 'cbc:TaxAmount', (string) $item->tax_amount, ['currencyID' => $currencyId]);
            $this->el($document, $taxTotal, 'cbc:RoundingAmount', (string) $item->line_total, ['currencyID' => $currencyId]);
            $itemNode = $this->el($document, $line, 'cac:Item');
            $this->el($document, $itemNode, 'cbc:Name', $item->description);
            $category = $this->el($document, $itemNode, 'cac:ClassifiedTaxCategory');
            $this->el($document, $category, 'cbc:ID', $item->tax_category ?: 'S');
            $this->el($document, $category, 'cbc:Percent', (string) $item->tax_percent);
            $scheme = $this->el($document, $category, 'cac:TaxScheme');
            $this->el($document, $scheme, 'cbc:ID', 'VAT');
            $price = $this->el($document, $line, 'cac:Price');
            $this->el($document, $price, 'cbc:PriceAmount', (string) $item->unit_price, ['currencyID' => $currencyId]);
            $this->el($document, $price, 'cbc:BaseQuantity', '1');
            $this->allowanceCharge($document, $price, (string) $item->discount, $currencyId);
        }
    }

    private function legalMonetaryTotal(DOMDocument $document, DOMElement $root, Invoice $invoice, string $currencyId): void
    {
        $legal = $this->el($document, $root, 'cac:LegalMonetaryTotal');
        $this->el($document, $legal, 'cbc:LineExtensionAmount', (string) $invoice->taxable_amount, ['currencyID' => $currencyId]);
        $this->el($document, $legal, 'cbc:TaxExclusiveAmount', (string) $invoice->subtotal, ['currencyID' => $currencyId]);
        $this->el($document, $legal, 'cbc:TaxInclusiveAmount', (string) $invoice->total_amount, ['currencyID' => $currencyId]);
        $this->el($document, $legal, 'cbc:AllowanceTotalAmount', (string) $invoice->discount_amount, ['currencyID' => $currencyId]);
        $this->el($document, $legal, 'cbc:PayableAmount', (string) $invoice->payable_amount, ['currencyID' => $currencyId]);
    }

    private function allowanceCharge(DOMDocument $document, DOMElement $parent, string $amount, string $currencyId): void
    {
        $allowance = $this->el($document, $parent, 'cac:AllowanceCharge');
        $this->el($document, $allowance, 'cbc:ChargeIndicator', 'false');
        $this->el($document, $allowance, 'cbc:AllowanceChargeReason', 'discount');
        $this->el($document, $allowance, 'cbc:Amount', $amount, ['currencyID' => $currencyId]);
    }

    private function taxTotal(DOMDocument $document, DOMElement $root, string $amount, string $currencyId): void
    {
        $taxTotal = $this->el($document, $root, 'cac:TaxTotal');
        $this->el($document, $taxTotal, 'cbc:TaxAmount', $amount, ['currencyID' => $currencyId]);
    }

    private function icvReference(DOMDocument $document, DOMElement $root, string $icv): void
    {
        $reference = $this->el($document, $root, 'cac:AdditionalDocumentReference');
        $this->el($document, $reference, 'cbc:ID', 'ICV');
        $this->el($document, $reference, 'cbc:UUID', $icv);
    }

    private function binaryReference(DOMDocument $document, DOMElement $root, string $id, string $value): void
    {
        $reference = $this->el($document, $root, 'cac:AdditionalDocumentReference');
        $this->el($document, $reference, 'cbc:ID', $id);
        $attachment = $this->el($document, $reference, 'cac:Attachment');
        $this->el($document, $attachment, 'cbc:EmbeddedDocumentBinaryObject', $value, ['mimeCode' => 'text/plain']);
    }

    private function previousHash(Invoice $invoice): string
    {
        return (string) ($invoice->previous_invoice_hash ?: config('services.jofotara.initial_pih', ''));
    }

    private function realBuyerId(?string $taxNumber, ?string $nationalNumber): ?string
    {
        foreach ([$taxNumber, $nationalNumber] as $value) {
            if (filled($value) && ! preg_match('/^0+$/', (string) $value)) {
                return (string) $value;
            }
        }

        return null;
    }

    private function el(DOMDocument $document, DOMElement $parent, string $name, ?string $value = null, array $attributes = []): DOMElement
    {
        $element = $document->createElement($name);
        foreach ($attributes as $key => $attributeValue) {
            $element->setAttribute($key, (string) $attributeValue);
        }
        if ($value !== null) {
            $element->appendChild($document->createTextNode($value));
        }
        $parent->appendChild($element);

        return $element;
    }
}
