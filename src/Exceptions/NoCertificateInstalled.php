<?php

namespace Joelwmale\SslCertificate\Exceptions;

use Joelwmale\SslCertificate\CertificateException;

class NoCertificateInstalled extends CertificateException
{
    public function __construct(string $host)
    {
        parent::__construct("Failed to find certificate on host {$host}.");
    }
}
