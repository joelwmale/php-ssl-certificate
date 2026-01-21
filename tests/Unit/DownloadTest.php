<?php

use Joelwmale\SslCertificate\Certificate;
use Joelwmale\SslCertificate\Exceptions\NoCertificateInstalled;
use Joelwmale\SslCertificate\Exceptions\UnknownError;

it('can download a certificate from a normal host', function () {
    $sslCert = Certificate::forHost('joelmale.com');

    expect($sslCert)->toBeInstanceOf(Certificate::class);
});

it('can download a certificate from a host with protocol', function () {
    $sslCert = Certificate::forHost('https://joelmale.com');

    expect($sslCert)->toBeInstanceOf(Certificate::class);
});

it('can download a certificate from a host with paths', function () {
    $sslCert = Certificate::forHost('https://joelmale.com/this-is-not/the/base_domain.html');

    expect($sslCert)->toBeInstanceOf(Certificate::class);
});

it('can download a certificate from a host with weird characters', function () {
    // copied host from spatie/ssl package
    $sslCert = Certificate::forHost('https://www.hüpfburg.de');

    expect($sslCert)->toBeInstanceOf(Certificate::class);
});

// it('correctly throws an exception when no certificate is installed', function () {
//     // copied host from spatie/ssl-package
//     expect(fn() => Certificate::forHost('hipsteadresjes.gent'))
//         ->toThrow(NoCertificateInstalled::class);
// });

it('throws an exception when host doesnt exist', function () {
    expect(fn () => Certificate::forHost('hostthatdoesntexist.dev'))
        ->toThrow(UnknownError::class);
});

it('throws an exception when downloading a certificate from a host that has none', function () {
    expect(fn () => Certificate::forHost('3564020356.org'))
        ->toThrow(UnknownError::class);
});
