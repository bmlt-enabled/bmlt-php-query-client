<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Tests\Integration;

use BmltEnabled\BmltQueryClient\Client\BmltClient;
use BmltEnabled\BmltQueryClient\Types\Coordinates;
use BmltEnabled\BmltQueryClient\Types\Language;
use BmltEnabled\BmltQueryClient\Types\VenueType;
use BmltEnabled\BmltQueryClient\Types\Weekday;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests using the NYC demo BMLT server
 */
class BmltApiTest extends TestCase
{
    private BmltClient $client;

    protected function setUp(): void
    {
        $testServerUrl = $_ENV['BMLT_TEST_SERVER'] ?? 'https://latest.aws.bmlt.app/main_server';
        
        $this->client = new BmltClient(
            rootServerUrl: $testServerUrl,
            timeout: 15, // 15 second timeout for tests
            enableGeocoding: true
        );
    }

    public function testGetServerInfo(): void
    {
        $serverInfo = $this->client->getServerInfo();
        
        $this->assertNotEmpty($serverInfo->version);
        $this->assertIsString($serverInfo->version);
    }

    public function testGetFormats(): void
    {
        $formats = $this->client->getFormats();
        
        $this->assertIsArray($formats);
        $this->assertNotEmpty($formats);
        
        $format = $formats[0];
        $this->assertNotEmpty($format->id);
        $this->assertNotEmpty($format->key_string);
        $this->assertNotEmpty($format->name_string);
    }

    public function testGetServiceBodies(): void
    {
        $serviceBodies = $this->client->getServiceBodies();
        
        $this->assertIsArray($serviceBodies);
        $this->assertNotEmpty($serviceBodies);
        
        $serviceBody = $serviceBodies[0];
        $this->assertNotEmpty($serviceBody->id);
        $this->assertNotEmpty($serviceBody->name);
        $this->assertNotEmpty($serviceBody->type);
    }

    public function testSearchMeetings(): void
    {
        $meetings = $this->client->searchMeetings(['page_size' => 10]);
        
        $this->assertIsArray($meetings);
        $this->assertNotEmpty($meetings);
        $this->assertLessThanOrEqual(10, count($meetings));
        
        $meeting = $meetings[0];
        $this->assertNotEmpty($meeting->id_bigint);
        $this->assertNotEmpty($meeting->meeting_name);
        $this->assertIsInt($meeting->weekday_tinyint);
        $this->assertNotEmpty($meeting->start_time);
    }

    public function testSearchVirtualMeetings(): void
    {
        $virtualMeetings = $this->client->searchMeetings([
            'venue_types' => VenueType::VIRTUAL->value,
            'page_size' => 5,
        ]);

        $this->assertIsArray($virtualMeetings);
        
        foreach ($virtualMeetings as $meeting) {
            $this->assertEquals(VenueType::VIRTUAL->value, $meeting->venue_type);
        }
    }

    public function testSearchMeetingsOnMonday(): void
    {
        $mondayMeetings = $this->client->searchMeetings([
            'weekdays' => Weekday::MONDAY->value,
            'page_size' => 5,
        ]);

        $this->assertIsArray($mondayMeetings);
        
        foreach ($mondayMeetings as $meeting) {
            $this->assertEquals(Weekday::MONDAY->value, $meeting->weekday_tinyint);
        }
    }

    public function testSearchMeetingsByCoordinates(): void
    {
        // Times Square coordinates
        $coordinates = new Coordinates(40.758, -73.9855);
        
        $meetings = $this->client->searchMeetingsByCoordinates(
            $coordinates,
            1.0, // 1 mile radius
            searchParams: ['page_size' => 5]
        );

        $this->assertIsArray($meetings);
        
        foreach ($meetings as $meeting) {
            $this->assertNotNull($meeting->distance_in_miles);
            $this->assertLessThanOrEqual(1.0, $meeting->distance_in_miles);
        }
    }

    public function testGeocodingNYCAddress(): void
    {
        $result = $this->client->geocodeAddress('Times Square, New York, NY');

        $this->assertNotNull($result);
        $this->assertNotNull($result->coordinates);
        
        // Should be close to Times Square coordinates
        $this->assertEqualsWithDelta(40.758, $result->coordinates->latitude, 0.1);
        $this->assertEqualsWithDelta(-73.985, $result->coordinates->longitude, 0.1);
        $this->assertStringContainsString('New York', $result->display_name);
    }

    public function testSearchMeetingsByAddress(): void
    {
        $meetings = $this->client->searchMeetingsByAddress(
            'Central Park, New York, NY',
            2.0, // 2 mile radius
            searchParams: ['page_size' => 5]
        );

        $this->assertIsArray($meetings);
        
        foreach ($meetings as $meeting) {
            $this->assertNotNull($meeting->distance_in_miles);
            $this->assertLessThanOrEqual(2.0, $meeting->distance_in_miles);
        }
    }

    public function testReverseGeocode(): void
    {
        $coordinates = new Coordinates(40.7614, -73.9776); // Times Square
        $address = $this->client->reverseGeocode($coordinates);

        $this->assertNotNull($address);
        $this->assertNotEmpty($address->display_name);
        $this->assertEquals($coordinates->latitude, $address->coordinates->latitude);
        $this->assertEquals($coordinates->longitude, $address->coordinates->longitude);
    }

    public function testGetFormatsWithLanguage(): void
    {
        $formats = $this->client->getFormats(['lang_enum' => Language::ENGLISH->value]);
        
        $this->assertIsArray($formats);
        $this->assertNotEmpty($formats);
        
        foreach ($formats as $format) {
            $this->assertEquals('en', $format->lang);
        }
    }

    /**
     * @group slow
     */
    public function testGetChanges(): void
    {
        $changes = $this->client->getChanges('2024-01-01', '2024-01-31');
        
        $this->assertIsArray($changes);
        // Changes array might be empty if no changes in date range
    }

    public function testGetFieldValues(): void
    {
        $fieldValues = $this->client->getFieldValues('location_municipality');
        
        $this->assertIsArray($fieldValues);
        // Field values might be empty depending on the server data
    }
}