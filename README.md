# Easily retrieve the ssl certificate for any host

<a href="https://github.com/joelwmale/php-ssl-certificate/actions"><img src="https://github.com/joelwmale/php-ssl-certificate/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/joelwmale/php-ssl-certificate"><img src="https://img.shields.io/packagist/dt/joelwmale/php-ssl-certificate" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/joelwmale/php-ssl-certificate"><img src="https://img.shields.io/packagist/v/joelwmale/php-ssl-certificate" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/joelwmale/php-ssl-certificate"><img src="https://img.shields.io/packagist/l/joelwmale/php-ssl-certificate" alt="License"></a>

This package makes it easy to download a certificate for a host.

## Version Compatibility

| PHP Version | Package Version |
|-------------|-----------------|
| 8.2+        | 4.x             |
| 8.0 - 8.1   | 3.x             |

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

All properties are `readonly`.

```php
$certificate->issuer;              // string - the issuer of the certificate
$certificate->domain;              // string - the primary domain on the certificate
$certificate->additionalDomains;   // array  - all the additional/alt domains on the certificate
$certificate->valid;               // bool   - true if valid, false if not
$certificate->issued;              // Carbon - when the certificate was issued
$certificate->expires;             // Carbon - when the certificate expires
$certificate->expiresIn;           // int    - days until the certificate expires
$certificate->expired;             // bool   - true if the certificate is expired
$certificate->signatureAlgorithm;  // string - the signature algorithm used to sign the certificate
$certificate->isSelfSigned;        // bool   - true if the certificate was self signed
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

### Determining if certificate contains/covers a domain

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
