<?php

namespace Joelwmale\SslCertificate;

use Joelwmale\SslCertificate\Exceptions\BadHost;
use Joelwmale\SslCertificate\Exceptions\CouldNotDownloadCertificate;
use Joelwmale\SslCertificate\Exceptions\NoCertificateInstalled;
use Joelwmale\SslCertificate\Exceptions\UnknownError;
use Joelwmale\SslCertificate\Helpers\Url;
use Throwable;

class Download
{
    /** @var string */
    protected $host = '';

    /** @var int */
    protected $port = 443;

    /** @var int */
    protected $timeout = 30;

    /**
     * Set the host to download the certificate for.
     */
    public function setHost(string $host): self
    {
        // new helper class
        $url = new Url($host);

        // get the host from the url
        $this->host = $url->getHost();

        // return self
        return $this;
    }

    /**
     * Get the first ssl certificate from host.
     *
     * @throws CertificateException
     */
    public function certificate(): ?Certificate
    {
        if (! $this->host) {
            throw new BadHost($this->host);
        }

        // parse the host
        $this->host = (new Url($this->host))->getHost();

        // get all the certificates
        $certificates = $this->parseCertificates();

        // return the first one or null
        return $certificates[0] ?? null;
    }

    /**
     * Parse the hosts certificates.
     */
    public function parseCertificates(): array
    {
        // get the response from an https connection
        // to the host
        $response = $this->fetchHost();

        // get the certificate & certificate chains
        $peerCertificate = $response['options']['ssl']['peer_certificate'];
        $peerCertificateChain = $response['options']['ssl']['peer_certificate_chain'] ?? [];

        // merge them to make the full certificate chain
        $fullCertificateChain = array_merge([$peerCertificate], $peerCertificateChain);

        // map through the full certificate chain
        $certificates = array_map(function ($certificate) {
            // parse the certificate
            $rawCertificateFields = openssl_x509_parse($certificate);

            // create new instance of the certificate
            return new Certificate($rawCertificateFields);
        }, $fullCertificateChain);

        return array_unique($certificates);
    }

    /**
     * Load a small https connection to the host
     * to reteive the ssl options.
     */
    protected function fetchHost(): array
    {
        // configure ssl options
        $sslOptions = [
            'capture_peer_cert' => true,
            'capture_peer_cert_chain' => false,
            'SNI_enabled' => true,
            'verify_peer' => true,
            'verify_peer_name' => true,
        ];

        // create a new context stream
        $streamContext = stream_context_create(['ssl' => $sslOptions]);

        try {
            // connect to the client
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

        // could not connect to the client
        if (! $client) {
            throw new UnknownError($this->host, "Could not connect to {$this->host}.");
        }

        // get the paramters from the context stream
        $response = stream_context_get_params($client);

        // close conection
        fclose($client);

        // return the response
        return $response;
    }

    /**
     * Failed to make a request, so throw the appropriate exception.
     *
     * @throws CouldNotDownloadCertificate
     */
    protected function requestFailed(string $host, Throwable $thrown)
    {
        // host doesnt exist
        if (str_contains($thrown->getMessage(), 'getaddrinfo failed')) {
            throw new BadHost($host);
        }

        // no certificate installed
        if (str_contains($thrown->getMessage(), 'error:14090086')) {
            throw new NoCertificateInstalled($host);
        }

        // default to unknown error
        throw new UnknownError($host, $thrown->getMessage());
    }
}
