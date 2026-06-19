<?php

namespace App\Console\Commands;

use App\Services\Jofotara\JoFotaraCredentialValidator;
use Illuminate\Console\Command;

class CheckJofotaraConfig extends Command
{
    protected $signature = 'jofotara:check-config';

    protected $description = 'Verify required JoFotara configuration values exist without exposing secrets.';

    public function handle(JoFotaraCredentialValidator $validator): int
    {
        $result = $validator->validate();
        if (! $result['valid']) {
            $this->error('JoFotara configuration is incomplete or invalid: '.implode(', ', array_keys($result['errors'])));
            foreach ($result['errors'] as $field => $message) {
                $this->line($field.': '.$message);
            }
            return self::FAILURE;
        }
        $this->info('JoFotara configuration is complete.');
        $this->line('client id configured: yes');
        $this->line('secret key configured: yes');
        $this->line('secret key length: '.$result['meta']['secret_key_length']);
        return self::SUCCESS;
    }
}
