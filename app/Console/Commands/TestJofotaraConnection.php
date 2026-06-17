<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

class TestJofotaraConnection extends Command
{
    protected $signature = 'jofotara:test-connection';

    protected $description = 'Run a lightweight JoFotara endpoint connectivity diagnostic.';

    public function handle(): int
    {
        $url = config('services.jofotara.url');
        $host = parse_url($url, PHP_URL_HOST);
        $port = (int) (parse_url($url, PHP_URL_PORT) ?: 443);

        if (! $url || ! $host) {
            $this->error('JoFotara URL is not configured.');

            return self::FAILURE;
        }

        $resolved = gethostbyname($host);
        $dnsOk = $resolved !== $host;
        $this->line('DNS resolution: '.($dnsOk ? $resolved : 'FAILED'));

        $sslStatus = 'NOT TESTED';
        try {
            $context = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'SNI_enabled' => true, 'peer_name' => $host]]);
            $socket = @stream_socket_client('ssl://'.$host.':'.$port, $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
            if ($socket) {
                $sslStatus = 'OK';
                fclose($socket);
            } else {
                $sslStatus = 'FAILED: '.$errstr.' ('.$errno.')';
            }
        } catch (Throwable $exception) {
            $sslStatus = 'FAILED: '.$exception->getMessage();
        }
        $this->line('SSL status: '.$sslStatus);

        try {
            $response = Http::timeout(15)->acceptJson()->get($url);
            $this->line('HTTP status: '.$response->status());
            $this->line('Response body:');
            $this->line($response->body() !== '' ? $response->body() : '[empty]');
        } catch (Throwable $exception) {
            $this->line('HTTP status: FAILED');
            $this->line('Response body: '.$exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
