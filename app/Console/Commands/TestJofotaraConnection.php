<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Throwable;

class TestJofotaraConnection extends Command
{
    protected $signature = 'jofotara:test-connection';

    protected $description = 'Run a lightweight JoFotara endpoint connectivity diagnostic.';

    public function handle(): int
    {
        $host = 'backend.jofotara.gov.jo';
        $port = 443;

        try {
            $records = dns_get_record($host, DNS_A) ?: [];
        } catch (Throwable $exception) {
            $records = [];
            $this->line('DNS error: '.$exception->getMessage());
        }
        $ips = collect($records)->pluck('ip')->filter()->values();
        $this->line('DNS host: '.$host);
        $this->line('IP address: '.($ips->isNotEmpty() ? $ips->implode(', ') : 'FAILED'));

        try {
            $context = stream_context_create(['ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'SNI_enabled' => true,
                'peer_name' => $host,
            ]]);
            $socket = @stream_socket_client('ssl://'.$host.':'.$port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
            if (! $socket) {
                $this->line('HTTPS connectivity: FAILED');
                $this->line('SSL verification result: FAILED: '.$errstr.' ('.$errno.')');

                return self::FAILURE;
            }

            $params = stream_context_get_params($socket);
            fclose($socket);
            $this->line('HTTPS connectivity: OK');
            $this->line('SSL verification result: OK');
            if (isset($params['options']['ssl']['peer_name'])) {
                $this->line('SSL peer name: '.$params['options']['ssl']['peer_name']);
            }
        } catch (Throwable $exception) {
            $this->line('HTTPS connectivity: FAILED');
            $this->line('SSL verification result: FAILED: '.$exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
