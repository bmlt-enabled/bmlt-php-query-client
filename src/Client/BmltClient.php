<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Client;

use BmltEnabled\BmltQueryClient\Exceptions\BmltQueryException;
use BmltEnabled\BmltQueryClient\Services\GeocodingService;
use BmltEnabled\BmltQueryClient\Types\BmltDataFormat;
use BmltEnabled\BmltQueryClient\Types\BmltEndpoint;
use BmltEnabled\BmltQueryClient\Types\Coordinates;
use BmltEnabled\BmltQueryClient\Types\Format;
use BmltEnabled\BmltQueryClient\Types\GeocodeResult;
use BmltEnabled\BmltQueryClient\Types\Meeting;
use BmltEnabled\BmltQueryClient\Types\ServerInfo;
use BmltEnabled\BmltQueryClient\Types\ServiceBody;
use BmltEnabled\BmltQueryClient\Utils\UrlBuilder;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

/**
 * Main BMLT client class for querying BMLT servers with built-in geocoding support.
 * 
 * This class provides a complete interface to BMLT (Basic Meeting List Tool) servers,
 * allowing you to search for meetings, get server information, formats, service bodies,
 * and perform geocoding operations.
 * 
 * @package BmltEnabled\BmltQueryClient\Client
 * @author  Patrick Joyce
 * @since   1.0.0
 * 
 * @example
 * ```php
 * $client = new BmltClient(
 *     rootServerUrl: 'https://bmlt.example.org/main_server'
 * );
 * 
 * $meetings = $client->searchMeetings([
 *     'weekdays' => [1, 2], // Sunday, Monday
 *     'page_size' => 10
 * ]);
 * ```
 */
class BmltClient
{
    private Client $httpClient;
    private ?GeocodingService $geocodingService = null;

    /**
     * Create a new BMLT client instance.
     * 
     * @param string               $rootServerUrl     The root server URL (e.g., 'https://bmlt.example.org/main_server')
     * @param BmltDataFormat       $defaultFormat     Default data format for API requests
     * @param int                  $timeout           Request timeout in seconds
     * @param string               $userAgent         Custom user agent string
     * @param bool                 $enableGeocoding   Whether to enable geocoding features
     * @param GeocodingService|null $geocodingService Custom geocoding service instance
     * 
     * @throws BmltQueryException If the root server URL is invalid
     */
    public function __construct(
        private string $rootServerUrl,
        private BmltDataFormat $defaultFormat = BmltDataFormat::JSON,
        private int $timeout = 30,
        private string $userAgent = 'bmlt-php-query-client/1.0.0',
        private bool $enableGeocoding = true,
        ?GeocodingService $geocodingService = null,
    ) {
        // Validate and normalize root server URL
        $this->rootServerUrl = UrlBuilder::normalizeRootServerUrl($rootServerUrl);
        
        // Initialize HTTP client
        $this->httpClient = new Client([
            RequestOptions::TIMEOUT => $this->timeout,
            RequestOptions::HEADERS => [
                'User-Agent' => $this->userAgent,
                'Accept' => 'application/json',
            ],
        ]);

        // Initialize geocoding service if enabled
        if ($this->enableGeocoding) {
            $this->geocodingService = $geocodingService ?? new GeocodingService(
                timeout: 10,
                userAgent: $this->userAgent,
            );
        }
    }

    /**
     * Search for meetings using the BMLT API.
     * 
     * @param array $params Search parameters (e.g., weekdays, venue_types, page_size)
     * 
     * @return Meeting[] Array of Meeting objects
     * 
     * @throws BmltQueryException If the request fails or returns invalid data
     * 
     * @example
     * ```php
     * // Search for virtual meetings on Monday
     * $meetings = $client->searchMeetings([
     *     'weekdays' => [2], // Monday
     *     'venue_types' => 2, // Virtual
     *     'page_size' => 10
     * ]);
     * ```
     */
    public function searchMeetings(array $params = []): array
    {
        $format = $params['format'] ?? $this->defaultFormat;
        unset($params['format']);

        $responseData = $this->makeRequest(BmltEndpoint::GET_SEARCH_RESULTS, $params, $format);
        
        if (!is_array($responseData)) {
            return [];
        }

        return array_map(fn(array $data) => Meeting::fromArray($data), $responseData);
    }

