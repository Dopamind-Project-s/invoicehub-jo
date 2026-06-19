<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

use App\Models\Company;
use Illuminate\Support\Facades\DB;

class ICVService
{
    public function next(Company $company): int
    {
        return DB::transaction(function () use ($company): int {
            $locked = Company::query()->whereKey($company->id)->lockForUpdate()->firstOrFail();
            $locked->last_icv++;
            $locked->save();

            return (int) $locked->last_icv;
        });
    }
}
