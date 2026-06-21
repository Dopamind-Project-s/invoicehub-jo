<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

use App\Models\Company;
use App\Models\Invoice;

class ICVService
{
    public function next(Company $company): int
    {
        return $this->nextForSubmission($company);
    }

    public function nextForSubmission(Company $company): int
    {
        return ((int) $this->acceptedQuery($company->id)->max('icv')) + 1;
    }

    public function lastAccepted(Company $company): ?Invoice
    {
        return $this->acceptedQuery($company->id)->orderByDesc('icv')->first();
    }

    public function previousAccepted(Company $company, int $icv): ?Invoice
    {
        if ($icv <= 1) {
            return null;
        }

        return $this->acceptedQuery($company->id)->where('icv', $icv - 1)->first();
    }

    public function acceptedQuery(int $companyId)
    {
        return Invoice::query()
            ->where('supplier_id', $companyId)
            ->where(function ($query): void {
                $query->where('jofotara_status', 'ACCEPTED')
                    ->orWhere(function ($legacy): void {
                        $legacy->where('status', 'ACCEPTED')->whereNotNull('accepted_at');
                    });
            })
            ->whereNotNull('xml_hash')
            ->where('xml_hash', '<>', '')
            ->where(function ($query): void {
                $query->where(fn ($uuid) => $uuid->whereNotNull('jofotara_uuid')->where('jofotara_uuid', '<>', ''))
                    ->orWhere(fn ($uuid) => $uuid->whereNotNull('submission_uuid')->where('submission_uuid', '<>', ''));
            });
    }
}
