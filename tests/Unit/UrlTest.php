<?php

use Joelwmale\SslCertificate\Exceptions\InvalidUrl;
use Joelwmale\SslCertificate\Helpers\Url;

it('parses a simple domain', function () {
    $url = new Url('example.com');

    expect($url->getHost())->toBe('example.com');
});

it('parses a URL with https scheme', function () {
    $url = new Url('https://example.com');

    expect($url->getHost())->toBe('example.com');
});

it('parses a URL with http scheme', function () {
    $url = new Url('http://example.com');

    expect($url->getHost())->toBe('example.com');
});

it('parses a URL with ssl scheme', function () {
    $url = new Url('ssl://example.com');

    expect($url->getHost())->toBe('example.com');
});

it('parses a URL with a path', function () {
    $url = new Url('https://example.com/path/to/page.html');

    expect($url->getHost())->toBe('example.com');
});

it('parses a URL with a port', function () {
    $url = new Url('https://example.com:8443');

    expect($url->getHost())->toBe('example.com');
    expect($url->getPort())->toBe(8443);
});

it('returns default port 443', function () {
    $url = new Url('example.com');

    expect($url->getPort())->toBe(443);
});

it('throws InvalidUrl for invalid URLs', function () {
    expect(fn () => new Url('not a valid url!!!'))
        ->toThrow(InvalidUrl::class);
});
