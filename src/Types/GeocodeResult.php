<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Types;

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