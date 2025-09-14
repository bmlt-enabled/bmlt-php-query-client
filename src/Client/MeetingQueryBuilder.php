<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Client;

use BmltEnabled\BmltQueryClient\Types\Coordinates;
use BmltEnabled\BmltQueryClient\Types\VenueType;
use BmltEnabled\BmltQueryClient\Types\Weekday;
use BmltEnabled\BmltQueryClient\Types\Language;
use BmltEnabled\BmltQueryClient\Types\SortKey;

/**
 * Fluent query builder for BMLT meeting searches.
 * 
 * Provides a chainable interface for building complex meeting search queries.
 * All methods return $this for method chaining, allowing you to build sophisticated
 * queries in a readable, fluent style.
 * 
 * @package BmltEnabled\BmltQueryClient\Client
 * @author  Patrick Joyce
 * @since   1.0.0
 * 
 * @example
 * ```php
 * $meetings = (new MeetingQueryBuilder($client))
 *     ->virtualOnly()
 *     ->startingAfter(17, 0)  // After 5 PM
 *     ->endingBefore(21, 0)   // Before 9 PM
 *     ->onWeekdays(Weekday::MONDAY, Weekday::FRIDAY)
 *     ->paginate(10)
 *     ->execute();
 * ```
 */
class MeetingQueryBuilder
{
    private array $params = [];

    public function __construct(
        private BmltClient $client
    ) {
    }

    /**
     * Filter meetings by specific weekdays.
     * 
     * @param Weekday|int ...$weekdays One or more weekdays (1=Sunday, 7=Saturday)
     * 
     * @return self Returns $this for method chaining
     * 
     * @example
     * ```php
     * // Filter for Monday and Friday
     * $builder->onWeekdays(Weekday::MONDAY, Weekday::FRIDAY);
     * 
     * // Or use integers
     * $builder->onWeekdays(2, 6); // Monday, Friday
     * ```
     */
    public function onWeekdays(Weekday|int ...$weekdays): self
    {
        $values = [];
        foreach ($weekdays as $weekday) {
            $values[] = $weekday instanceof Weekday ? $weekday->value : $weekday;
        }
        
        if (!empty($values)) {
            $this->params['weekdays'] = $values;
        }
        
        return $this;
    }

    /**
     * Filter by venue type
     */
    public function withVenueType(VenueType|int $venueType): self
    {
        $this->params['venue_types'] = $venueType instanceof VenueType ? $venueType->value : $venueType;
        return $this;
    }

    /**
     * Filter to virtual meetings only
     */
    public function virtualOnly(): self
    {
        return $this->withVenueType(VenueType::VIRTUAL);
    }

    /**
     * Filter to in-person meetings only
     */
    public function inPersonOnly(): self
    {
        return $this->withVenueType(VenueType::IN_PERSON);
    }

    /**
     * Filter to hybrid meetings only
     */
    public function hybridOnly(): self
    {
        return $this->withVenueType(VenueType::HYBRID);
    }

    /**
     * Filter meetings starting after specified time
     */
    public function startingAfter(int $hour, int $minute = 0): self
    {
        $time = sprintf('%02d:%02d:00', $hour, $minute);
        $this->params['StartsAfterH'] = $hour;
        $this->params['StartsAfterM'] = $minute;
        return $this;
    }

    /**
     * Filter meetings ending before specified time
     */
    public function endingBefore(int $hour, int $minute = 0): self
    {
        $time = sprintf('%02d:%02d:00', $hour, $minute);
        $this->params['StartsBeforeH'] = $hour;
        $this->params['StartsBeforeM'] = $minute;
        return $this;
    }

    /**
     * Filter meetings starting before specified time
     */
    public function startingBefore(int $hour, int $minute = 0): self
    {
        $this->params['StartsBeforeH'] = $hour;
        $this->params['StartsBeforeM'] = $minute;
        return $this;
    }

    /**
     * Filter meetings ending after specified time
     */
    public function endingAfter(int $hour, int $minute = 0): self
    {
        $this->params['EndsAfterH'] = $hour;
        $this->params['EndsAfterM'] = $minute;
        return $this;
    }