    /**
     * Search for meetings by geographic coordinates
     */
    public function searchMeetingsByCoordinates(
        Coordinates $coordinates,
        float $radiusMiles,
        ?float $radiusKm = null,
        array $searchParams = []
    ): array {
        UrlBuilder::validateCoordinates($coordinates);
        UrlBuilder::validateRadius($radiusMiles);

        $params = array_merge($searchParams, [
            'lat_val' => $coordinates->latitude,
            'long_val' => $coordinates->longitude,
            'geo_width' => $radiusMiles,
            'sort_results_by_distance' => true,
        ]);

        return $this->searchMeetings($params);
    }

    /**
     * Search for meetings by address using geocoding
     */
    public function searchMeetingsByAddress(
        string $address,
        float $radiusMiles,
        ?float $radiusKm = null,
        bool $sortByDistance = true,
        array $searchParams = []
    ): array {
        if (!$this->geocodingService) {
            throw BmltQueryException::validationError(
                'Geocoding is not enabled. Initialize client with enableGeocoding: true'
            );
        }

        // Geocode the address
        $geocodeResult = $this->geocodingService->geocode($address);

        // Build search parameters with coordinates
        $geoSearchParams = array_merge($searchParams, [
            'lat_val' => $geocodeResult->coordinates->latitude,
            'long_val' => $geocodeResult->coordinates->longitude,
            'sort_results_by_distance' => $sortByDistance,
        ]);

        // Add radius parameter
        if ($radiusKm !== null) {
            UrlBuilder::validateRadius($radiusKm);
            $geoSearchParams['geo_width_km'] = $radiusKm;
        } else {
            UrlBuilder::validateRadius($radiusMiles);
            $geoSearchParams['geo_width'] = $radiusMiles;
        }

        return $this->searchMeetings($geoSearchParams);
    }

    /**
     * Get server information
     */
    public function getServerInfo(): ServerInfo
    {
        $responseData = $this->makeRequest(BmltEndpoint::GET_SERVER_INFO);
        
        // API returns an array with one element
        if (is_array($responseData) && !empty($responseData)) {
            $data = is_array($responseData[0]) ? $responseData[0] : $responseData;
        } else {
            $data = $responseData;
        }
        
        return ServerInfo::fromArray($data);
    }

    /**
     * Get meeting formats
     */
    public function getFormats(array $params = []): array
    {
        $responseData = $this->makeRequest(BmltEndpoint::GET_FORMATS, $params);
        
        if (!is_array($responseData)) {
            return [];
        }

        return array_map(fn(array $data) => Format::fromArray($data), $responseData);
    }

    /**
     * Get service bodies
     */
    public function getServiceBodies(array $params = []): array
    {
        $responseData = $this->makeRequest(BmltEndpoint::GET_SERVICE_BODIES, $params);
        
        if (!is_array($responseData)) {
            return [];
        }

        return array_map(fn(array $data) => ServiceBody::fromArray($data), $responseData);
    }

    /**
     * Get field values
     */
    public function getFieldValues(string $meetingKey): array
    {
        $params = ['meeting_key' => $meetingKey];
        $responseData = $this->makeRequest(BmltEndpoint::GET_FIELD_VALUES, $params);
        
        return is_array($responseData) ? $responseData : [];
    }

    /**
     * Get changes within a date range
     */
    public function getChanges(string $startDate, ?string $endDate = null, ?int $serviceBodyId = null): array
    {
        $params = ['start_date' => $startDate];
        
        if ($endDate !== null) {
            $params['end_date'] = $endDate;
        }
        
        if ($serviceBodyId !== null) {
            $params['sb_id'] = $serviceBodyId;
        }

        $responseData = $this->makeRequest(BmltEndpoint::GET_CHANGES, $params);
        
        return is_array($responseData) ? $responseData : [];
    }

