<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Tests\Unit;

use BmltEnabled\BmltQueryClient\Client\BmltClient;
use BmltEnabled\BmltQueryClient\Exceptions\BmltQueryException;
use BmltEnabled\BmltQueryClient\Types\BmltDataFormat;
use BmltEnabled\BmltQueryClient\Types\Coordinates;
use PHPUnit\Framework\TestCase;

class BmltClientTest extends TestCase
{
    private BmltClient $client;

    protected function setUp(): void
    {
        $this->client = new BmltClient(
            rootServerUrl: 'https://example.com/main_server',
            enableGeocoding: false // Disable geocoding for unit tests
        );
    }

    public function testClientInitialization(): void
    {
        $this->assertInstanceOf(BmltClient::class, $this->client);
        $this->assertEquals('https://example.com/main_server', $this->client->getRootServerUrl());
        $this->assertEquals('bmlt-php-query-client/1.0.0', $this->client->getUserAgent());
        $this->assertEquals(30, $this->client->getTimeout());
        $this->assertEquals(BmltDataFormat::JSON, $this->client->getDefaultFormat());
    }

    public function testSetUserAgent(): void
    {
        $customUserAgent = 'my-custom-app/2.1.0';
        $this->client->setUserAgent($customUserAgent);
        $this->assertEquals($customUserAgent, $this->client->getUserAgent());
    }

    public function testSetUserAgentValidation(): void
    {
        $this->expectException(BmltQueryException::class);
        $this->expectExceptionMessage('User agent must be a non-empty string');
        
        $this->client->setUserAgent('');
    }

    public function testSetUserAgentTrimsWhitespace(): void
    {
        $this->client->setUserAgent('   trimmed-agent   ');
        $this->assertEquals('trimmed-agent', $this->client->getUserAgent());
    }

    public function testSetTimeout(): void
    {
        $this->client->setTimeout(60);
        $this->assertEquals(60, $this->client->getTimeout());
    }

    public function testSetTimeoutValidation(): void
    {
        $this->expectException(BmltQueryException::class);
        $this->expectExceptionMessage('Timeout must be a positive integer');
        
        $this->client->setTimeout(-1);
    }

    public function testSetTimeoutZeroValidation(): void
    {
        $this->expectException(BmltQueryException::class);
        $this->expectExceptionMessage('Timeout must be a positive integer');
        
        $this->client->setTimeout(0);
    }

    public function testSetRootServerUrl(): void
    {
        $newUrl = 'https://newserver.com/main_server';
        $this->client->setRootServerUrl($newUrl);
        $this->assertEquals($newUrl, $this->client->getRootServerUrl());
    }

    public function testSetDefaultFormat(): void
    {
        $this->client->setDefaultFormat(BmltDataFormat::JSONP);
        $this->assertEquals(BmltDataFormat::JSONP, $this->client->getDefaultFormat());
    }

    public function testGeocodingDisabledThrowsException(): void
    {
        $this->expectException(BmltQueryException::class);
        $this->expectExceptionMessage('Geocoding is not enabled');
        
        $this->client->geocodeAddress('123 Main St');
    }

    public function testReverseGeocodingDisabledThrowsException(): void
    {
        $this->expectException(BmltQueryException::class);
        $this->expectExceptionMessage('Geocoding is not enabled');
        
        $coordinates = new Coordinates(40.7580, -73.9855);
        $this->client->reverseGeocode($coordinates);
    }

    public function testSearchMeetingsByAddressDisabledThrowsException(): void
    {
        $this->expectException(BmltQueryException::class);
        $this->expectExceptionMessage('Geocoding is not enabled');
        
        $this->client->searchMeetingsByAddress('123 Main St', 5.0);
    }
}