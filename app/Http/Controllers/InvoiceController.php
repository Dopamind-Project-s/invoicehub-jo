<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Seller;
use App\Services\JofotaraService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index()
    {
        return view('invoices.index', ['invoices' => Invoice::with('seller', 'customer')->latest()->paginate(15)]);
    }

    public function create()
    {
        return view('invoices.create', ['invoice' => new Invoice(['invoice_date' => now(), 'invoice_number' => $this->nextNumber(), 'seller_id' => $this->defaultSellerId()]), 'customers' => Customer::orderBy('name')->get(), 'sellers' => Seller::orderByDesc('is_default')->orderBy('name')->get()]);
    }

    public function store(StoreInvoiceRequest $request)
    {
        $invoice = $this->saveInvoice(new Invoice, $request->validated());

        return redirect()->route('invoices.show', $invoice)->with('success', 'تم حفظ الفاتورة.');
    }

    public function show(Invoice $invoice)
    {
        return view('invoices.show', ['invoice' => $invoice->load('seller', 'customer', 'items')]);
    }

    public function edit(Invoice $invoice)
    {
        return view('invoices.edit', ['invoice' => $invoice->load('items'), 'customers' => Customer::orderBy('name')->get(), 'sellers' => Seller::orderByDesc('is_default')->orderBy('name')->get()]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $this->saveInvoice($invoice, $request->validated());

        return redirect()->route('invoices.show', $invoice)->with('success', 'تم تحديث الفاتورة.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'تم حذف الفاتورة.');
    }

    public function preview(Invoice $invoice)
    {
        return view('invoices.pdf', ['invoice' => $invoice->load('seller', 'customer', 'items'), 'preview' => true]);
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load('seller', 'customer', 'items');
        if (class_exists(Pdf::class)) {
            return Pdf::loadView('invoices.pdf', compact('invoice'))->setPaper('a4')->download($invoice->invoice_number.'.pdf');
        }

        return view('invoices.pdf', compact('invoice'));
    }

    public function submitToJofotara(Invoice $invoice, JofotaraService $service)
    {
        $result = $service->submitInvoice($invoice);

        return back()->with($result['accepted'] ? 'success' : 'error', $result['accepted'] ? 'تم قبول الفاتورة من جوفوتارا.' : 'تم رفض الفاتورة من جوفوتارا.');
    }

    private function saveInvoice(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            $subtotal = $taxTotal = 0;
            $items = collect($data['items'])->map(function ($item) use (&$subtotal, &$taxTotal) {
                $lineBase = round((float) $item['quantity'] * (float) $item['unit_price'], 3);
                $tax = round($lineBase * ((float) ($item['tax_rate'] ?? 0) / 100), 3);
                $subtotal += $lineBase;
                $taxTotal += $tax;

                return $item + ['tax_amount' => $tax, 'line_total' => round($lineBase + $tax, 3)];
            });
            $number = $invoice->exists ? $invoice->invoice_number : $this->nextNumber();
            $invoice->fill(['invoice_number' => $number, 'seller_id' => $data['seller_id'] ?? $this->defaultSellerId(), 'customer_id' => $data['customer_id'] ?? null, 'invoice_date' => $data['invoice_date'], 'due_date' => $data['due_date'] ?? null, 'subtotal' => $subtotal, 'tax_total' => $taxTotal, 'discount_total' => 0, 'total' => round($subtotal + $taxTotal, 3), 'payment_reference' => $number, 'payment_type' => $data['payment_type'] ?? 'receivable', 'taxpayer_type' => $data['taxpayer_type'] ?? 'general_sales', 'status' => $invoice->status ?: 'draft'])->save();
            $invoice->items()->delete();
            $invoice->items()->createMany($items->all());

            return $invoice;
        });
    }

    private function defaultSellerId(): ?int
    {
        if (Seller::count() === 1) {
            return Seller::value('id');
        }

        return Seller::where('is_default', true)->value('id');
    }

    private function nextNumber(): string
    {
        $next = (Invoice::whereYear('created_at', now()->year)->count() + 1);

        return 'INV/'.now()->year.'/'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
