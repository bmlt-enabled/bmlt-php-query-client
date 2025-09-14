<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Types;

/**
 * Coordinates (latitude/longitude pair)
 */
readonly class Coordinates
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            latitude: (float) $data['latitude'],
            longitude: (float) $data['longitude']
        );
    }
}

/**
 * BMLT Meeting data
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
            formats: $data['formats'] ?? [],
            format_shared_id_list: $data['format_shared_id_list'] ?? [],
        );
    }
}

/**
 * BMLT Format data
 */
readonly class Format
{
    public function __construct(
        public string $id,
        public string $key_string,
        public string $name_string,
        public ?string $description_string = null,
        public string $lang = 'en',
        public string $format_type_enum = 'FC3',
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            key_string: (string) $data['key_string'],
            name_string: (string) $data['name_string'],
            description_string: $data['description_string'] ?? null,
            lang: $data['lang'] ?? 'en',
            format_type_enum: $data['format_type_enum'] ?? 'FC3',
        );
    }
}

/**
 * BMLT Service Body data
 */
readonly class ServiceBody
{
    public function __construct(
        public string $id,
        public string $name,
        public string $type,
        public ?string $description = null,
        public ?string $parent_id = null,
        public ?string $uri = null,
        public ?string $kml_uri = null,
        public ?string $helpline = null,
        public ?string $world_id = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            name: (string) $data['name'],
            type: (string) $data['type'],
            description: $data['description'] ?? null,
            parent_id: $data['parent_id'] ?? null,
            uri: $data['uri'] ?? null,
            kml_uri: $data['kml_uri'] ?? null,
            helpline: $data['helpline'] ?? null,
            world_id: $data['world_id'] ?? null,
        );
    }
}

/**
 * BMLT Server Info
 */
readonly class ServerInfo
{
    public function __construct(
        public string $version,
        public ?string $semantic_admin_server_base_uri = null,
        public ?array $langs = null,
        public ?string $charset = null,
        public ?array $server_time_zone_info = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            version: (string) $data['version'],
            semantic_admin_server_base_uri: $data['semantic_admin_server_base_uri'] ?? null,
            langs: $data['langs'] ?? null,
            charset: $data['charset'] ?? null,
            server_time_zone_info: $data['server_time_zone_info'] ?? null,
        );
    }
}

/**
 * Geocoding result
 */
readonly class GeocodeResult
{
    public function __construct(
        public Coordinates $coordinates,
        public string $display_name,
        public ?array $raw_data = null,
    ) {
    }
}