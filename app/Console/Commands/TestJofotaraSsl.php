<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Throwable;

class TestJofotaraSsl extends Command
{
    protected $signature = 'jofotara:test-ssl';

    protected $description = 'Inspect DNS, certificate, TLS version, and SSL verification for JoFotara.';

    public function handle(): int
    {
        $host = 'backend.jofotara.gov.jo';
        $this->line('DNS resolution: '.$host);
        try {
            $records = dns_get_record($host, DNS_A) ?: [];
        } catch (Throwable $exception) {
            $records = [];
            $this->line('DNS error: '.$exception->getMessage());
        }
        $ips = collect($records)->pluck('ip')->filter()->values();
        $this->line('IP addresses: '.($ips->isNotEmpty() ? $ips->implode(', ') : 'FAILED'));

        try {
            $context = stream_context_create(['ssl' => [
                'capture_peer_cert' => true,
                'capture_peer_cert_chain' => true,
                'verify_peer' => true,
                'verify_peer_name' => true,
                'SNI_enabled' => true,
                'peer_name' => $host,
            ]]);
            $socket = @stream_socket_client('ssl://'.$host.':443', $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $context);
            if (! $socket) {
                $this->line('SSL verification succeeds: no');
                $this->line('SSL error: '.$errstr.' ('.$errno.')');

                return self::FAILURE;
            }

            $meta = stream_get_meta_data($socket);
            $params = stream_context_get_params($socket);
            $cert = $params['options']['ssl']['peer_certificate'] ?? null;
            $certInfo = $cert ? openssl_x509_parse($cert) : null;
            fclose($socket);

            $this->line('SSL verification succeeds: yes');
            $this->line('TLS version: '.($meta['crypto']['protocol'] ?? 'unknown'));
            $this->line('SSL certificate info: '.json_encode($certInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->line('SSL verification succeeds: no');
            $this->line('SSL error: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
