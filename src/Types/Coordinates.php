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