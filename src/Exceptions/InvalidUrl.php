<?php

namespace Joelwmale\SslCertificate\Exceptions;

use Exception;

class InvalidUrl extends Exception
{
    public static function couldNotValidate(string $url): self
    {
        return new static("{$url} is not a valid url.");
    }

    public static function couldNotDetermineHost(string $url): self
    {
        return new static("Unable to determine host from url {$url}.");
    }
}
