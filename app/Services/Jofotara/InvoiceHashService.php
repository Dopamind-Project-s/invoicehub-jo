<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

use App\Models\Invoice;

class InvoiceHashService
{
    public function canonicalize(string $xml): string
    {
        $dom = new \DOMDocument;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xml);

        return $dom->C14N() ?: $xml;
    }

    public function hash(string $canonicalXml): string
    {
        return base64_encode(hash('sha256', $canonicalXml, true));
    }

    public function previousHash(Invoice $invoice): ?string
    {
        return Invoice::query()->where('supplier_id', $invoice->supplier_id)->where('icv', '<', $invoice->icv)->orderByDesc('icv')->value('xml_hash');
    }
}
