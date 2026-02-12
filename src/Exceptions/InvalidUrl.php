<?php

declare(strict_types=1);

namespace Joelwmale\SslCertificate\Exceptions;

use Exception;

class InvalidUrl extends Exception
{
    public function __construct(string $url)
    {
        parent::__construct("{$url} is not a valid url.");
    }
}
