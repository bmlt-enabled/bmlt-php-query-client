<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Types;

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
            langs: self::parseLanguages($data['langs'] ?? null),
            charset: $data['charset'] ?? null,
            server_time_zone_info: $data['server_time_zone_info'] ?? null,
        );
    }

    /**
     * Parse comma-separated language string into array
     */
    private static function parseLanguages(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        if (is_array($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            return array_map('trim', explode(',', $value));
        }
        
        return null;
    }
}