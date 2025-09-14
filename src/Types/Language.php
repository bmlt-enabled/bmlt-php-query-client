<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Types;

/**
 * Supported languages
 */
enum Language: string
{
    case ENGLISH = 'en';
    case GERMAN = 'de';
    case DANISH = 'dk';
    case SPANISH = 'es';
    case PERSIAN = 'fa';
    case FRENCH = 'fr';
    case ITALIAN = 'it';
    case POLISH = 'pl';
    case PORTUGUESE = 'pt';
    case SWEDISH = 'sv';
}