<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Types;

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