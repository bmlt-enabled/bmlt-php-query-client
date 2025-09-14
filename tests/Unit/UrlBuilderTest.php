<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Tests\Unit;

use BmltEnabled\BmltQueryClient\Exceptions\BmltQueryException;
use BmltEnabled\BmltQueryClient\Types\BmltDataFormat;
use BmltEnabled\BmltQueryClient\Types\BmltEndpoint;
use BmltEnabled\BmltQueryClient\Types\Coordinates;
use BmltEnabled\BmltQueryClient\Utils\UrlBuilder;
use PHPUnit\Framework\TestCase;

class UrlBuilderTest extends TestCase
{
    public function testBuildBmltUrl(): void
    {
        $url = UrlBuilder::buildBmltUrl(
            'https://example.com/main_server',
            BmltEndpoint::GET_SEARCH_RESULTS,
            BmltDataFormat::JSON,
            ['page_size' => 10]
        );

        $this->assertStringContainsString('https://example.com/main_server/client_interface/json/', $url);
        $this->assertStringContainsString('switcher=GetSearchResults', $url);
        $this->assertStringContainsString('page_size=10', $url);
    }

    public function testBuildBmltUrlWithJsonpFormat(): void
    {
        $url = UrlBuilder::buildBmltUrl(
            'https://example.com/main_server',
            BmltEndpoint::GET_SEARCH_RESULTS,
            BmltDataFormat::JSONP,
            ['callback' => 'myCallback']
        );

        $this->assertStringContainsString('data_format_type=jsonp', $url);
        $this->assertStringContainsString('callback=myCallback', $url);
    }

    public function testValidateRootServerUrl(): void
    {
        // Valid URLs should not throw
        $this->assertNull(UrlBuilder::validateRootServerUrl('https://example.com'));
        $this->assertNull(UrlBuilder::validateRootServerUrl('http://example.com'));
    }

    public function testValidateRootServerUrlEmpty(): void
    {
        $this->expectException(BmltQueryException::class);
        $this->expectExceptionMessage('Root server URL cannot be empty');
        
        UrlBuilder::validateRootServerUrl('');
    }

    public function testValidateRootServerUrlInvalid(): void
    {
        $this->expectException(BmltQueryException::class);
        $this->expectExceptionMessage('Invalid root server URL format');
        
        UrlBuilder::validateRootServerUrl('not-a-url');
    }

    public function testValidateRootServerUrlInvalidScheme(): void
    {
        $this->expectException(BmltQueryException::class);
        $this->expectExceptionMessage('Root server URL must use HTTP or HTTPS');
        
        UrlBuilder::validateRootServerUrl('ftp://example.com');
    }

    public function testValidateCoordinates(): void
    {
        $validCoords = new Coordinates(40.7580, -73.9855);
        $this->assertNull(UrlBuilder::validateCoordinates($validCoords));
    }

    public function testValidateCoordinatesInvalidLatitude(): void
    {
        $this->expectException(BmltQueryException::class);
        $this->expectExceptionMessage('Latitude must be between -90 and 90 degrees');
        
        $invalidCoords = new Coordinates(91.0, 0.0);
        UrlBuilder::validateCoordinates($invalidCoords);
    }

    public function testValidateCoordinatesInvalidLongitude(): void
    {
        $this->expectException(BmltQueryException::class);
        $this->expectExceptionMessage('Longitude must be between -180 and 180 degrees');
        
        $invalidCoords = new Coordinates(0.0, 181.0);
        UrlBuilder::validateCoordinates($invalidCoords);
    }

    public function testValidateRadius(): void
    {
        $this->assertNull(UrlBuilder::validateRadius(5.0));
        $this->assertNull(UrlBuilder::validateRadius(0.1));
        $this->assertNull(UrlBuilder::validateRadius(100.0));
    }

    public function testValidateRadiusNegative(): void
    {
        $this->expectException(BmltQueryException::class);
        $this->expectExceptionMessage('Radius must be greater than 0');
        
        UrlBuilder::validateRadius(-1.0);
    }

    public function testValidateRadiusZero(): void
    {
        $this->expectException(BmltQueryException::class);
        $this->expectExceptionMessage('Radius must be greater than 0');
        
        UrlBuilder::validateRadius(0.0);
    }

    public function testValidateRadiusTooLarge(): void
    {
        $this->expectException(BmltQueryException::class);
        $this->expectExceptionMessage('Radius cannot exceed 100 miles/km');
        
        UrlBuilder::validateRadius(101.0);
    }

    public function testNormalizeRootServerUrl(): void
    {
        // Test various URL formats
        $this->assertEquals(
            'https://example.com/main_server',
            UrlBuilder::normalizeRootServerUrl('https://example.com')
        );

        $this->assertEquals(
            'https://example.com/main_server',
            UrlBuilder::normalizeRootServerUrl('https://example.com/')
        );

        $this->assertEquals(
            'https://example.com/main_server',
            UrlBuilder::normalizeRootServerUrl('https://example.com/main_server')
        );

        $this->assertEquals(
            'https://example.com/main_server',
            UrlBuilder::normalizeRootServerUrl('https://example.com/main_server/')
        );

        $this->assertEquals(
            'https://example.com/main_server',
            UrlBuilder::normalizeRootServerUrl('https://example.com/client_interface/json')
        );
    }

    public function testValidateEndpointFormat(): void
    {
        // Valid combinations should not throw
        $this->assertNull(UrlBuilder::validateEndpointFormat(
            BmltEndpoint::GET_SEARCH_RESULTS,
            BmltDataFormat::JSON
        ));

        $this->assertNull(UrlBuilder::validateEndpointFormat(
            BmltEndpoint::GET_FORMATS,
            BmltDataFormat::CSV
        ));
    }

    public function testValidateEndpointFormatInvalid(): void
    {
        $this->expectException(BmltQueryException::class);
        $this->expectExceptionMessage('Format csv is not supported for endpoint GetServerInfo');
        
        UrlBuilder::validateEndpointFormat(
            BmltEndpoint::GET_SERVER_INFO,
            BmltDataFormat::CSV
        );
    }
}