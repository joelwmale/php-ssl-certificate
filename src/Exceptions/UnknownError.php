<?php

namespace Joelwmale\SslCertificate\Exceptions;

use Exception;

class UnknownError extends Exception
{
    public function __construct(string $host, string $error)
    {
        parent::__construct("Failed to download certificate for host {$host}: {$error}");
    }
}
