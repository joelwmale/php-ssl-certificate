<?php

namespace Joelwmale\SslCertificate\Exceptions;

use Joelwmale\SslCertificate\CertificateException;

class UnknownError extends CertificateException
{
    public function __construct(string $host, string $error)
    {
        parent::__construct("Failed to download certificate for host {$host}: ${error}");
    }
}
