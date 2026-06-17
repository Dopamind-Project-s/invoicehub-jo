<?php

namespace App\Services;

use App\Models\Invoice;
use DOMDocument;
use DOMElement;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JofotaraService
{
    private const CBC = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';

    private const CAC = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';

    public function submitInvoice(Invoice $invoice): array
    {
        $invoice->load(['seller', 'customer', 'items']);
        $this->ensureJofotaraIdentifiers($invoice);
        $xml = $this->buildUblXml($invoice);
        $encodedXml = base64_encode($xml);
        $payload = ['invoice' => $encodedXml];
        $xmlPath = 'jofotara/last-submission-'.$invoice->id.'.xml';
        $payloadPath = 'jofotara/last-payload-'.$invoice->id.'.json';
        $this->saveDebugFiles($xmlPath, $xml, $payloadPath, $payload);
        $xmlInfo = $this->debugFileInfo($xmlPath);
        $payloadInfo = $this->debugFileInfo($payloadPath);
        Log::info('Submitting invoice to JoFotara', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'jofotara_invoice_number' => $invoice->jofotara_invoice_number,
            'jofotara_xml_uuid' => $invoice->jofotara_xml_uuid,
            'seller_id' => $invoice->seller_id,
            'url' => config('services.jofotara.url'),
            'xml_length' => strlen($xml),
            'base64_length' => strlen($encodedXml),
            'xml_file_exists' => $xmlInfo['exists'],
            'payload_file_exists' => $payloadInfo['exists'],
            'xml_file_size' => $xmlInfo['size'],
            'payload_file_size' => $payloadInfo['size'],
            'client_id_exists' => filled($this->sellerConfig($invoice, 'client_id')),
            'secret_key_length' => strlen((string) $this->sellerConfig($invoice, 'secret_key')),
            'source_id' => $this->sellerConfig($invoice, 'source_id'),
            'tax_number' => $this->sellerConfig($invoice, 'tax_number'),
        ]);
        $httpOptions = ['verify' => filter_var(config('services.jofotara.verify_ssl', true), FILTER_VALIDATE_BOOLEAN)];
        $debugStream = null;
        if (filter_var(config('services.jofotara.http_debug', false), FILTER_VALIDATE_BOOLEAN)) {
            $debugStream = fopen('php://temp', 'w+');
            $httpOptions['debug'] = $debugStream;
        }

        $response = Http::withHeaders([
            'Client-Id' => $this->sellerConfig($invoice, 'client_id'),
            'Secret-Key' => $this->sellerConfig($invoice, 'secret_key'),
            'Content-Type' => 'application/json',
            'Accept' => '*/*',
        ])->timeout((int) config('services.jofotara.timeout', 60))
            ->withOptions($httpOptions)
            ->post(config('services.jofotara.url'), $payload);

        if (is_resource($debugStream)) {
            rewind($debugStream);
            $debugOutput = stream_get_contents($debugStream) ?: '';
            fclose($debugStream);
            $this->saveHttpDebugFiles($invoice, $payload, $response, $debugOutput);
        }
        Log::info('JoFotara HTTP response received', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'jofotara_invoice_number' => $invoice->jofotara_invoice_number,
            'jofotara_xml_uuid' => $invoice->jofotara_xml_uuid,
            'seller_id' => $invoice->seller_id,
            'http_status' => $response->status(),
            'response_headers' => $response->headers(),
            'response_body_length' => strlen($response->body()),
            'response_body' => $response->body() !== '' ? $response->body() : null,
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
        $this->ensureJofotaraIdentifiers($invoice);

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
        $this->append($document, $root, 'cbc:ID', $this->safeText($invoice->jofotara_invoice_number));
        $this->append($document, $root, 'cbc:UUID', $this->safeText($invoice->jofotara_xml_uuid));
        $this->append($document, $root, 'cbc:IssueDate', $invoice->invoice_date->format('Y-m-d'));
        $this->append($document, $root, 'cbc:InvoiceTypeCode', '388', ['name' => $this->getInvoiceTypeCodeName($invoice)]);
        $this->append($document, $root, 'cbc:Note', $this->safeText($invoice->payment_reference, '-'));
        $this->append($document, $root, 'cbc:DocumentCurrencyCode', 'JOD');
        $this->append($document, $root, 'cbc:TaxCurrencyCode', 'JOD');
        $this->addAdditionalDocumentReference($document, $root, $invoice);
        $this->addSupplierParty($document, $root, $invoice);
        $this->addCustomerParty($document, $root, $invoice);
        $this->addSellerSupplierParty($document, $root, $invoice);
        $this->addInvoiceAllowanceCharge($document, $root, $invoice);
        $this->addTaxTotal($document, $root, $invoice);
        $this->addLegalMonetaryTotal($document, $root, $invoice);
        $this->addInvoiceLines($document, $root, $invoice);

        return $document->saveXML() ?: '';
    }

    public function ensureJofotaraIdentifiers(Invoice $invoice): void
    {
        $updates = [];
        if (blank($invoice->jofotara_invoice_number)) {
            $updates['jofotara_invoice_number'] = 'INV_'.$invoice->invoice_date->format('Y').'_'.str_pad((string) $invoice->id, 5, '0', STR_PAD_LEFT);
        }
        if (blank($invoice->jofotara_xml_uuid)) {
            $updates['jofotara_xml_uuid'] = (string) Str::uuid();
        }
        if (blank($invoice->icv_counter)) {
            $updates['icv_counter'] = $invoice->id;
        }
        if ($updates !== []) {
            $invoice->forceFill($updates)->save();
            $invoice->refresh();
        }
    }

    public function saveDebugFiles(string $xmlPath, string $xml, string $payloadPath, array $payload): void
    {
        $disk = Storage::build(['driver' => 'local', 'root' => storage_path('app')]);
        $disk->put($xmlPath, $xml);
        $disk->put($payloadPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public function debugFileInfo(string $path): array
    {
        $disk = Storage::build(['driver' => 'local', 'root' => storage_path('app')]);
        $exists = $disk->exists($path);

        return [
            'path' => 'storage/app/'.$path,
            'exists' => $exists,
            'size' => $exists ? $disk->size($path) : null,
        ];
    }

    public function saveHttpDebugFiles(Invoice $invoice, array $payload, Response $response, string $debugOutput = ''): void
    {
        $requestText = 'REQUEST:
POST /core/invoices/
'.
            'Client-Id: '.$this->sellerConfig($invoice, 'client_id').'
'.
            'Secret-Key: [masked length '.strlen((string) $this->sellerConfig($invoice, 'secret_key')).']
'.
            'Content-Type: application/json
'.
            'Accept: */*

'.
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).
            '

GUZZLE DEBUG:
'.$this->redactSecret($debugOutput, $this->sellerConfig($invoice, 'secret_key'));

        $responseText = 'RESPONSE:'.'
'.
            'HTTP status: '.$response->status().' '.$response->toPsrResponse()->getReasonPhrase().'
'.
            'Headers: '.json_encode($response->headers(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).'

'.
            ($response->body() !== '' ? $response->body() : '[empty]');

        $disk = Storage::build(['driver' => 'local', 'root' => storage_path('app')]);
        $disk->put('jofotara/http-request.txt', $requestText);
        $disk->put('jofotara/http-response.txt', $responseText);
    }

    private function redactSecret(string $text, ?string $secret): string
    {
        return filled($secret) ? str_replace($secret, '[SECRET-KEY-REDACTED]', $text) : $text;
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
        return number_format((float) $value, 9, '.', '');
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

    private function addAdditionalDocumentReference(DOMDocument $document, DOMElement $root, Invoice $invoice): void
    {
        $reference = $this->append($document, $root, 'cac:AdditionalDocumentReference');
        $this->append($document, $reference, 'cbc:ID', 'ICV');
        $this->append($document, $reference, 'cbc:UUID', (string) $invoice->icv_counter);
    }

    private function addSupplierParty(DOMDocument $document, DOMElement $root, Invoice $invoice): void
    {
        $supplier = $this->append($document, $root, 'cac:AccountingSupplierParty');
        $party = $this->append($document, $supplier, 'cac:Party');

        $address = $this->append($document, $party, 'cac:PostalAddress');
        $country = $this->append($document, $address, 'cac:Country');
        $this->append($document, $country, 'cbc:IdentificationCode', 'JO');

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
            $identification = $this->append($document, $party, 'cac:PartyIdentification');
            $this->append($document, $identification, 'cbc:ID', $customerTaxNumber ?: $customerNationalNumber);
        }

        $address = $this->append($document, $party, 'cac:PostalAddress');
        $this->append($document, $address, 'cbc:PostalZone', $this->safeText($invoice->customer?->address, '-'));
        $country = $this->append($document, $address, 'cac:Country');
        $this->append($document, $country, 'cbc:IdentificationCode', 'JO');

        $taxScheme = $this->append($document, $party, 'cac:PartyTaxScheme');
        $this->addTaxScheme($taxScheme);

        $legal = $this->append($document, $party, 'cac:PartyLegalEntity');
        $this->append($document, $legal, 'cbc:RegistrationName', $this->safeText($invoice->customer?->name, 'عميل نقدي'));

        $contact = $this->append($document, $customer, 'cac:AccountingContact');
        $this->append($document, $contact, 'cbc:Telephone', $this->safeText($invoice->customer?->phone, '-'));
    }

    private function addSellerSupplierParty(DOMDocument $document, DOMElement $root, Invoice $invoice): void
    {
        $sellerSupplier = $this->append($document, $root, 'cac:SellerSupplierParty');
        $party = $this->append($document, $sellerSupplier, 'cac:Party');
        $identification = $this->append($document, $party, 'cac:PartyIdentification');
        $this->append($document, $identification, 'cbc:ID', $this->safeText($this->sellerConfig($invoice, 'source_id')));
    }

    private function addInvoiceAllowanceCharge(DOMDocument $document, DOMElement $root, Invoice $invoice): void
    {
        $allowance = $this->append($document, $root, 'cac:AllowanceCharge');
        $this->append($document, $allowance, 'cbc:ChargeIndicator', 'false');
        $this->append($document, $allowance, 'cbc:AllowanceChargeReason', 'discount');
        $this->append($document, $allowance, 'cbc:Amount', $this->money($this->lineDiscountTotal($invoice)), ['currencyID' => 'JO']);
    }

    private function addTaxTotal(DOMDocument $document, DOMElement $root, Invoice $invoice): void
    {
        $taxTotal = $this->append($document, $root, 'cac:TaxTotal');
        $this->append($document, $taxTotal, 'cbc:TaxAmount', $this->money($this->invoiceTaxAmount($invoice)), ['currencyID' => 'JO']);

        $groupedTaxes = $invoice->items->groupBy(fn ($item) => $this->percent($item->tax_rate));
        foreach ($groupedTaxes as $rate => $items) {
            $taxable = $items->sum(fn ($item) => $this->lineExtensionAmount($item));
            $tax = $items->sum(fn ($item) => $this->lineTaxAmount($item));
            $subtotal = $this->append($document, $taxTotal, 'cac:TaxSubtotal');
            $this->append($document, $subtotal, 'cbc:TaxableAmount', $this->money($taxable), ['currencyID' => 'JO']);
            $this->append($document, $subtotal, 'cbc:TaxAmount', $this->money($tax), ['currencyID' => 'JO']);
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
        $taxExclusive = $invoice->items->sum(fn ($item) => round((float) $item->quantity * (float) $item->unit_price, 9));
        $taxInclusive = $invoice->items->sum(fn ($item) => $this->lineRoundingAmount($item));
        $this->append($document, $total, 'cbc:LineExtensionAmount', $this->money($invoice->items->sum(fn ($item) => $this->lineExtensionAmount($item))), ['currencyID' => 'JO']);
        $this->append($document, $total, 'cbc:TaxExclusiveAmount', $this->money($taxExclusive), ['currencyID' => 'JO']);
        $this->append($document, $total, 'cbc:TaxInclusiveAmount', $this->money($taxInclusive), ['currencyID' => 'JO']);
        $this->append($document, $total, 'cbc:AllowanceTotalAmount', $this->money($this->lineDiscountTotal($invoice)), ['currencyID' => 'JO']);
        $this->append($document, $total, 'cbc:PrepaidAmount', $this->money(0), ['currencyID' => 'JO']);
        $this->append($document, $total, 'cbc:PayableAmount', $this->money($taxInclusive), ['currencyID' => 'JO']);
    }

    private function addInvoiceLines(DOMDocument $document, DOMElement $root, Invoice $invoice): void
    {
        foreach ($invoice->items as $index => $item) {
            $discount = $this->lineDiscountAmount($item);
            $line = $this->append($document, $root, 'cac:InvoiceLine');
            $this->append($document, $line, 'cbc:ID', (string) ($index + 1));
            $this->append($document, $line, 'cbc:InvoicedQuantity', $this->money($item->quantity), ['unitCode' => 'PCE']);
            $this->append($document, $line, 'cbc:LineExtensionAmount', $this->money($this->lineExtensionAmount($item)), ['currencyID' => 'JO']);

            $taxTotal = $this->append($document, $line, 'cac:TaxTotal');
            $this->append($document, $taxTotal, 'cbc:TaxAmount', $this->money($this->lineTaxAmount($item)), ['currencyID' => 'JO']);
            $this->append($document, $taxTotal, 'cbc:RoundingAmount', $this->money($this->lineRoundingAmount($item)), ['currencyID' => 'JO']);

            $itemNode = $this->append($document, $line, 'cac:Item');
            $this->append($document, $itemNode, 'cbc:Name', $this->safeText($item->description, 'بند فاتورة'));
            $category = $this->append($document, $itemNode, 'cac:ClassifiedTaxCategory');
            $this->append($document, $category, 'cbc:ID', ((float) $item->tax_rate) > 0 ? 'S' : 'Z');
            $this->append($document, $category, 'cbc:Percent', $this->percent($item->tax_rate));
            $this->addTaxScheme($category);

            $price = $this->append($document, $line, 'cac:Price');
            $this->append($document, $price, 'cbc:PriceAmount', $this->money($item->unit_price), ['currencyID' => 'JO']);
            $allowance = $this->append($document, $price, 'cac:AllowanceCharge');
            $this->append($document, $allowance, 'cbc:ChargeIndicator', 'false');
            $this->append($document, $allowance, 'cbc:AllowanceChargeReason', 'discount');
            $this->append($document, $allowance, 'cbc:Amount', $this->money($discount), ['currencyID' => 'JO']);
        }
    }

    public function getInvoiceTypeCodeName(Invoice $invoice): string
    {
        return match (($invoice->taxpayer_type ?: 'general_sales').':'.($invoice->payment_type ?: 'receivable')) {
            'general_sales:receivable' => '022',
            'general_sales:cash' => '012',
            'income:receivable' => '021',
            'income:cash' => '011',
            'special_sales:receivable' => '023',
            'special_sales:cash' => '013',
            default => '022',
        };
    }

    private function lineDiscountAmount($item): float
    {
        return 0.0;
    }

    private function lineDiscountTotal(Invoice $invoice): float
    {
        return $invoice->items->sum(fn ($item) => $this->lineDiscountAmount($item));
    }

    private function lineExtensionAmount($item): float
    {
        return round(((float) $item->quantity * (float) $item->unit_price) - $this->lineDiscountAmount($item), 9);
    }

    private function lineTaxAmount($item): float
    {
        return round($this->lineExtensionAmount($item) * ((float) $item->tax_rate / 100), 9);
    }

    private function lineRoundingAmount($item): float
    {
        return round($this->lineExtensionAmount($item) + $this->lineTaxAmount($item), 9);
    }

    private function invoiceTaxAmount(Invoice $invoice): float
    {
        return $invoice->items->sum(fn ($item) => $this->lineTaxAmount($item));
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
