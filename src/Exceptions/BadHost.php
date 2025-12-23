<?php

namespace Joelwmale\SslCertificate\Exceptions;

use Exception;

class BadHost extends Exception
{
    public function __construct(string $host)
    {
        parent::__construct("Failed to download certificate for host {$host}: Host {$host} does not exist.");
    }
}
