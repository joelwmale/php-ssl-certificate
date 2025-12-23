<?php

namespace Joelwmale\SslCertificate;

use Carbon\Carbon;
use Joelwmale\SslCertificate\Helpers\Url;

class Certificate
{
    /** @var array */
    protected $rawCertificateFields = [];

    /** @var string */
    public $issuer;

    /** @var string */
    public $domain;

    /** @var array */
    public $additionalDomains;

    /** @var Carbon */
    public $issued;

    /** @var Carbon */
    public $expires;

    /** @var int expiresin (days) */
    public $expiresIn;

    /** @var bool */
    public $expired;

    /** @var string */
    public $signatureAlgorithm;

    /** @var bool */
    public $isSelfSigned;

    /** @var bool */
    public $valid;

    /**
     * Return a new instance of the certificate downloader.
     *
     * @return Download
     */
    public static function forHost(string $host): ?Certificate
    {
        // return the certificate or null if one is not found
        return (new Download)->setHost($host)->certificate();
    }

    /**
     * Constructor.
     */
    public function __construct(array $rawCertificateFields)
    {
        // set raw certificate fields
        $this->rawCertificateFields = $rawCertificateFields;

        // set up class properties
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

    /**
     * Return the raw certificate fields.
     */
    public function getRawCertificateFields(): array
    {
        return $this->rawCertificateFields;
    }

    /**
     * Get the main domain for the ssl certificate.
     */
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
     * Get the additional domains on the certificate.
     */
    public function getAdditionalDomains(): array
    {
        $additionalDomains = explode(', ', $this->rawCertificateFields['extensions']['subjectAltName'] ?? '');

        return array_map(function (string $domain) {
            return str_replace('DNS:', '', $domain);
        }, $additionalDomains);
    }

    // helper functions

    /**
     * Convert the raw certificate fields to a json string.
     */
    public function getRawCertificateFieldsAsJson(): string
    {
        return json_encode($this->getRawCertificateFields());
    }

    /**
     * Determines if the certificate is valid for the url.
     */
    public function isValid(?string $url = null): bool
    {
        if (! Carbon::now()->between($this->issued, $this->expires)) {
            return false;
        }

        if (! empty($url)) {
            return $this->appliesToUrl($url ?? $this->domain);
        }

        return true;
    }

    /**
     * Determine is the certificate will still be valid at this time.
     */
    public function isValidAt(Carbon $carbon, ?string $url = null): bool
    {
        if ($this->expires->lessThanOrEqualTo($carbon)) {
            return false;
        }

        return $this->isValid($url);
    }

    /**
     * Combine the main domain and additional domains.
     *
     * @return array;
     */
    public function getDomains(): array
    {
        // merge main domain with additional domains
        $allDomains = array_merge(
            $this->getAdditionalDomains(),
            [$this->getDomain()]
        );

        // convert all domains to lower case
        $lowerCaseDomains = array_map('strtolower', $allDomains);

        // remove any duplicates
        $uniqueDomains = array_unique($lowerCaseDomains);

        // return the values once passed through unique filter
        return array_values(array_filter($uniqueDomains));
    }

    /**
     * Determine if the certificate contains this domain.
     *
     * @return bool;
     */
    public function containsDomain(string $domain): bool
    {
        // convert to collection
        $domains = collect($this->getDomains());

        // check if collection contains the domain
        return $domains->contains(strtolower($domain));
    }

    /**
     * Check if the certificate covers the domain.
     */
    public function coversDomain(string $domain): bool
    {
        // get all domains on the certificate
        $certificateHosts = $this->getDomains();

        // foreach host on the certificate
        foreach ($certificateHosts as $certificateHost) {
            // if the host matches
            if ($certificateHost == $domain) {
                // covers the domain, return true
                return true;
            }

            // if the domain ends with `.certificatehost`
            if (ends_with($domain, '.'.$certificateHost)) {
                // covers the domain, return true
                return true;
            }
        }

        // false as it doesn't cover the domain
        return false;
    }

    /**
     * @internal
     *
     * Determine if certificate applies to url
     */
    protected function appliesToUrl(string $url): bool
    {
        // get the host using the url parse
        $host = (new Url($url))->getHost();

        // get all domains for this certificate
        $certificateHosts = $this->getDomains();

        // loop through each certificate
        foreach ($certificateHosts as $certificateHost) {
            // remove `ip address:`
            $certificateHost = str_replace('ip address:', '', strtolower($certificateHost));

            // if the current certificate host
            // matches the host
            if ($certificateHost === $host) {
                // domain applies to this certificate
                return true;
            }

            // if the wildcard covers the passed in host
            if ($this->wildcardHostCoversHost($certificateHost, $host)) {
                // domain applies to this certificate
                return true;
            }
        }

        // domain does not apply to this certificate
        return false;
    }

    /**
     * @internal
     *
     * Determine if the wildcard covers the host domain
     */
    protected function wildcardHostCoversHost(string $wildcardHost, string $host): bool
    {
        // if the host is the same as the wild card host
        if ($host === $wildcardHost) {
            // return true as it covers the host
            return true;
        }

        // if the wildcard host does not start with *
        if (! starts_with($wildcardHost, ['*'])) {
            // then return false as it does not cover the host
            return false;
        }

        // if there are less . in wildcard host than the host
        if (substr_count($wildcardHost, '.') < substr_count($host, '.')) {
            // return false as it does not cover the host
            return false;
        }

        // remove the wild card (but keep the .)
        $wildcardHostWithoutWildcard = substr($wildcardHost, 1);

        // add the .host
        $hostWithDottedPrefix = ".{$host}";

        // check if the end of wildcard matches the host with a dot
        return ends_with($hostWithDottedPrefix, $wildcardHostWithoutWildcard);
    }
}
