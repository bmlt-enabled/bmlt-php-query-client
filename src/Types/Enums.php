<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Types;

/**
 * Days of the week (BMLT uses 1-7, Sunday=1)
 */
enum Weekday: int
{
    case SUNDAY = 1;
    case MONDAY = 2;
    case TUESDAY = 3;
    case WEDNESDAY = 4;
    case THURSDAY = 5;
    case FRIDAY = 6;
    case SATURDAY = 7;
}

/**
 * Meeting venue types
 */
enum VenueType: int
{
    case IN_PERSON = 1;
    case VIRTUAL = 2;
    case HYBRID = 3;
}

/**
 * Sort keys for meeting search results
 */
enum SortKey: string
{
    case WEEKDAY = 'weekday';
    case TIME = 'time';
    case TOWN = 'town';
    case STATE = 'state';
    case WEEKDAY_STATE = 'weekday_state';
}

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