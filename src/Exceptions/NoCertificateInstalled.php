<?php

namespace Joelwmale\SslCertificate\Exceptions;

use Exception;

class NoCertificateInstalled extends Exception
{
    public function __construct(string $host)
    {
        parent::__construct("Failed to find certificate on host {$host}.");
    }
}
