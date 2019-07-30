# Easily retrieve the ssl certificate for any host

This package makes it easy to download a certificate for a host.

## Usage

```php
use Joelwmale\SslCertificate\Certificate;

$certificate = Certificate::forHost('joelmale.com');
```

## Installation

You can install the package via composer:

```bash
composer require joelwmale/php-ssl-certificate
```

## Available Properties & Methods

```php
/** @var string */
$certificate->issuer; // returns the issuer of the certificate

/** @var string */
$certificate->domain; // returns the primary domain on the certificate

/** @var array */
$certificate->additionalDomains; // returns all the additional/alt domains on the certificate

/** @var bool */
$certificate->isValid; // returns true if valid, false if not 

/** @var Carbon */
$certificate->issued; // returns a carbon instance of when the certificate was issued

/** @var Carbon */
$certificate->expires; // returns a carbon instance of when the certificate expires

/** @var int */
$certificate->expiresIn; // returns the amount of days until the certificate expires

/** @var bool */
$certificate->expired; // returns true if the certificate is expired, false if not 

/** @var string */
$certificate->signatureAlgorithm; // returns the signature algorithm used to sign the certificate

/** @var bool */
$certificate->isSelfSigned; // returns true if the certificate was self signed
```

### Get raw certificate as JSON

```php
$certificate->getRawCertificateFieldsAsJson();
```

### Determining if the certificate is valid at a given date

Returns true if the certificate will still be valid.
Takes a Carbon instance as the first parameter.

```php
$certificate->isValidAt(Carbon::today()->addMonth(1));
```

### Determining if certificate contains/convers a domain

Returns true if the certificate contains the domain

```php
$certificate->containsDomain('joelmale.dev');
```

## Testing

``` bash
$ composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for a list of recent changes.
