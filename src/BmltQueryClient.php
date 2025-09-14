<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient;

// Main client classes
use BmltEnabled\BmltQueryClient\Client\BmltClient;
use BmltEnabled\BmltQueryClient\Client\MeetingQueryBuilder;
use BmltEnabled\BmltQueryClient\Client\QuickSearch;

// Services
use BmltEnabled\BmltQueryClient\Services\GeocodingService;

// Types and enums
use BmltEnabled\BmltQueryClient\Types\{
    BmltDataFormat,
    BmltEndpoint,
    Language,
    SortKey,
    VenueType,
    Weekday
};
use BmltEnabled\BmltQueryClient\Types\{
    Coordinates,
    Format,
    GeocodeResult,
    Meeting,
    ServerInfo,
    ServiceBody
};

// Exceptions
use BmltEnabled\BmltQueryClient\Exceptions\BmltQueryException;

// Utilities
use BmltEnabled\BmltQueryClient\Utils\UrlBuilder;

/**
 * Main entry point for the BMLT PHP Query Client
 * 
 * This class provides factory methods and easy access to the main functionality
 */
class BmltQueryClient
{
    /**
     * Create a new BMLT client instance
     */
    public static function create(
        string $rootServerUrl,
        BmltDataFormat $defaultFormat = BmltDataFormat::JSON,
        int $timeout = 30,
        string $userAgent = 'bmlt-php-query-client/1.0.0',
        bool $enableGeocoding = true,
        ?GeocodingService $geocodingService = null,
    ): BmltClient {
        return new BmltClient(
            rootServerUrl: $rootServerUrl,
            defaultFormat: $defaultFormat,
            timeout: $timeout,
            userAgent: $userAgent,
            enableGeocoding: $enableGeocoding,
            geocodingService: $geocodingService,
        );
    }

    /**
     * Create a geocoding service instance
     */
    public static function createGeocodingService(
        int $timeout = 10,
        int $retryCount = 3,
        string $userAgent = 'bmlt-php-query-client/1.0.0',
        ?string $countryCode = null,
        ?array $viewbox = null,
        bool $bounded = false,
    ): GeocodingService {
        return new GeocodingService(
            timeout: $timeout,
            retryCount: $retryCount,
            userAgent: $userAgent,
            countryCode: $countryCode,
            viewbox: $viewbox,
            bounded: $bounded,
        );
    }

    /**
     * Validate a root server URL
     */
    public static function validateRootServerUrl(string $url): bool
    {
        try {
            UrlBuilder::validateRootServerUrl($url);
            return true;
        } catch (BmltQueryException $e) {
            return false;
        }
    }

    /**
     * Normalize a root server URL
     */
    public static function normalizeRootServerUrl(string $url): string
    {
        return UrlBuilder::normalizeRootServerUrl($url);
    }

    /**
     * Get version information
     */
    public static function getVersion(): string
    {
        return '1.0.0';
    }

    /**
     * Get library information
     */
    public static function getInfo(): array
    {
        return [
            'name' => 'BMLT PHP Query Client',
            'version' => self::getVersion(),
            'description' => 'A PHP client for querying BMLT (Basic Meeting List Tool) servers with built-in geocoding support',
            'author' => 'Patrick Joyce',
            'license' => 'MIT',
            'php_version' => PHP_VERSION,
            'supported_formats' => array_map(fn($case) => $case->value, BmltDataFormat::cases()),
            'supported_endpoints' => array_map(fn($case) => $case->value, BmltEndpoint::cases()),
        ];
    }
}