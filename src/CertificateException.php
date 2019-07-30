<?php

namespace Joelwmale\SslCertificate;

use Exception;
use Joelwmale\SslCertificate\Exceptions\BadHost;
use Joelwmale\SslCertificate\Exceptions\InvalidUrl;
use Joelwmale\SslCertificate\Exceptions\UnknownError;
use Joelwmale\SslCertificate\Exceptions\NoCertificateInstalled;

class CertificateException extends Exception
{
    public static function badHost(string $host): BadHost
    {
        return new BadHost($host);
    }

    public static function noCertificateInstalled(string $host): NoCertificateInstalled
    {
        return new NoCertificateInstalled($host);
    }

    public static function unknownError(string $host, string $error): UnknownError
    {
        return new UnknownError($host, $error);
    }

    public static function couldNotValidate(string $host, string $error): InvalidUrl
    {
        return InvalidUrl::couldNotValidate($host, $error);
    }

    public static function couldNotDetermineHost(string $host, string $error): InvalidUrl
    {
        return InvalidUrl::couldNotDetermineHost($host, $error);
    }
}
