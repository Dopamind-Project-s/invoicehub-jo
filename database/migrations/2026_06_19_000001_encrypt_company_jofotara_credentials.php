<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('companies')->orderBy('id')->chunkById(100, function ($companies): void {
            foreach ($companies as $company) {
                $updates = [];
                foreach (['jofotara_client_id', 'jofotara_secret_key'] as $column) {
                    $value = $company->{$column};
                    if (blank($value) || $this->isEncrypted((string) $value)) {
                        continue;
                    }
                    $updates[$column] = Crypt::encryptString((string) $value);
                }
                if ($updates !== []) {
                    DB::table('companies')->where('id', $company->id)->update($updates);
                }
            }
        });
    }

    public function down(): void
    {
        // Intentionally keep credentials encrypted at rest.
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);
            return true;
        } catch (DecryptException) {
            return false;
        }
    }
};
