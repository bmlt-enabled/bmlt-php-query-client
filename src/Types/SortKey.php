<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Types;

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