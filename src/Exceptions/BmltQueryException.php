<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Exceptions;

use Exception;
use Throwable;

/**
 * Base exception for BMLT Query Client operations.
 * 
 * Provides enhanced error information including error types, retry capabilities,
 * and user-friendly messages. All exceptions thrown by the library extend this class.
 * 
 * @package BmltEnabled\BmltQueryClient\Exceptions
 * @author  Patrick Joyce
 * @since   1.0.0
 * 
 * @example
 * ```php
 * try {
 *     $client->searchMeetings();
 * } catch (BmltQueryException $e) {
 *     echo "Error: " . $e->getUserMessage();
 *     
 *     if ($e->isRetryable()) {
 *         // Retry the operation
 *     }
 * }
 * ```
 */
class BmltQueryException extends Exception
{
    public const TYPE_NETWORK_ERROR = 'NETWORK_ERROR';
    public const TYPE_TIMEOUT_ERROR = 'TIMEOUT_ERROR';
    public const TYPE_VALIDATION_ERROR = 'VALIDATION_ERROR';
    public const TYPE_GEOCODING_ERROR = 'GEOCODING_ERROR';
    public const TYPE_RESPONSE_ERROR = 'RESPONSE_ERROR';
    public const TYPE_RATE_LIMIT_ERROR = 'RATE_LIMIT_ERROR';

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        private readonly string $type = self::TYPE_NETWORK_ERROR,
        private readonly bool $retryable = false,
        private readonly ?string $userMessage = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isRetryable(): bool
    {
        return $this->retryable;
    }

    public function getUserMessage(): string
    {
        return $this->userMessage ?? $this->getMessage();
    }

    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    public static function networkError(string $message, ?Throwable $previous = null): self
    {
        return new self(
            message: $message,
            previous: $previous,
            type: self::TYPE_NETWORK_ERROR,
            retryable: true,
            userMessage: 'Network connection failed. Please check your internet connection and try again.',
        );
    }

    public static function timeoutError(string $message, ?Throwable $previous = null): self
    {
        return new self(
            message: $message,
            previous: $previous,
            type: self::TYPE_TIMEOUT_ERROR,
            retryable: true,
            userMessage: 'Request timed out. Please try again.',
        );
    }

    public static function validationError(string $message): self
    {
        return new self(
            message: $message,
            type: self::TYPE_VALIDATION_ERROR,
            retryable: false,
            userMessage: $message,
        );
    }

    public static function geocodingError(string $message, ?Throwable $previous = null): self
    {
        return new self(
            message: $message,
            previous: $previous,
            type: self::TYPE_GEOCODING_ERROR,
            retryable: true,
            userMessage: 'Unable to find the specified address. Please check the address and try again.',
        );
    }

    public static function responseError(string $message, int $statusCode = 0): self
    {
        return new self(
            message: $message,
            code: $statusCode,
            type: self::TYPE_RESPONSE_ERROR,
            retryable: $statusCode >= 500,
            userMessage: $statusCode >= 500 
                ? 'Server error occurred. Please try again later.'
                : 'Invalid request. Please check your parameters.',
        );
    }

    public static function rateLimitError(string $message): self
    {
        return new self(
            message: $message,
            type: self::TYPE_RATE_LIMIT_ERROR,
            retryable: true,
            userMessage: 'Too many requests. Please wait a moment and try again.',
        );
    }
}