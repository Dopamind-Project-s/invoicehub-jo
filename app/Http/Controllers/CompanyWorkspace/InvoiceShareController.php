<?php

namespace App\Http\Controllers\CompanyWorkspace;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceShare;
use App\Services\Audit\AuditLogger;
use App\Services\Invoices\InvoiceNotificationService;
use App\Services\Invoices\InvoiceShareService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoiceShareController extends Controller
{
    public function __construct(private readonly InvoiceShareService $shares, private readonly InvoiceNotificationService $notifications, private readonly AuditLogger $audit) {}

    public function store(Request $request, Company $company, Invoice $invoice): RedirectResponse
    {
        abort_unless((int) $invoice->company_id === (int) $company->id, 404);
        $data = $request->validate(['channel' => ['required', 'in:link,whatsapp,email'], 'recipient' => ['nullable', 'string', 'max:255']]);
        $share = $this->shares->create($invoice, $data['channel'], $data['recipient'] ?? null);
        $payload = $this->shares->payload($share->load('invoice'));
        $this->notifications->record($invoice, 'shared', $request->user()?->id);
        $this->audit->record('invoice.shared', $invoice, [], ['share_id' => $share->id, 'channel' => $share->channel], $request);

        return back()->with('status', 'تم إنشاء رابط المشاركة.')->with('share_payload', $payload);
    }
}
