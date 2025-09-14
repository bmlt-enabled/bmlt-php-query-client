# BMLT PHP Query Client

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Composer](https://img.shields.io/badge/composer-ready-green.svg)](https://packagist.org/)

A modern PHP client for querying BMLT (Basic Meeting List Tool) servers with built-in geocoding support using Guzzle HTTP client.

## Features

- 🚀 **Modern PHP 8.1+** - Uses enums, readonly classes, and named parameters
- 🏛️ **Complete BMLT API coverage** - All semantic endpoints supported
- 🌍 **Built-in geocoding** - Uses OpenStreetMap Nominatim for address-to-coordinates conversion
- 🔍 **Type-safe API** - Full type definitions with readonly data objects
- ⚡ **HTTP client** - Built on Guzzle HTTP with proper error handling
- 📦 **Composer ready** - Easy installation via Composer
- 🎯 **Well tested** - Comprehensive unit and integration tests
- 🛠️ **Developer friendly** - Clear exceptions and validation

## Requirements

- PHP 8.1 or higher
- ext-curl
- ext-json

## Installation

```bash
composer require bmlt-enabled/bmlt-php-query-client
```

## Quick Start

### Basic Usage

```php
<?php

use BmltEnabled\BmltQueryClient\Client\BmltClient;
use BmltEnabled\BmltQueryClient\Types\VenueType;
use BmltEnabled\BmltQueryClient\Types\Weekday;

// Initialize the client
$client = new BmltClient(
    rootServerUrl: 'https://latest.aws.bmlt.app/main_server' // NYC demo server
);

// Search for meetings
$meetings = $client->searchMeetings([
    'venue_types' => VenueType::VIRTUAL->value,
    'page_size' => 10,
]);

foreach ($meetings as $meeting) {
    echo "Meeting: {$meeting->meeting_name}\n";
    echo "Time: {$meeting->start_time}\n";
    if ($meeting->virtual_meeting_link) {
        echo "Link: {$meeting->virtual_meeting_link}\n";
    }
    echo "---\n";
}
```

### Using the Factory

```php
<?php

use BmltEnabled\BmltQueryClient\BmltQueryClient;

// Create client using factory method
$client = BmltQueryClient::create(
    rootServerUrl: 'https://your-bmlt-server.org/main_server'
);

$serverInfo = $client->getServerInfo();
echo "Server version: {$serverInfo->version}\n";
```

## API Reference

### Client Configuration

```php
$client = new BmltClient(
    rootServerUrl: 'https://your-server.org/main_server', // Required
    defaultFormat: BmltDataFormat::JSON, // Optional
    timeout: 30, // Request timeout in seconds
    userAgent: 'my-app/1.0.0', // Custom user agent
    enableGeocoding: true, // Enable geocoding features
);
```

### Search Methods

#### Search Meetings

```php
// Basic search
$meetings = $client->searchMeetings([
    'weekdays' => [Weekday::SUNDAY->value, Weekday::MONDAY->value],
    'venue_types' => VenueType::IN_PERSON->value,
    'page_size' => 20,
]);

// Search by coordinates
use BmltEnabled\BmltQueryClient\Types\Coordinates;

$coordinates = new Coordinates(40.7580, -73.9855); // Times Square
$nearbyMeetings = $client->searchMeetingsByCoordinates(
    $coordinates,
    radiusMiles: 5.0,
    searchParams: ['page_size' => 10]
);

// Search by address (uses geocoding)
$addressMeetings = $client->searchMeetingsByAddress(
    address: 'Times Square, New York, NY',
    radiusMiles: 2.0,
    sortByDistance: true,
    searchParams: ['venue_types' => VenueType::VIRTUAL->value]
);
```

#### Server Information

```php
// Get server info
$serverInfo = $client->getServerInfo();
echo "Version: {$serverInfo->version}\n";
echo "Languages: " . implode(', ', $serverInfo->langs ?? []) . "\n";

// Get meeting formats
$formats = $client->getFormats();
foreach ($formats as $format) {
    echo "{$format->key_string}: {$format->name_string}\n";
}

// Get service bodies
$serviceBodies = $client->getServiceBodies();
foreach ($serviceBodies as $serviceBody) {
    echo "{$serviceBody->name} ({$serviceBody->type})\n";
}
```

#### Geocoding

```php
// Geocode an address
$result = $client->geocodeAddress('Central Park, New York, NY');
echo "Coordinates: {$result->coordinates->latitude}, {$result->coordinates->longitude}\n";
echo "Address: {$result->display_name}\n";

// Reverse geocode coordinates
$coordinates = new Coordinates(40.7614, -73.9776);
$address = $client->reverseGeocode($coordinates);
echo "Address: {$address->display_name}\n";
```

### Fluent Query Builder

```php
use BmltEnabled\BmltQueryClient\Client\{MeetingQueryBuilder, QuickSearch};

// Complex fluent query
$eveningVirtual = (new MeetingQueryBuilder($client))
    ->virtualOnly()
    ->startingAfter(17, 0)  // After 5 PM
    ->endingBefore(21, 0)   // Before 9 PM
    ->onWeekdays(Weekday::MONDAY, Weekday::FRIDAY)
    ->paginate(10)
    ->execute();

// Quick search patterns
$quickSearch = new QuickSearch($client);

$todaysMeetings = $quickSearch->today()->execute();
$tonightVirtual = $quickSearch->tonight()->virtualOnly()->execute();
$weekendInPerson = $quickSearch->weekend()->inPersonOnly()->execute();

// Geographic search with fluent interface
$nearbyMeetings = (new MeetingQueryBuilder($client))
    ->inPersonOnly()
    ->morning()
    ->executeNearAddress('Times Square, NY', 2.0);
```

### Data Types

The library provides strongly-typed data objects:

#### Meeting Object

```php
// Meeting properties
$meeting->id_bigint;           // Meeting ID
$meeting->meeting_name;        // Meeting name
$meeting->weekday_tinyint;     // Day of week (1=Sunday)
$meeting->start_time;          // Start time (HH:MM)
$meeting->venue_type;          // 1=In-person, 2=Virtual, 3=Hybrid
$meeting->virtual_meeting_link; // Virtual meeting URL
$meeting->latitude;            // Latitude
$meeting->longitude;           // Longitude
$meeting->distance_in_miles;   // Distance (when searching by location)
// ... and many more
```

#### Enums

```php
use BmltEnabled\BmltQueryClient\Types\{Weekday, VenueType, Language, BmltDataFormat};

// Weekday enum (BMLT uses 1-7, Sunday=1)
Weekday::SUNDAY;    // 1
Weekday::MONDAY;    // 2
// ... etc

// Venue types
VenueType::IN_PERSON; // 1
VenueType::VIRTUAL;   // 2
VenueType::HYBRID;    // 3

// Supported languages
Language::ENGLISH;    // 'en'
Language::SPANISH;    // 'es'
// ... etc

// Data formats
BmltDataFormat::JSON; // 'json'
BmltDataFormat::CSV;  // 'csv'
// ... etc
```

## Error Handling

The library provides comprehensive error handling with specific exception types:

```php
use BmltEnabled\BmltQueryClient\Exceptions\BmltQueryException;

try {
    $meetings = $client->searchMeetings(['invalid' => 'parameter']);
} catch (BmltQueryException $e) {
    echo "Error type: {$e->getType()}\n";
    echo "User message: {$e->getUserMessage()}\n";
    
    if ($e->isRetryable()) {
        echo "This error can be retried\n";
    }
    
    if ($e->isType(BmltQueryException::TYPE_GEOCODING_ERROR)) {
        echo "Geocoding failed\n";
    }
}
```

### Exception Types

- `TYPE_NETWORK_ERROR` - Network connectivity issues (retryable)
- `TYPE_TIMEOUT_ERROR` - Request timeout (retryable)
- `TYPE_VALIDATION_ERROR` - Invalid parameters (not retryable)
- `TYPE_GEOCODING_ERROR` - Geocoding service error (retryable)
- `TYPE_RESPONSE_ERROR` - Invalid server response (retryable for 5xx)
- `TYPE_RATE_LIMIT_ERROR` - Too many requests (retryable)

## Configuration

### Runtime Configuration

You can update client settings after initialization:

```php
// Update user agent
$client->setUserAgent('my-updated-app/2.0.0');
echo $client->getUserAgent(); // 'my-updated-app/2.0.0'

// Update timeout
$client->setTimeout(60); // 60 seconds
echo $client->getTimeout(); // 60

// Update root server URL
$client->setRootServerUrl('https://new-server.org/main_server');
echo $client->getRootServerUrl();

// Update default format
$client->setDefaultFormat(BmltDataFormat::JSONP);
echo $client->getDefaultFormat()->value; // 'jsonp'
```

### Geocoding Options

```php
use BmltEnabled\BmltQueryClient\Services\GeocodingService;

// Create custom geocoding service
$geocodingService = new GeocodingService(
    timeout: 10,
    retryCount: 3,
    userAgent: 'my-app/1.0.0',
    countryCode: 'us', // Bias results to US
    viewbox: [-74.2, 40.4, -73.7, 40.9], // NYC bounding box [w,s,e,n]
    bounded: true, // Restrict to viewbox
);

$client = new BmltClient(
    rootServerUrl: 'https://your-server.org/main_server',
    geocodingService: $geocodingService
);
```

## Testing

The library includes comprehensive tests:

```bash
# Run unit tests
./vendor/bin/phpunit --testsuite="Unit Tests"

# Run integration tests (requires internet connection)
./vendor/bin/phpunit --testsuite="Integration Tests"

# Run all tests
./vendor/bin/phpunit

# Run with coverage (requires xdebug)
./vendor/bin/phpunit --coverage-html coverage
```

## API Documentation

The library includes comprehensive PHPDoc annotations. You can generate HTML API documentation using:

```bash
# Generate API documentation
composer docs

# View documentation
open docs/api/index.html
```

The generated documentation includes:
- Complete API reference for all classes, methods, and properties
- Usage examples and code samples
- Type information and parameter descriptions
- Exception documentation
- Inheritance diagrams

## Development

```bash
# Install dependencies
composer install

# Run tests
composer test

# Static analysis
composer analyse

# Code style check
composer cs-check

# Fix code style
composer cs-fix

# Generate documentation
composer docs
```

## Examples

See the `examples/` directory for complete working examples:

- [basic-usage.php](examples/basic-usage.php) - Basic API usage examples
- [geocoding.php](examples/geocoding.php) - Geocoding examples
- [error-handling.php](examples/error-handling.php) - Error handling examples

## License

MIT License. See [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please read the contributing guidelines and submit pull requests.

## Support

- [Issues](https://github.com/bmlt-enabled/bmlt-php-query-client/issues)
- [BMLT Documentation](https://bmlt.app/documentation/)

## Related Projects

- [bmlt-query-client](https://github.com/bmlt-enabled/bmlt-query-client) - TypeScript/JavaScript version
- [BMLT Root Server](https://github.com/bmlt-enabled/bmlt-root-server) - The BMLT server software