    /**
     * Search for text in meeting data
     */
    public function searchText(string $text): self
    {
        $this->params['SearchString'] = $text;
        return $this;
    }

    /**
     * Filter by language
     */
    public function inLanguage(Language|string $language): self
    {
        $this->params['lang_enum'] = $language instanceof Language ? $language->value : $language;
        return $this;
    }

    /**
     * Filter by service body ID
     */
    public function inServiceBody(int $serviceBodyId): self
    {
        $this->params['services'] = $serviceBodyId;
        return $this;
    }

    /**
     * Filter by format shared ID
     */
    public function withFormat(int $formatId): self
    {
        if (!isset($this->params['formats'])) {
            $this->params['formats'] = [];
        }
        $this->params['formats'][] = $formatId;
        return $this;
    }

    /**
     * Filter by multiple format shared IDs
     */
    public function withFormats(int ...$formatIds): self
    {
        foreach ($formatIds as $formatId) {
            $this->withFormat($formatId);
        }
        return $this;
    }

    /**
     * Set page size for results
     */
    public function paginate(int $pageSize, int $pageNum = 1): self
    {
        $this->params['page_size'] = $pageSize;
        if ($pageNum > 1) {
            $this->params['page_num'] = $pageNum;
        }
        return $this;
    }

    /**
     * Sort results by distance (requires coordinates)
     */
    public function sortByDistance(bool $sort = true): self
    {
        $this->params['sort_results_by_distance'] = $sort;
        return $this;
    }

    /**
     * Sort results by specified key
     */
    public function sortBy(SortKey|string $sortKey): self
    {
        $this->params['sort_keys'] = $sortKey instanceof SortKey ? $sortKey->value : $sortKey;
        return $this;
    }

    /**
     * Filter by geographic coordinates and radius
     */
    public function nearCoordinates(Coordinates $coordinates, float $radiusMiles): self
    {
        $this->params['lat_val'] = $coordinates->latitude;
        $this->params['long_val'] = $coordinates->longitude;
        $this->params['geo_width'] = $radiusMiles;
        return $this;
    }

    /**
     * Filter by geographic coordinates and radius in kilometers
     */
    public function nearCoordinatesKm(Coordinates $coordinates, float $radiusKm): self
    {
        $this->params['lat_val'] = $coordinates->latitude;
        $this->params['long_val'] = $coordinates->longitude;
        $this->params['geo_width_km'] = $radiusKm;
        return $this;
    }

    /**
     * Add custom parameter
     */
    public function withParam(string $key, mixed $value): self
    {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * Execute the query and return meetings.
     * 
     * Runs the constructed query against the BMLT server and returns an array
     * of Meeting objects matching the specified criteria.
     * 
     * @return Meeting[] Array of Meeting objects matching the query criteria
     * 
     * @throws BmltQueryException If the request fails or returns invalid data
     * 
     * @example
     * ```php
     * $meetings = $builder->virtualOnly()->startingAfter(18, 0)->execute();
     * ```
     */
    public function execute(): array
    {
        return $this->client->searchMeetings($this->params);
    }

    /**
     * Execute query near an address (uses geocoding)
     */
    public function executeNearAddress(string $address, float $radiusMiles, bool $sortByDistance = true): array
    {
        if ($sortByDistance) {
            $this->sortByDistance();
        }
        
        return $this->client->searchMeetingsByAddress(
            $address,
            $radiusMiles,
            searchParams: $this->params
        );
    }

    /**
     * Execute query near an address in kilometers
     */
    public function executeNearAddressKm(string $address, float $radiusKm, bool $sortByDistance = true): array
    {
        if ($sortByDistance) {
            $this->sortByDistance();
        }
        
        return $this->client->searchMeetingsByAddress(
            $address,
            0, // radiusMiles - not used when radiusKm is provided
            $radiusKm,
            $sortByDistance,
            $this->params
        );
    }

    /**
     * Get the current parameters (useful for debugging)
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Reset all parameters
     */
    public function reset(): self
    {
        $this->params = [];
        return $this;
    }
}