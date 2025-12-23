<?php

use Carbon\Carbon;
use Joelwmale\SslCertificate\Certificate;

beforeEach(function () {
    // for testing, initially set the time in the past
    Carbon::setTestNow(Carbon::parse('first day of july 2019'));

    $url = __DIR__.'/../data/certificate.test.json';

    // initialize the certificate
    $rawCertificateFields = json_decode(file_get_contents($url), true);

    $this->sslCert = new Certificate($rawCertificateFields);
});

it('can get the issuer', function () {
    expect($this->sslCert->issuer)->toBe("Let's Encrypt Authority X3");
});

it('can determine the root domain', function () {
    expect($this->sslCert->domain)->toBe('testcertificate.dev');
});

it('can determine additional domains', function () {
    expect($this->sslCert->additionalDomains)->toHaveCount(5);

    $domains = collect($this->sslCert->additionalDomains);

    expect($domains->contains('www.testcertificate.com'))->toBeTrue();
    expect($domains->contains('www.testcertificate.dev'))->toBeTrue();
    expect($domains->contains('subdomain.testcertificate.dev'))->toBeTrue();
    expect($domains->contains('sub.subdomain.testcertificate.dev'))->toBeTrue();
    expect($domains->contains('sub.sub.subdomain.testcertificate.dev'))->toBeFalse();
});

it('can determine the issued date', function () {
    expect($this->sslCert->issued)->toBeInstanceOf(Carbon::class);
    expect($this->sslCert->issued->format('Y-m-d H:i:s'))->toBe('2019-06-08 09:45:19');
});

it('can determine the expires date', function () {
    expect($this->sslCert->issued)->toBeInstanceOf(Carbon::class);
    // + 3 months as lets encrypt
    expect($this->sslCert->expires->format('Y-m-d H:i:s'))->toBe('2019-09-06 09:45:19');
});

it('can determine the days until expiry', function () {
    expect($this->sslCert->expiresIn)->toBe(67);
});

it('can determine if the certificate is expired', function () {
    expect($this->sslCert->expired)->toBeFalse();
});

it('can determine all domains', function () {
    expect($this->sslCert->getDomains())->toEqual([
        0 => 'www.testcertificate.com',
        1 => 'testcertificate.com',
        2 => 'www.testcertificate.dev',
        3 => 'subdomain.testcertificate.dev',
        4 => 'sub.subdomain.testcertificate.dev',
        5 => 'testcertificate.dev',
    ]);
});

it('can determine if it is self signed', function () {
    expect($this->sslCert->isSelfSigned)->toBeFalse();
});

it('can determine if the certificate has a specific domain', function () {
    expect($this->sslCert->containsDomain('google.com'))->toBeFalse();
    expect($this->sslCert->containsDomain('www.youtube.com'))->toBeFalse();

    expect($this->sslCert->containsDomain('testcertificate.dev'))->toBeTrue();
    expect($this->sslCert->containsDomain('www.testcertificate.com'))->toBeTrue();
});

it('can determine if the certificate has a specific domain case insensitive', function () {
    expect($this->sslCert->containsDomain('GOOGLE.com'))->toBeFalse();
    expect($this->sslCert->containsDomain('TESTcertIFICATE.dev'))->toBeTrue();
});

it('can determine if the certificate is valid for date', function () {
    // set the carbon timestamp to be 2019-07-29
    Carbon::setTestNow(Carbon::createFromTimestampUTC(1564444073));
    expect($this->sslCert->isValidAt(Carbon::now()))->toBeTrue();

    // set the carbon timestamp to be 2019-03-01
    Carbon::setTestNow(Carbon::createFromTimestampUTC(1551484419));
    expect($this->sslCert->isValidAt(Carbon::now()))->toBeFalse();

    // set the carbon timestamp to be 2019-11-29
    Carbon::setTestNow(Carbon::createFromTimestampUTC(1575071663));
    expect($this->sslCert->isValidAt(Carbon::now()))->toBeFalse();

    // reset test mode
    Carbon::setTestNow(null);
});
