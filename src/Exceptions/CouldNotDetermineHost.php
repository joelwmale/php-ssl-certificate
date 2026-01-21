<?php

namespace Joelwmale\SslCertificate\Exceptions;

use Exception;

class CouldNotDetermineHost extends Exception
{
    public function __construct(string $url)
    {
        parent::__construct("Unable to determine host from url {$url}.");
    }
}
