<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Types;

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