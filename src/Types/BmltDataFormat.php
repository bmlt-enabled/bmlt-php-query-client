<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Types;

/**
 * BMLT API data formats
 */
enum BmltDataFormat: string
{
    case JSON = 'json';
    case JSONP = 'jsonp';
    case TSML = 'tsml';
    case CSV = 'csv';
}