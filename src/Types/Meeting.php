<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Types;

/**
 * BMLT Meeting data object.
 * 
 * Represents a single meeting from the BMLT database with all associated
 * information including location, time, venue type, and format details.
 * This is a readonly class - all properties are immutable after creation.
 * 
 * @package BmltEnabled\BmltQueryClient\Types
 * @author  Patrick Joyce
 * @since   1.0.0
 * 
 * @property string      $id_bigint                        Meeting ID
 * @property string      $meeting_name                     Meeting name
 * @property int         $weekday_tinyint                  Day of week (1=Sunday, 7=Saturday)
 * @property string      $start_time                       Start time (HH:MM:SS format)
 * @property string|null $end_time                         End time (HH:MM:SS format)
 * @property string|null $duration_time                    Duration (HH:MM:SS format)
 * @property float|null  $latitude                         Latitude coordinate
 * @property float|null  $longitude                        Longitude coordinate
 * @property string|null $location_text                    Full location description
 * @property string|null $location_street                  Street address
 * @property string|null $location_municipality           City/municipality
 * @property string|null $location_province               State/province
 * @property string|null $location_postal_code_1          ZIP/postal code
 * @property int|null    $venue_type                       1=In-person, 2=Virtual, 3=Hybrid
 * @property string|null $virtual_meeting_link            Virtual meeting URL
 * @property float|null  $distance_in_miles               Distance from search point (miles)
 * @property float|null  $distance_in_km                  Distance from search point (kilometers)
 * @property array       $formats                          Array of format codes
 * @property array       $format_shared_id_list           Array of format IDs
 */
readonly class Meeting
{
    public function __construct(
        public string $id_bigint,
        public string $meeting_name,
        public int $weekday_tinyint,
        public string $start_time,
        public ?string $end_time = null,
        public ?string $duration_time = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $location_text = null,
        public ?string $location_street = null,
        public ?string $location_city_subsection = null,
        public ?string $location_neighborhood = null,
        public ?string $location_municipality = null,
        public ?string $location_sub_province = null,
        public ?string $location_province = null,
        public ?string $location_postal_code_1 = null,
        public ?string $location_nation = null,
        public ?string $comments = null,
        public ?string $train_lines = null,
        public ?string $bus_lines = null,
        public ?int $venue_type = null,
        public ?string $virtual_meeting_link = null,
        public ?string $phone_meeting_number = null,
        public ?string $virtual_meeting_additional_info = null,
        public ?float $distance_in_km = null,
        public ?float $distance_in_miles = null,
        public array $formats = [],
        public array $format_shared_id_list = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id_bigint: (string) $data['id_bigint'],
            meeting_name: (string) $data['meeting_name'],
            weekday_tinyint: (int) $data['weekday_tinyint'],
            start_time: (string) $data['start_time'],
            end_time: $data['end_time'] ?? null,
            duration_time: $data['duration_time'] ?? null,
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            location_text: $data['location_text'] ?? null,
            location_street: $data['location_street'] ?? null,
            location_city_subsection: $data['location_city_subsection'] ?? null,
            location_neighborhood: $data['location_neighborhood'] ?? null,
            location_municipality: $data['location_municipality'] ?? null,
            location_sub_province: $data['location_sub_province'] ?? null,
            location_province: $data['location_province'] ?? null,
            location_postal_code_1: $data['location_postal_code_1'] ?? null,
            location_nation: $data['location_nation'] ?? null,
            comments: $data['comments'] ?? null,
            train_lines: $data['train_lines'] ?? null,
            bus_lines: $data['bus_lines'] ?? null,
            venue_type: isset($data['venue_type']) ? (int) $data['venue_type'] : null,
            virtual_meeting_link: $data['virtual_meeting_link'] ?? null,
            phone_meeting_number: $data['phone_meeting_number'] ?? null,
            virtual_meeting_additional_info: $data['virtual_meeting_additional_info'] ?? null,
            distance_in_km: isset($data['distance_in_km']) ? (float) $data['distance_in_km'] : null,
            distance_in_miles: isset($data['distance_in_miles']) ? (float) $data['distance_in_miles'] : null,
            formats: self::parseCommaSeparatedString($data['formats'] ?? ''),
            format_shared_id_list: self::parseCommaSeparatedString($data['format_shared_id_list'] ?? ''),
        );
    }

    /**
     * Parse comma-separated string into array
     */
    private static function parseCommaSeparatedString(string $value): array
    {
        if (empty(trim($value))) {
            return [];
        }
        
        return array_map('trim', explode(',', $value));
    }
}
