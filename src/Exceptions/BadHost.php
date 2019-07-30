<?php

namespace Joelwmale\SslCertificate\Exceptions;

use Joelwmale\SslCertificate\CertificateException;

class BadHost extends CertificateException
{
    public function __construct(string $host)
    {
        parent::__construct("Host {$host} does not exist.");
    }
}
