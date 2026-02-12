<?php

declare(strict_types=1);

namespace Joelwmale\SslCertificate;

use Joelwmale\SslCertificate\Exceptions\BadHost;
use Joelwmale\SslCertificate\Exceptions\NoCertificateInstalled;
use Joelwmale\SslCertificate\Exceptions\UnknownError;
use Joelwmale\SslCertificate\Helpers\Url;
use Throwable;

class Download
{
    private string $host = '';

    public function __construct(
        private readonly int $port = 443,
        private readonly int $timeout = 30,
    ) {}

    public function setHost(string $host): self
    {
        $this->host = (new Url($host))->getHost();

        return $this;
    }

    /**
     * @throws BadHost
     * @throws UnknownError
     */
    public function certificate(): ?Certificate
    {
        if ($this->host === '') {
            throw new BadHost($this->host);
        }

        $this->host = (new Url($this->host))->getHost();

        $certificates = $this->parseCertificates();

        return $certificates[0] ?? null;
    }

    /**
     * @return array<int, Certificate>
     */
    public function parseCertificates(): array
    {
        $response = $this->fetchHost();

        $peerCertificate = $response['options']['ssl']['peer_certificate'];
        $peerCertificateChain = $response['options']['ssl']['peer_certificate_chain'] ?? [];

        $fullCertificateChain = array_merge([$peerCertificate], $peerCertificateChain);

        $certificates = array_map(function ($certificate) {
            $rawCertificateFields = openssl_x509_parse($certificate);

            return new Certificate($rawCertificateFields);
        }, $fullCertificateChain);

        return array_unique($certificates);
    }

    protected function fetchHost(): array
    {
        $sslOptions = [
            'capture_peer_cert' => true,
            'capture_peer_cert_chain' => false,
            'SNI_enabled' => true,
            'verify_peer' => true,
            'verify_peer_name' => true,
        ];

        $streamContext = stream_context_create(['ssl' => $sslOptions]);

        try {
            $client = stream_socket_client(
                "ssl://{$this->host}:{$this->port}",
                $errno,
                $errstr,
                $this->timeout,
                STREAM_CLIENT_CONNECT,
                $streamContext
            );
        } catch (Throwable $thrown) {
            $this->requestFailed($this->host, $thrown);
        }

        if (! $client) {
            throw new UnknownError($this->host, "Could not connect to {$this->host}.");
        }

        $response = stream_context_get_params($client);

        fclose($client);

        return $response;
    }

    /**
     * @throws BadHost
     * @throws NoCertificateInstalled
     * @throws UnknownError
     */
    protected function requestFailed(string $host, Throwable $thrown): never
    {
        if (\str_contains($thrown->getMessage(), 'getaddrinfo failed')) {
            throw new BadHost($host);
        }

        if (\str_contains($thrown->getMessage(), 'error:14090086')) {
            throw new NoCertificateInstalled($host);
        }

        throw new UnknownError($host, $thrown->getMessage());
    }
}
