<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckJofotaraConfig extends Command
{
    protected $signature = 'jofotara:check-config';

    protected $description = 'Verify required JoFotara environment configuration values exist.';

    public function handle(): int
    {
        $keys = ['url', 'client_id', 'secret_key', 'source_id', 'tax_number', 'seller_name'];
        $missing = collect($keys)->filter(fn ($key) => blank(config('services.jofotara.'.$key)));
        if ($missing->isNotEmpty()) {
            $this->error('Missing JoFotara config: '.$missing->implode(', '));

            return self::FAILURE;
        }
        $this->info('JoFotara configuration is complete.');

        return self::SUCCESS;
    }
}
