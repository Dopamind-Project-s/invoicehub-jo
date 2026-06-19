<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class CheckJofotaraDatabase extends Command
{
    protected $signature = 'jofotara:check-db';

    protected $description = 'Validate JoFotara database/schema and removal of legacy sellers dependencies.';

    public function handle(): int
    {
        $failed = false;
        $failed = $this->check('companies table exists', Schema::hasTable('companies')) || $failed;
        $failed = $this->check('routes/controllers do not reference sellers table', ! $this->hasLegacySellerReferences()) || $failed;
        $failed = $this->check('jofotara_secret_key can store 1000 chars', $this->secretColumnCanStore1000Chars()) || $failed;
        $company = Company::where('is_active', true)->first();
        $failed = $this->check('at least one active company exists', (bool) $company) || $failed;
        $failed = $this->check('company has tax_number', filled($company?->tax_number)) || $failed;
        $failed = $this->check('company has jofotara_source_id', filled($company?->jofotara_source_id)) || $failed;
        $failed = $this->check('env client_id exists', filled(config('services.jofotara.client_id'))) || $failed;
        $failed = $this->check('env secret_key exists and length > 100', strlen((string) config('services.jofotara.secret_key')) > 100) || $failed;

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function check(string $label, bool $passes): bool
    {
        $this->line(($passes ? 'PASS ' : 'FAIL ').$label);

        return ! $passes;
    }

    private function hasLegacySellerReferences(): bool
    {
        foreach (Route::getRoutes() as $route) {
            if (str_contains((string) $route->uri(), 'sellers') || str_contains((string) $route->getName(), 'sellers')) {
                return true;
            }
        }

        foreach ([app_path('Http/Controllers'), base_path('routes')] as $path) {
            foreach (File::allFiles($path) as $file) {
                $contents = $file->getContents();
                if (str_contains($contents, 'App\\Models\\'.'Seller') || str_contains($contents, 'exists:'.'sellers') || str_contains($contents, "Route::resource('".'sellers')) {
                    return true;
                }
            }
        }

        return false;
    }

    private function secretColumnCanStore1000Chars(): bool
    {
        foreach (Schema::getColumns('companies') as $column) {
            if (($column['name'] ?? null) !== 'jofotara_secret_key') {
                continue;
            }
            $type = strtolower((string) ($column['type_name'] ?? $column['type'] ?? ''));
            $length = (int) ($column['length'] ?? 0);

            return str_contains($type, 'text') || str_contains($type, 'clob') || $length >= 1000;
        }

        return false;
    }
}