    /**
     * Geocode an address to coordinates
     */
    public function geocodeAddress(string $address): GeocodeResult
    {
        if (!$this->geocodingService) {
            throw BmltQueryException::validationError(
                'Geocoding is not enabled. Initialize client with enableGeocoding: true'
            );
        }

        return $this->geocodingService->geocode($address);
    }

    /**
     * Reverse geocode coordinates to an address
     */
    public function reverseGeocode(Coordinates $coordinates): GeocodeResult
    {
        if (!$this->geocodingService) {
            throw BmltQueryException::validationError(
                'Geocoding is not enabled. Initialize client with enableGeocoding: true'
            );
        }

        return $this->geocodingService->reverseGeocode($coordinates);
    }

    // Configuration getters and setters

    public function getRootServerUrl(): string
    {
        return $this->rootServerUrl;
    }

    public function setRootServerUrl(string $url): void
    {
        $this->rootServerUrl = UrlBuilder::normalizeRootServerUrl($url);
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): void
    {
        if (empty(trim($userAgent))) {
            throw BmltQueryException::validationError('User agent must be a non-empty string');
        }
        
        $this->userAgent = trim($userAgent);
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): void
    {
        if ($timeout <= 0 || !is_int($timeout)) {
            throw BmltQueryException::validationError('Timeout must be a positive integer');
        }
        
        $this->timeout = $timeout;
    }

    public function getDefaultFormat(): BmltDataFormat
    {
        return $this->defaultFormat;
    }

    public function setDefaultFormat(BmltDataFormat $format): void
    {
        $this->defaultFormat = $format;
    }

    /**
     * Make a request to the BMLT API
     */
    private function makeRequest(BmltEndpoint $endpoint, array $parameters = [], ?BmltDataFormat $format = null): mixed
    {
        $format = $format ?? $this->defaultFormat;
        
        try {
            // Build the request URL
            $url = UrlBuilder::buildBmltUrl(
                $this->rootServerUrl,
                $endpoint,
                $format,
                $parameters
            );

            // Make the request
            $response = $this->httpClient->get($url);

            if ($response->getStatusCode() !== 200) {
                throw BmltQueryException::responseError(
                    "HTTP {$response->getStatusCode()}: {$response->getReasonPhrase()}",
                    $response->getStatusCode()
                );
            }

            // Get response content
            $responseText = $response->getBody()->getContents();

            // Handle different response formats
            return $this->parseResponse($responseText, $format, $parameters);

        } catch (GuzzleException $e) {
            throw BmltQueryException::networkError(
                "Request failed: {$e->getMessage()}",
                $e
            );
        }
    }

    /**
     * Parse response based on format
     */
    private function parseResponse(string $responseText, BmltDataFormat $format, array $parameters = []): mixed
    {
        try {
            switch ($format) {
                case BmltDataFormat::CSV:
                    return $responseText; // Return as-is for CSV

                case BmltDataFormat::JSONP:
                    // Extract JSON from JSONP callback
                    $callbackName = $parameters['callback'] ?? 'callback';
                    $pattern = '/^' . preg_quote($callbackName, '/') . '\s*\(\s*(.*?)\s*\)\s*;?\s*$/s';
                    if (preg_match($pattern, $responseText, $matches)) {
                        return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
                    }
                    throw new \JsonException('Invalid JSONP format');

                case BmltDataFormat::JSON:
                case BmltDataFormat::TSML:
                default:
                    return json_decode($responseText, true, 512, JSON_THROW_ON_ERROR);
            }
        } catch (\JsonException $e) {
            throw BmltQueryException::responseError(
                "Failed to parse response: {$e->getMessage()}"
            );
        }
    }
}