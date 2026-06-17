<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Console\Commands\CreateRealJofotaraSample;
use App\Models\Invoice;
use App\Services\Jofotara\JoFotaraApiService;
use App\Services\Jofotara\JoFotaraPreparationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class InvoiceController extends Controller
{
    public function index()
    {
        return view('invoices.index', ['invoices' => Invoice::with(['supplier', 'customer'])->latest()->paginate(15)]);
    }

    public function show(Invoice $invoice)
    {
        return view('invoices.show', ['invoice' => $invoice->load(['supplier', 'customer', 'items'])]);
    }

    public function createRealSample(): RedirectResponse
    {
        Artisan::call(CreateRealJofotaraSample::class);

        return redirect()->route('invoices.index')->with('success', trim(Artisan::output()));
    }

    public function prepare(Invoice $invoice, JoFotaraPreparationService $preparer): RedirectResponse
    {
        try {
            $prepared = $preparer->prepare($invoice);
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'تم تجهيز XML وحفظه: '.$prepared['xml_path']);
    }

    public function submitReal(Invoice $invoice, JoFotaraApiService $api): RedirectResponse
    {
        try {
            $result = $api->submit($invoice);
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with($result['status'] === 'ACCEPTED' ? 'success' : 'error', $result['status'] === 'ACCEPTED' ? 'تم قبول الفاتورة من جوفوتارا.' : 'لم يتم قبول الفاتورة. راجع الرد المحفوظ.');
    }

    public function downloadXml(Invoice $invoice): BinaryFileResponse
    {
        return response()->download(storage_path("app/jofotara/invoice-{$invoice->id}/invoice.xml"));
    }

    public function downloadPayload(Invoice $invoice): BinaryFileResponse
    {
        return response()->download(storage_path("app/jofotara/invoice-{$invoice->id}/payload.json"));
    }

    public function issuedPdf(Invoice $invoice)
    {
        $invoice->load(['supplier', 'customer', 'items']);
        if (class_exists(Pdf::class)) {
            return Pdf::loadView('invoices.issued-pdf', compact('invoice'))->setPaper('a4')->download($invoice->invoice_number.'-issued.pdf');
        }

        return view('invoices.issued-pdf', compact('invoice'));
    }
}
