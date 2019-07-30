<?php

namespace Joelwmale\SslCertificate\Tests;

use Joelwmale\SslCertificate\Certificate;
use Joelwmale\SslCertificate\Exceptions\BadHost;
use Joelwmale\SslCertificate\Exceptions\UnknownError;
use Joelwmale\SslCertificate\Exceptions\NoCertificateInstalled;

class DownloadTest extends \Codeception\Test\Unit
{
    /** @test */
    public function it_can_download_a_certificate_from_a_normal_host()
    {
        $sslCert = Certificate::forHost('joelmale.com');

        $this->assertInstanceOf(Certificate::class, $sslCert);
    }

    /** @test */
    public function it_can_download_a_certificate_from_a_host_with_protocol()
    {
        $sslCert = Certificate::forHost('https://joelmale.com');

        $this->assertInstanceOf(Certificate::class, $sslCert);
    }

    /** @test */
    public function it_can_download_a_certificate_from_a_host_with_paths()
    {
        $sslCert = Certificate::forHost('https://joelmale.com/this-is-not/the/base_domain.html');

        $this->assertInstanceOf(Certificate::class, $sslCert);
    }

    /** @test */
    public function it_can_download_a_certificate_from_a_host_with_weird_characters()
    {
        // copied host from spatie/ssl package
        $sslCert = Certificate::forHost('https://www.hüpfburg.de');

        $this->assertInstanceOf(Certificate::class, $sslCert);
    }

    /** @test */
    public function it_correctly_throws_an_exception_when_no_certificate_is_installed()
    {
        $this->expectException(NoCertificateInstalled::class);

        // copied host from spatie/ssl-package
        Certificate::forHost('hipsteadresjes.gent');
    }

    /** @test */
    public function it_throws_an_exception_when_host_doesnt_exist()
    {
        $this->expectException(BadHost::class);

        Certificate::forHost('hostthatdoesntexist.dev');
    }

    /** @test */
    public function it_throws_an_exception_when_downloading_a_certificate_from_a_host_that_has_none()
    {
        $this->expectException(UnknownError::class);

        Certificate::forHost('3564020356.org');
    }
}
