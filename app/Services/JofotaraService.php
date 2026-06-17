<?php

namespace App\Services;

use App\Models\Invoice;
use DOMDocument;
use DOMElement;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class JofotaraService
{
    private const CBC = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';

    private const CAC = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';

    public function submitInvoice(Invoice $invoice): array
    {
        $invoice->load(['seller', 'customer', 'items']);
        $xml = $this->buildUblXml($invoice);
        $encodedXml = base64_encode($xml);
        $payload = ['invoice' => $encodedXml];
        Storage::disk('local')->put('jofotara/last-submission-'.$invoice->id.'.xml', $xml);
        Storage::disk('local')->put('jofotara/last-payload-'.$invoice->id.'.json', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        Log::info('Submitting invoice to JoFotara', [
            'invoice_id' => $invoice->id,
            'seller_id' => $invoice->seller_id,
            'url' => config('services.jofotara.url'),
            'xml_length' => strlen($xml),
            'base64_length' => strlen($encodedXml),
            'client_id_exists' => filled($this->sellerConfig($invoice, 'client_id')),
            'secret_key_length' => strlen((string) $this->sellerConfig($invoice, 'secret_key')),
            'source_id' => $this->sellerConfig($invoice, 'source_id'),
            'tax_number' => $this->sellerConfig($invoice, 'tax_number'),
        ]);
        $response = Http::withHeaders([
            'Client-Id' => $this->sellerConfig($invoice, 'client_id'),
            'Secret-Key' => $this->sellerConfig($invoice, 'secret_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post(config('services.jofotara.url'), $payload);
        Log::info('JoFotara HTTP response received', [
            'invoice_id' => $invoice->id,
            'seller_id' => $invoice->seller_id,
            'http_status' => $response->status(),
            'response_headers' => $response->headers(),
            'raw_response_body' => $response->body(),
            'response_length' => strlen($response->body()),
        ]);
        $parsed = $this->parseResponse($response);
        $invoice->forceFill([
            'status' => $parsed['accepted'] ? 'accepted' : 'rejected',
            'jofotara_uuid' => $parsed['uuid'],
            'jofotara_qr' => $parsed['qr'],
            'jofotara_response' => $parsed['raw_response'],
            'submitted_at' => $parsed['accepted'] ? now() : null,
        ])->save();

        return $parsed;
    }

    public function buildUblXml(Invoice $invoice): string
    {
        $invoice->loadMissing(['seller', 'customer', 'items']);

        $document = new DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;

        $root = $document->createElementNS('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2', 'Invoice');
        $root->setAttribute('xmlns:cac', self::CAC);
        $root->setAttribute('xmlns:cbc', self::CBC);
        $root->setAttribute('xmlns:ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $document->appendChild($root);

        $this->append($document, $root, 'cbc:UBLVersionID', '2.1');
        // TODO: Confirm whether JoFotara requires CustomizationID for this invoice type against the latest official ISTD XML examples before production use.
        if (filter_var(config('services.jofotara.include_customization_id'), FILTER_VALIDATE_BOOLEAN)) {
            $this->append($document, $root, 'cbc:CustomizationID', 'urn:cen.eu:en16931:2017');
        }
        $this->append($document, $root, 'cbc:ProfileID', 'reporting:1.0');
        $this->append($document, $root, 'cbc:ID', $this->safeText($invoice->invoice_number));
        $this->append($document, $root, 'cbc:UUID', $this->safeText($invoice->jofotara_uuid, (string) $invoice->id));
        $this->append($document, $root, 'cbc:IssueDate', $invoice->invoice_date->format('Y-m-d'));
        // TODO: Confirm InvoiceTypeCode and attributes for income/sales/return invoice scenarios with official JoFotara samples.
        $this->append($document, $root, 'cbc:InvoiceTypeCode', '388', ['name' => '012']);
        $this->append($document, $root, 'cbc:DocumentCurrencyCode', 'JOD');
        $this->append($document, $root, 'cbc:TaxCurrencyCode', 'JOD');

        $this->addSupplierParty($document, $root, $invoice);
        $this->addCustomerParty($document, $root, $invoice);
        $this->addTaxTotal($document, $root, $invoice);
        $this->addLegalMonetaryTotal($document, $root, $invoice);
        $this->addInvoiceLines($document, $root, $invoice);

        return $document->saveXML() ?: '';
    }

    public function parseResponse(Response $response): array
    {
        $body = $response->json() ?? ['raw' => $response->body()];
        $rawResponse = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: $response->body();

        return [
            'accepted' => $response->successful(),
            'uuid' => data_get($body, 'uuid') ?? data_get($body, 'invoice_uuid'),
            'qr' => data_get($body, 'qr') ?? data_get($body, 'qr_code'),
            'body' => $body,
            'raw_response' => $rawResponse,
            'errors' => data_get($body, 'errors'),
        ];
    }

    public function money($value): string
    {
        return number_format((float) $value, 3, '.', '');
    }

    public function percent($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    public function addTaxScheme($node): DOMElement
    {
        $document = $node instanceof DOMDocument ? $node : $node->ownerDocument;
        $taxScheme = $document->createElementNS(self::CAC, 'cac:TaxScheme');
        $this->append($document, $taxScheme, 'cbc:ID', 'VAT');

        return $node->appendChild($taxScheme);
    }

    public function safeText($value, $fallback = ''): string
    {
        $value = is_scalar($value) ? trim((string) $value) : '';

        return $value !== '' ? $value : (string) $fallback;
    }

    private function addSupplierParty(DOMDocument $document, DOMElement $root, Invoice $invoice): void
    {
        $supplier = $this->append($document, $root, 'cac:AccountingSupplierParty');
        $party = $this->append($document, $supplier, 'cac:Party');
        $this->append($document, $party, 'cbc:EndpointID', $this->safeText($this->sellerConfig($invoice, 'tax_number'), '000000000'), ['schemeID' => 'TN']);

        $identification = $this->append($document, $party, 'cac:PartyIdentification');
        $this->append($document, $identification, 'cbc:ID', $this->safeText($this->sellerConfig($invoice, 'source_id'), '0'));

        $partyName = $this->append($document, $party, 'cac:PartyName');
        $this->append($document, $partyName, 'cbc:Name', $this->safeText($this->sellerConfig($invoice, 'seller_name'), 'اسم البائع'));

        $taxScheme = $this->append($document, $party, 'cac:PartyTaxScheme');
        $this->append($document, $taxScheme, 'cbc:CompanyID', $this->safeText($this->sellerConfig($invoice, 'tax_number'), '000000000'));
        $this->addTaxScheme($taxScheme);

        $legal = $this->append($document, $party, 'cac:PartyLegalEntity');
        $this->append($document, $legal, 'cbc:RegistrationName', $this->safeText($this->sellerConfig($invoice, 'seller_name'), 'اسم البائع'));
    }

    private function addCustomerParty(DOMDocument $document, DOMElement $root, Invoice $invoice): void
    {
        $customer = $this->append($document, $root, 'cac:AccountingCustomerParty');
        $party = $this->append($document, $customer, 'cac:Party');
        $customerTaxNumber = $this->realIdentifier($invoice->customer?->tax_number);
        $customerNationalNumber = $this->realIdentifier($invoice->customer?->national_number);

        if ($customerTaxNumber || $customerNationalNumber) {
            $this->append($document, $party, 'cbc:EndpointID', $customerTaxNumber ?: $customerNationalNumber, ['schemeID' => $customerTaxNumber ? 'TN' : 'NIN']);
        }

        $partyName = $this->append($document, $party, 'cac:PartyName');
        $this->append($document, $partyName, 'cbc:Name', $this->safeText($invoice->customer?->name, 'عميل نقدي'));

        if ($customerTaxNumber) {
            $taxScheme = $this->append($document, $party, 'cac:PartyTaxScheme');
            $this->append($document, $taxScheme, 'cbc:CompanyID', $customerTaxNumber);
            $this->addTaxScheme($taxScheme);
        }
    }

    private function addTaxTotal(DOMDocument $document, DOMElement $root, Invoice $invoice): void
    {
        $taxTotal = $this->append($document, $root, 'cac:TaxTotal');
        $this->append($document, $taxTotal, 'cbc:TaxAmount', $this->money($invoice->tax_total), ['currencyID' => 'JOD']);

        $groupedTaxes = $invoice->items->groupBy(fn ($item) => $this->percent($item->tax_rate));
        foreach ($groupedTaxes as $rate => $items) {
            $taxable = $items->sum(fn ($item) => (float) $item->quantity * (float) $item->unit_price);
            $tax = $items->sum('tax_amount');
            $subtotal = $this->append($document, $taxTotal, 'cac:TaxSubtotal');
            $this->append($document, $subtotal, 'cbc:TaxableAmount', $this->money($taxable), ['currencyID' => 'JOD']);
            $this->append($document, $subtotal, 'cbc:TaxAmount', $this->money($tax), ['currencyID' => 'JOD']);
            $category = $this->append($document, $subtotal, 'cac:TaxCategory');
            // TODO: Confirm category ID for zero-rated/exempt/non-taxable JoFotara invoices against official samples.
            $this->append($document, $category, 'cbc:ID', ((float) $rate) > 0 ? 'S' : 'Z');
            $this->append($document, $category, 'cbc:Percent', $this->percent($rate));
            $this->addTaxScheme($category);
        }
    }

    private function addLegalMonetaryTotal(DOMDocument $document, DOMElement $root, Invoice $invoice): void
    {
        $total = $this->append($document, $root, 'cac:LegalMonetaryTotal');
        $this->append($document, $total, 'cbc:LineExtensionAmount', $this->money($invoice->subtotal), ['currencyID' => 'JOD']);
        $this->append($document, $total, 'cbc:TaxExclusiveAmount', $this->money($invoice->subtotal - $invoice->discount_total), ['currencyID' => 'JOD']);
        $this->append($document, $total, 'cbc:TaxInclusiveAmount', $this->money($invoice->total), ['currencyID' => 'JOD']);
        $this->append($document, $total, 'cbc:AllowanceTotalAmount', $this->money($invoice->discount_total), ['currencyID' => 'JOD']);
        $this->append($document, $total, 'cbc:PrepaidAmount', $this->money(0), ['currencyID' => 'JOD']);
        $this->append($document, $total, 'cbc:PayableAmount', $this->money($invoice->total), ['currencyID' => 'JOD']);
    }

    private function addInvoiceLines(DOMDocument $document, DOMElement $root, Invoice $invoice): void
    {
        foreach ($invoice->items as $index => $item) {
            $lineBase = (float) $item->quantity * (float) $item->unit_price;
            $line = $this->append($document, $root, 'cac:InvoiceLine');
            $this->append($document, $line, 'cbc:ID', (string) ($index + 1));
            $this->append($document, $line, 'cbc:InvoicedQuantity', $this->money($item->quantity), ['unitCode' => 'PCE']);
            $this->append($document, $line, 'cbc:LineExtensionAmount', $this->money($lineBase), ['currencyID' => 'JOD']);

            $taxTotal = $this->append($document, $line, 'cac:TaxTotal');
            $this->append($document, $taxTotal, 'cbc:TaxAmount', $this->money($item->tax_amount), ['currencyID' => 'JOD']);
            $this->append($document, $taxTotal, 'cbc:RoundingAmount', $this->money($item->line_total), ['currencyID' => 'JOD']);

            $itemNode = $this->append($document, $line, 'cac:Item');
            $this->append($document, $itemNode, 'cbc:Name', $this->safeText($item->description, 'بند فاتورة'));
            $category = $this->append($document, $itemNode, 'cac:ClassifiedTaxCategory');
            $this->append($document, $category, 'cbc:ID', ((float) $item->tax_rate) > 0 ? 'S' : 'Z');
            $this->append($document, $category, 'cbc:Percent', $this->percent($item->tax_rate));
            $this->addTaxScheme($category);

            $price = $this->append($document, $line, 'cac:Price');
            $this->append($document, $price, 'cbc:PriceAmount', $this->money($item->unit_price), ['currencyID' => 'JOD']);
            $this->append($document, $price, 'cbc:BaseQuantity', $this->money(1), ['unitCode' => 'PCE']);
        }
    }

    private function realIdentifier($value): ?string
    {
        $value = $this->safeText($value);

        return $value !== '' && $value !== '000000000' ? $value : null;
    }

    private function append(DOMDocument $document, DOMElement $parent, string $name, ?string $value = null, array $attributes = []): DOMElement
    {
        $namespace = str_starts_with($name, 'cbc:') ? self::CBC : (str_starts_with($name, 'cac:') ? self::CAC : null);
        $element = $namespace ? $document->createElementNS($namespace, $name) : $document->createElement($name);
        if ($value !== null) {
            $element->appendChild($document->createTextNode($value));
        }
        foreach ($attributes as $attribute => $attributeValue) {
            $element->setAttribute($attribute, (string) $attributeValue);
        }

        return $parent->appendChild($element);
    }

    public function credential(Invoice $invoice, string $key): ?string
    {
        return $this->sellerConfig($invoice, $key);
    }

    private function sellerConfig(Invoice $invoice, string $key): ?string
    {
        return match ($key) {
            'client_id' => $invoice->seller?->jofotara_client_id ?: config('services.jofotara.client_id'),
            'secret_key' => $invoice->seller?->jofotara_secret_key ?: config('services.jofotara.secret_key'),
            'source_id' => $invoice->seller?->jofotara_source_id ?: $invoice->seller?->income_source_sequence ?: config('services.jofotara.source_id'),
            'tax_number' => $invoice->seller?->tax_number ?: config('services.jofotara.tax_number'),
            'seller_name' => $invoice->seller?->name ?: config('services.jofotara.seller_name'),
            default => null,
        };
    }
}
