<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreInvoiceRequest;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceXmlLog;
use App\Services\Jofotara\ICVService;
use App\Services\Jofotara\InvoiceHashService;
use App\Services\Jofotara\JoFotaraApiService;
use App\Services\Jofotara\QRCodeService;
use App\Services\Jofotara\TaxCalculationService;
use App\Services\Jofotara\UBLInvoiceBuilder;
use App\Services\Jofotara\UBLValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceApiController extends Controller
{
    public function store(StoreInvoiceRequest $request, ICVService $icv, TaxCalculationService $tax): JsonResponse
    {
        $data = $request->validated();
        $company = Company::findOrFail($data['supplier_id']);
        $invoice = DB::transaction(function () use ($data, $company, $icv, $tax) {
            $totals = $tax->calculateInvoice($data['items']);
            $inv = Invoice::create(array_merge($totals, ['uuid' => (string) Str::uuid(), 'invoice_number' => $data['invoice_number'], 'icv' => $icv->next($company), 'invoice_type' => $data['invoice_type'], 'invoice_subtype' => $data['invoice_subtype'], 'issue_date' => now()->toDateString(), 'issue_time' => now()->format('H:i:s'), 'currency_code' => $data['currency_code'], 'exchange_rate' => '1', 'supplier_id' => $company->id, 'customer_id' => $data['customer_id'] ?? null, 'status' => 'DRAFT']));
            foreach ($data['items'] as $line) {
                $calc = $tax->calculateLine((string) $line['quantity'], (string) $line['unit_price'], (string) ($line['discount'] ?? 0), (string) $line['tax_percent']);
                $inv->items()->create(array_merge($line, $calc));
            }

            return $inv;
        });

        return response()->json($invoice->load('items'), 201);
    }

    public function generate(Invoice $invoice, UBLInvoiceBuilder $builder, InvoiceHashService $hash, QRCodeService $qr, UBLValidationService $validator): JsonResponse
    {
        $invoice->load(['supplier', 'customer', 'items']);
        $invoice->previous_invoice_hash = $hash->previousHash($invoice);
        $xml = $builder->build($invoice);
        $canonical = $hash->canonicalize($xml);
        $invoice->xml_hash = $hash->hash($canonical);
        $invoice->qr_code = $qr->base64($invoice);
        $invoice->status = 'GENERATED';
        $invoice->save();
        InvoiceXmlLog::create(['invoice_id' => $invoice->id, 'generated_xml' => $xml, 'canonical_xml' => $canonical, 'hash' => $invoice->xml_hash, 'validation_result' => $validator->validateXml($xml)]);

        return response()->json(['invoice' => $invoice->fresh(), 'xml_hash' => $invoice->xml_hash]);
    }

    public function submit(Invoice $invoice, UBLInvoiceBuilder $builder, JoFotaraApiService $api): JsonResponse
    {
        return response()->json($api->submit($invoice, $builder->build($invoice)));
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json($invoice->load(['supplier', 'customer', 'items']));
    }

    public function xml(Invoice $invoice, UBLInvoiceBuilder $builder): JsonResponse
    {
        return response()->json(['xml' => $builder->build($invoice)]);
    }

    public function status(Invoice $invoice, JoFotaraApiService $api): JsonResponse
    {
        return response()->json($api->status($invoice));
    }

    public function pdf(Invoice $invoice)
    {
        return response()->json(['message' => 'PDF endpoint is reserved for Dompdf rendering.', 'invoice_id' => $invoice->id]);
    }
}
