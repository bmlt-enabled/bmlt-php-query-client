<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Services;

use BmltEnabled\BmltQueryClient\Exceptions\BmltQueryException;
use BmltEnabled\BmltQueryClient\Types\Coordinates;
use BmltEnabled\BmltQueryClient\Types\GeocodeResult;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

/**
 * Geocoding service using OpenStreetMap Nominatim
 */
class GeocodingService
{
    private const NOMINATIM_BASE_URL = 'https://nominatim.openstreetmap.org';
    
    private Client $client;

    public function __construct(
        private readonly int $timeout = 10,
        private readonly int $retryCount = 3,
        private readonly string $userAgent = 'bmlt-php-query-client/1.0.0',
        private readonly ?string $countryCode = null,
        private readonly ?array $viewbox = null,
        private readonly bool $bounded = false,
    ) {
        $this->client = new Client([
            RequestOptions::TIMEOUT => $this->timeout,
            RequestOptions::HEADERS => [
                'User-Agent' => $this->userAgent,
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Geocode an address to coordinates
     */
    public function geocode(string $address): GeocodeResult
    {
        if (empty(trim($address))) {
            throw BmltQueryException::validationError('Address cannot be empty');
        }

        $params = [
            'q' => trim($address),
            'format' => 'json',
            'limit' => 1,
            'addressdetails' => 1,
        ];

        // Add country code bias if specified
        if ($this->countryCode) {
            $params['countrycodes'] = $this->countryCode;
        }

        // Add viewbox bias if specified
        if ($this->viewbox) {
            [$minLon, $minLat, $maxLon, $maxLat] = $this->viewbox;
            $params['viewbox'] = "{$minLon},{$maxLat},{$maxLon},{$minLat}";
            
            if ($this->bounded) {
                $params['bounded'] = '1';
            }
        }

        $url = self::NOMINATIM_BASE_URL . '/search?' . http_build_query($params);

        try {
            $response = $this->client->get($url);
            $data = json_decode($response->getBody()->getContents(), true);

            if (!is_array($data) || empty($data)) {
                throw BmltQueryException::geocodingError(
                    "No results found for address: {$address}"
                );
            }

            $result = $data[0];
            
            if (!isset($result['lat'], $result['lon'], $result['display_name'])) {
                throw BmltQueryException::geocodingError(
                    "Invalid geocoding response format"
                );
            }

            return new GeocodeResult(
                coordinates: new Coordinates(
                    latitude: (float) $result['lat'],
                    longitude: (float) $result['lon']
                ),
                display_name: (string) $result['display_name'],
                raw_data: $result
            );

        } catch (GuzzleException $e) {
            throw BmltQueryException::geocodingError(
                "Geocoding request failed: {$e->getMessage()}",
                $e
            );
        }
    }

    /**
     * Reverse geocode coordinates to an address
     */
    public function reverseGeocode(Coordinates $coordinates): GeocodeResult
    {
        $params = [
            'lat' => $coordinates->latitude,
            'lon' => $coordinates->longitude,
            'format' => 'json',
            'addressdetails' => 1,
        ];

        $url = self::NOMINATIM_BASE_URL . '/reverse?' . http_build_query($params);

        try {
            $response = $this->client->get($url);
            $data = json_decode($response->getBody()->getContents(), true);

            if (!is_array($data) || !isset($data['display_name'])) {
                throw BmltQueryException::geocodingError(
                    "No results found for coordinates: {$coordinates->latitude}, {$coordinates->longitude}"
                );
            }

            return new GeocodeResult(
                coordinates: $coordinates,
                display_name: (string) $data['display_name'],
                raw_data: $data
            );

        } catch (GuzzleException $e) {
            throw BmltQueryException::geocodingError(
                "Reverse geocoding request failed: {$e->getMessage()}",
                $e
            );
        }
    }

    /**
     * Validate coordinates
     */
    private function validateCoordinates(Coordinates $coordinates): void
    {
        if ($coordinates->latitude < -90 || $coordinates->latitude > 90) {
            throw BmltQueryException::validationError(
                'Latitude must be between -90 and 90 degrees'
            );
        }

        if ($coordinates->longitude < -180 || $coordinates->longitude > 180) {
            throw BmltQueryException::validationError(
                'Longitude must be between -180 and 180 degrees'
            );
        }
    }
}