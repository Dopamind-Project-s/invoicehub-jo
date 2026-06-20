<?php

namespace App\Http\Controllers;

use App\Models\InvoiceShare;
use App\Services\Invoices\InvoiceBrandingService;

class PublicInvoiceShareController extends Controller
{
    public function __invoke(string $token, InvoiceBrandingService $branding)
    {
        $share = InvoiceShare::with(['invoice.company.settings', 'invoice.contact', 'invoice.items.product'])->where('token', $token)->firstOrFail();
        abort_if($share->expires_at && $share->expires_at->isPast(), 410);
        $share->forceFill(['last_accessed_at' => now()])->save();
        $invoice = $share->invoice;
        $branding = $branding->settings($invoice->company);

        return view('company.invoices.printable', compact('invoice', 'branding', 'share'));
    }
}
