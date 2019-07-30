<?php

namespace Joelwmale\SslCertificate\Helpers;

use function Joelwmale\SslCertificate\starts_with;
use Joelwmale\SslCertificate\Exceptions\InvalidUrl;

class Url
{
    /** @var array */
    protected $parsedUrl;

    /**
     * Construct the Url class.
     *
     * @param string $host
     *
     * @throws InvalidUrl
     */
    public function __construct(string $url)
    {
        // add https to the url
        if (! starts_with($url, ['http://', 'https://', 'ssl://'])) {
            $url = "https://{$url}";
        }

        // convert the domain name to ascii format
        if (function_exists('idn_to_ascii') && strlen($url) < 61) {
            $url = idn_to_ascii($url, false, INTL_IDNA_VARIANT_UTS46);
        }

        // if the url is invalid
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            // throw an exception
            throw InvalidUrl::couldNotValidate($url);
        }

        // parse the url
        $this->parsedUrl = parse_url($url);

        // if we didn't parse the host
        if (! isset($this->parsedUrl['host'])) {
            // throw an exception
            throw InvalidUrl::couldNotDetermineHost($this->url);
        }
    }

    /**
     * Get the host from the parsed url.
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->parsedUrl['host'];
    }

    /**
     * Get the port.
     *
     * @return int
     */
    public function getPort(): int
    {
        return $this->parsedUrl['port'] ?? 443;
    }
}
