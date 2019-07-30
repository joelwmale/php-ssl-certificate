<?php

namespace Joelwmale\SslCertificate\Tests;

use Carbon\Carbon;
use Joelwmale\SslCertificate\Certificate;
use Joelwmale\SslCertificate\SslCertificate;

class CertificateTest extends \Codeception\Test\Unit
{
    /** @var SslCertificate */
    protected $sslCert;

    protected function _before()
    {
        // initialize the certificate
        $rawCertificateFields = json_decode(file_get_contents(__DIR__.'/data/certificate.test.json'), true);

        $this->sslCert = new Certificate($rawCertificateFields);
    }

    /** @test */
    public function it_can_get_the_issuer()
    {
        $this->assertSame("Let's Encrypt Authority X3", $this->sslCert->issuer);
    }

    /** @test */
    public function it_can_determine_the_root_domain()
    {
        $this->assertSame('testcertificate.dev', $this->sslCert->domain);
    }

    /** @test */
    public function it_can_determine_additional_domains()
    {
        $this->assertCount(5, $this->sslCert->additionalDomains);

        $domains = collect($this->sslCert->additionalDomains);

        $this->assertTrue($domains->contains('www.testcertificate.com'));
        $this->assertTrue($domains->contains('www.testcertificate.dev'));
        $this->assertTrue($domains->contains('subdomain.testcertificate.dev'));
        $this->assertTrue($domains->contains('sub.subdomain.testcertificate.dev'));
        $this->assertFalse($domains->contains('sub.sub.subdomain.testcertificate.dev'));
    }

    /** @test */
    public function it_can_determine_the_issued_date()
    {
        $this->assertInstanceOf(Carbon::class, $this->sslCert->issued);
        $this->assertSame('2019-06-08 09:45:19', $this->sslCert->issued->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_can_determine_the_expires_date()
    {
        $this->assertInstanceOf(Carbon::class, $this->sslCert->issued);
        // + 3 months as lets encrypt
        $this->assertSame('2019-09-06 09:45:19', $this->sslCert->expires->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_can_determine_the_days_until_expiry()
    {
        // set the carbon timestamp to be 2019-07-29
        Carbon::setTestNow(Carbon::createFromTimestampUTC(1564444073));
        $this->assertSame(38, $this->sslCert->expiresIn);
    }

    /** @test */
    public function it_can_determine_if_the_certificate_is_expired()
    {
        $this->assertFalse($this->sslCert->expired);
    }

    /** @test */
    public function it_can_determine_all_domains()
    {
        $this->assertEquals([
            0 => 'www.testcertificate.com',
            1 => 'testcertificate.com',
            2 => 'www.testcertificate.dev',
            3 => 'subdomain.testcertificate.dev',
            4 => 'sub.subdomain.testcertificate.dev',
            5 => 'testcertificate.dev',
        ], $this->sslCert->getDomains());
    }

    /** @test */
    public function it_can_determine_if_it_is_self_signed()
    {
        $this->assertFalse($this->sslCert->isSelfSigned);
    }

    /** @test */
    public function it_can_determine_if_the_certificate_has_a_specific_domain()
    {
        $this->assertFalse($this->sslCert->containsDomain('google.com'));
        $this->assertFalse($this->sslCert->containsDomain('www.youtube.com'));

        $this->assertTrue($this->sslCert->containsDomain('testcertificate.dev'));
        $this->assertTrue($this->sslCert->containsDomain('www.testcertificate.com'));
    }

    /** @test */
    public function it_can_determine_if_the_certificate_has_a_specific_domain_case_insensitive()
    {
        $this->assertFalse($this->sslCert->containsDomain('GOOGLE.com'));
        $this->assertTrue($this->sslCert->containsDomain('TESTcertIFICATE.dev'));
    }

    /** @test */
    public function it_can_determine_if_the_certificate_is_valid_for_date()
    {
        // set the carbon timestamp to be 2019-07-29
        Carbon::setTestNow(Carbon::createFromTimestampUTC(1564444073));
        $this->assertTrue($this->sslCert->isValidAt(Carbon::now()));

        // set the carbon timestamp to be 2019-03-01
        Carbon::setTestNow(Carbon::createFromTimestampUTC(1551484419));
        $this->assertFalse($this->sslCert->isValidAt(Carbon::now()));

        // set the carbon timestamp to be 2019-11-29
        Carbon::setTestNow(Carbon::createFromTimestampUTC(1575071663));
        $this->assertFalse($this->sslCert->isValidAt(Carbon::now()));

        // reset test mode
        Carbon::setTestNow(null);
    }
}
