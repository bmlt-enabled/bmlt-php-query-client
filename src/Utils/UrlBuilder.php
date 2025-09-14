<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Utils;

use BmltEnabled\BmltQueryClient\Exceptions\BmltQueryException;
use BmltEnabled\BmltQueryClient\Types\BmltDataFormat;
use BmltEnabled\BmltQueryClient\Types\BmltEndpoint;
use BmltEnabled\BmltQueryClient\Types\Coordinates;

/**
 * URL builder for BMLT API requests
 */
class UrlBuilder
{
    public static function buildBmltUrl(
        string $rootServerUrl,
        BmltEndpoint $endpoint,
        BmltDataFormat $format,
        array $parameters = [],
    ): string {
        // Validate root server URL
        self::validateRootServerUrl($rootServerUrl);
        
        // Validate endpoint/format combination
        self::validateEndpointFormat($endpoint, $format);

        // Normalize root server URL
        $baseUrl = rtrim($rootServerUrl, '/');
        
        // Build query parameters
        $queryParams = array_merge($parameters, [
            'switcher' => $endpoint->value,
        ]);

        // Add format parameter for non-JSON formats
        if ($format !== BmltDataFormat::JSON) {
            $queryParams['data_format_type'] = $format->value;
        }

        // Filter out null and empty values
        $queryParams = array_filter($queryParams, fn($value) => $value !== null && $value !== '');

        // Build the final URL
        $url = $baseUrl . '/client_interface/json/?' . http_build_query($queryParams);

        return $url;
    }

    public static function validateRootServerUrl(string $url): void
    {
        if (empty(trim($url))) {
            throw BmltQueryException::validationError('Root server URL cannot be empty');
        }

        // Basic URL validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw BmltQueryException::validationError('Invalid root server URL format');
        }

        // Ensure HTTPS for production use
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw BmltQueryException::validationError('Root server URL must use HTTP or HTTPS');
        }
    }

    public static function validateEndpointFormat(BmltEndpoint $endpoint, BmltDataFormat $format): void
    {
        // Some endpoints don't support all formats
        $unsupportedCombinations = [
            // CSV format limitations
            BmltEndpoint::GET_SERVER_INFO->value => [BmltDataFormat::CSV],
            BmltEndpoint::GET_COVERAGE_AREA->value => [BmltDataFormat::CSV],
        ];

        if (isset($unsupportedCombinations[$endpoint->value])) {
            if (in_array($format, $unsupportedCombinations[$endpoint->value], true)) {
                throw BmltQueryException::validationError(
                    "Format {$format->value} is not supported for endpoint {$endpoint->value}"
                );
            }
        }
    }

    public static function validateCoordinates(Coordinates $coordinates): void
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

    public static function validateRadius(float $radius): void
    {
        if ($radius <= 0) {
            throw BmltQueryException::validationError('Radius must be greater than 0');
        }

        if ($radius > 100) {
            throw BmltQueryException::validationError('Radius cannot exceed 100 miles/km');
        }
    }

    public static function normalizeRootServerUrl(string $url): string
    {
        // Remove trailing slashes and common paths
        $url = rtrim($url, '/');
        
        // Remove common BMLT paths if they exist
        $commonPaths = [
            '/client_interface/json',
            '/client_interface',
            '/main_server',
        ];

        foreach ($commonPaths as $path) {
            if (str_ends_with($url, $path)) {
                $url = substr($url, 0, -strlen($path));
                break;
            }
        }

        // Ensure we have main_server at the end
        if (!str_ends_with($url, '/main_server')) {
            $url .= '/main_server';
        }

        return $url;
    }
}