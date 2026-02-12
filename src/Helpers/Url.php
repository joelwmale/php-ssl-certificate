<?php

declare(strict_types=1);

namespace Joelwmale\SslCertificate\Helpers;

use Joelwmale\SslCertificate\Exceptions\CouldNotDetermineHost;
use Joelwmale\SslCertificate\Exceptions\InvalidUrl;

class Url
{
    /** @var array<string, string|int> */
    private readonly array $parsedUrl;

    /**
     * @throws InvalidUrl
     * @throws CouldNotDetermineHost
     */
    public function __construct(string $url)
    {
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://') && ! str_starts_with($url, 'ssl://')) {
            $url = "https://{$url}";
        }

        if (function_exists('idn_to_ascii')) {
            $parsed = parse_url($url);

            if (isset($parsed['host'])) {
                $asciiHost = idn_to_ascii($parsed['host'], 0, INTL_IDNA_VARIANT_UTS46);

                if ($asciiHost !== false) {
                    $url = str_replace($parsed['host'], $asciiHost, $url);
                }
            }
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidUrl($url);
        }

        $this->parsedUrl = parse_url($url);

        if (! isset($this->parsedUrl['host'])) {
            throw new CouldNotDetermineHost($url);
        }
    }

    public function getHost(): string
    {
        return $this->parsedUrl['host'];
    }

    public function getPort(): int
    {
        return $this->parsedUrl['port'] ?? 443;
    }
}
