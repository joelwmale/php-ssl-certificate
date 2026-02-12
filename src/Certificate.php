<?php

declare(strict_types=1);

namespace Joelwmale\SslCertificate;

use Carbon\Carbon;
use Joelwmale\SslCertificate\Helpers\Url;

class Certificate
{
    public readonly string $issuer;

    public readonly string $domain;

    /** @var array<int, string> */
    public readonly array $additionalDomains;

    public readonly Carbon $issued;

    public readonly Carbon $expires;

    public readonly int $expiresIn;

    public readonly bool $expired;

    public readonly string $signatureAlgorithm;

    public readonly bool $isSelfSigned;

    public readonly bool $valid;

    /**
     * @param  array<string, mixed>  $rawCertificateFields
     */
    public function __construct(
        private readonly array $rawCertificateFields,
    ) {
        $this->issuer = $this->rawCertificateFields['issuer']['CN'] ?? '';
        $this->domain = $this->getDomain();
        $this->additionalDomains = $this->getAdditionalDomains();

        $this->issued = Carbon::createFromTimestampUTC($this->rawCertificateFields['validFrom_time_t']);
        $this->expires = Carbon::createFromTimestampUTC($this->rawCertificateFields['validTo_time_t']);
        $this->valid = $this->isValid();
        $this->expiresIn = (int) Carbon::now()->diff($this->expires)->format('%r%a');
        $this->expired = $this->expires->isPast();

        $this->signatureAlgorithm = $this->rawCertificateFields['signatureTypeSN'] ?? '';
        $this->isSelfSigned = $this->issuer === $this->domain;
    }

    public static function forHost(string $host): ?Certificate
    {
        return (new Download)->setHost($host)->certificate();
    }

    public function getRawCertificateFields(): array
    {
        return $this->rawCertificateFields;
    }

    public function getDomain(): string
    {
        if (! array_key_exists('CN', $this->rawCertificateFields['subject'])) {
            return '';
        }

        if (is_string($this->rawCertificateFields['subject']['CN'])) {
            return $this->rawCertificateFields['subject']['CN'];
        }

        if (is_array($this->rawCertificateFields['subject']['CN'])) {
            return $this->rawCertificateFields['subject']['CN'][0];
        }

        return '';
    }

    /**
     * @return array<int, string>
     */
    public function getAdditionalDomains(): array
    {
        $additionalDomains = explode(', ', $this->rawCertificateFields['extensions']['subjectAltName'] ?? '');

        return array_map(function (string $domain) {
            return str_replace('DNS:', '', $domain);
        }, $additionalDomains);
    }

    public function getRawCertificateFieldsAsJson(): string
    {
        return json_encode($this->getRawCertificateFields());
    }

    public function isValid(?string $url = null): bool
    {
        if (! Carbon::now()->between($this->issued, $this->expires)) {
            return false;
        }

        if (! empty($url)) {
            return $this->appliesToUrl($url);
        }

        return true;
    }

    public function isValidAt(Carbon $carbon, ?string $url = null): bool
    {
        if ($this->expires->lessThanOrEqualTo($carbon)) {
            return false;
        }

        return $this->isValid($url);
    }

    /**
     * @return array<int, string>
     */
    public function getDomains(): array
    {
        $allDomains = array_merge(
            $this->getAdditionalDomains(),
            [$this->getDomain()]
        );

        $lowerCaseDomains = array_map('strtolower', $allDomains);

        $uniqueDomains = array_unique($lowerCaseDomains);

        return array_values(array_filter($uniqueDomains));
    }

    public function containsDomain(string $domain): bool
    {
        return in_array(strtolower($domain), $this->getDomains(), true);
    }

    public function coversDomain(string $domain): bool
    {
        $certificateHosts = $this->getDomains();

        foreach ($certificateHosts as $certificateHost) {
            if ($certificateHost === $domain) {
                return true;
            }

            if (str_ends_with($domain, '.'.$certificateHost)) {
                return true;
            }
        }

        return false;
    }

    protected function appliesToUrl(string $url): bool
    {
        $host = (new Url($url))->getHost();

        $certificateHosts = $this->getDomains();

        foreach ($certificateHosts as $certificateHost) {
            $certificateHost = str_replace('ip address:', '', strtolower($certificateHost));

            if ($certificateHost === $host) {
                return true;
            }

            if ($this->wildcardHostCoversHost($certificateHost, $host)) {
                return true;
            }
        }

        return false;
    }

    protected function wildcardHostCoversHost(string $wildcardHost, string $host): bool
    {
        if ($host === $wildcardHost) {
            return true;
        }

        if (! str_starts_with($wildcardHost, '*')) {
            return false;
        }

        if (substr_count($wildcardHost, '.') < substr_count($host, '.')) {
            return false;
        }

        $wildcardHostWithoutWildcard = substr($wildcardHost, 1);

        $hostWithDottedPrefix = ".{$host}";

        return str_ends_with($hostWithDottedPrefix, $wildcardHostWithoutWildcard);
    }
}
