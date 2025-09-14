<?php

/**
 * Basic usage examples using the NYC demo BMLT server
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use BmltEnabled\BmltQueryClient\Client\BmltClient;
use BmltEnabled\BmltQueryClient\Types\Coordinates;
use BmltEnabled\BmltQueryClient\Types\Language;
use BmltEnabled\BmltQueryClient\Types\VenueType;
use BmltEnabled\BmltQueryClient\Types\Weekday;
use BmltEnabled\BmltQueryClient\BmltQueryClient;

// Initialize client with NYC demo server
$client = new BmltClient(
    rootServerUrl: 'https://latest.aws.bmlt.app/main_server'
);

function basicExamples(BmltClient $client): void
{
    echo "=== Basic BMLT PHP Query Client Examples ===\n\n";

    try {
        // Example 1: Get all meetings
        echo "1. Getting all meetings...\n";
        $allMeetings = $client->searchMeetings(['page_size' => 5]);
        echo "Found " . count($allMeetings) . " meetings (first 5):\n";
        foreach ($allMeetings as $meeting) {
            echo "  - {$meeting->meeting_name} ({$meeting->location_text})\n";
        }
        echo "\n";

        // Example 2: Get virtual meetings
        echo "2. Getting virtual meetings...\n";
        $virtualMeetings = $client->searchMeetings([
            'venue_types' => VenueType::VIRTUAL->value,
            'page_size' => 5
        ]);
        echo "Found " . count($virtualMeetings) . " virtual meetings:\n";
        foreach ($virtualMeetings as $meeting) {
            echo "  - {$meeting->meeting_name} at {$meeting->start_time}\n";
            if ($meeting->virtual_meeting_link) {
                echo "    Link: {$meeting->virtual_meeting_link}\n";
            }
        }
        echo "\n";

        // Example 3: Get meetings for specific days
        echo "3. Getting Monday and Wednesday meetings...\n";
        $weekdayMeetings = $client->searchMeetings([
            'weekdays' => [Weekday::MONDAY->value, Weekday::WEDNESDAY->value],
            'page_size' => 5
        ]);
        echo "Found " . count($weekdayMeetings) . " meetings:\n";
        foreach ($weekdayMeetings as $meeting) {
            $dayNames = ['', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            $dayName = $dayNames[$meeting->weekday_tinyint] ?? 'Unknown';
            echo "  - {$dayName}: {$meeting->meeting_name} at {$meeting->start_time}\n";
        }
        echo "\n";

        // Example 4: Search by coordinates (Times Square area)
        echo "4. Getting meetings near Times Square...\n";
        $coordinates = new Coordinates(40.7580, -73.9855); // Times Square
        $nearbyMeetings = $client->searchMeetingsByCoordinates(
            $coordinates,
            2.0, // 2 miles radius
            searchParams: ['page_size' => 5]
        );
        echo "Found " . count($nearbyMeetings) . " meetings within 2 miles of Times Square:\n";
        foreach ($nearbyMeetings as $meeting) {
            $distance = $meeting->distance_in_miles ? number_format($meeting->distance_in_miles, 1) : 'N/A';
            echo "  - {$meeting->meeting_name} ({$distance} miles)\n";
            echo "    {$meeting->location_text}\n";
        }
        echo "\n";

        // Example 5: Search by address with geocoding
        echo "5. Getting meetings near Central Park...\n";
        $centralParkMeetings = $client->searchMeetingsByAddress(
            address: 'Central Park, New York, NY',
            radiusMiles: 1.5,
            searchParams: ['page_size' => 3]
        );
        echo "Found " . count($centralParkMeetings) . " meetings within 1.5 miles of Central Park:\n";
        foreach ($centralParkMeetings as $meeting) {
            $distance = $meeting->distance_in_miles ? number_format($meeting->distance_in_miles, 1) : 'N/A';
            echo "  - {$meeting->meeting_name}\n";
            echo "    {$meeting->location_text} ({$distance} miles)\n";
        }
        echo "\n";

        // Example 6: Get server information
        echo "6. Server information...\n";
        $serverInfo = $client->getServerInfo();
        echo "Server version: {$serverInfo->version}\n";
        echo "Available languages: " . implode(', ', $serverInfo->langs ?? []) . "\n";
        echo "\n";

        // Example 7: Get meeting formats
        echo "7. Available meeting formats...\n";
        $formats = $client->getFormats(['lang_enum' => Language::ENGLISH->value]);
        echo "Found " . count($formats) . " formats:\n";
        foreach (array_slice($formats, 0, 5) as $format) {
            echo "  - {$format->key_string}: {$format->name_string}\n";
        }
        echo "\n";

        // Example 8: Get service bodies
        echo "8. Service bodies...\n";
        $serviceBodies = $client->getServiceBodies();
        echo "Found " . count($serviceBodies) . " service bodies:\n";
        foreach (array_slice($serviceBodies, 0, 3) as $serviceBody) {
            echo "  - {$serviceBody->name} ({$serviceBody->type})\n";
        }
        echo "\n";

    } catch (Exception $error) {
        echo "Error in basic examples: " . $error->getMessage() . "\n";
    }
}

function geocodingExamples(BmltClient $client): void
{
    echo "=== Geocoding Examples ===\n\n";

    try {
        // Example 1: Direct geocoding
        echo "1. Geocoding famous NYC locations...\n";
        $locations = [
            'Times Square, New York, NY',
            'Central Park, New York, NY',
            'Brooklyn Bridge, New York, NY',
            'Statue of Liberty, New York, NY'
        ];

        foreach ($locations as $location) {
            try {
                $result = $client->geocodeAddress($location);
                echo "{$location}:\n";
                echo "  Coordinates: {$result->coordinates->latitude}, {$result->coordinates->longitude}\n";
                echo "  Full name: {$result->display_name}\n";
            } catch (Exception $error) {
                echo "  Failed to geocode: {$error->getMessage()}\n";
            }
        }
        echo "\n";

        // Example 2: Reverse geocoding
        echo "2. Reverse geocoding NYC coordinates...\n";
        $coords = new Coordinates(40.7614, -73.9776); // Times Square
        $address = $client->reverseGeocode($coords);
        echo "Coordinates {$coords->latitude}, {$coords->longitude}:\n";
        echo "  Address: {$address->display_name}\n";
        echo "\n";

    } catch (Exception $error) {
        echo "Error in geocoding examples: " . $error->getMessage() . "\n";
    }
}

function factoryExample(): void
{
    echo "=== Factory Method Example ===\n\n";
    
    // Using the factory method
    $client = BmltQueryClient::create(
        rootServerUrl: 'https://latest.aws.bmlt.app/main_server'
    );
    
    $info = BmltQueryClient::getInfo();
    echo "Library: {$info['name']}\n";
    echo "Version: {$info['version']}\n";
    echo "PHP Version: {$info['php_version']}\n";
    echo "Supported formats: " . implode(', ', $info['supported_formats']) . "\n";
    echo "\n";
    
    // URL validation
    $validUrl = 'https://example.com/main_server';
    $isValid = BmltQueryClient::validateRootServerUrl($validUrl);
    echo "URL '{$validUrl}' is valid: " . ($isValid ? 'Yes' : 'No') . "\n";
    
    $normalizedUrl = BmltQueryClient::normalizeRootServerUrl('https://example.com/');
    echo "Normalized URL: {$normalizedUrl}\n";
    echo "\n";
}

// Run all examples
echo "Running BMLT PHP Query Client Examples\n";
echo "=====================================\n\n";

factoryExample();
basicExamples($client);
geocodingExamples($client);

echo "=== All examples completed! ===\n";