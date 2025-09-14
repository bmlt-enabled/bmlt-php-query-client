<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Types;

/**
 * BMLT API endpoints
 */
enum BmltEndpoint: string
{
    case GET_SEARCH_RESULTS = 'GetSearchResults';
    case GET_FORMATS = 'GetFormats';
    case GET_SERVICE_BODIES = 'GetServiceBodies';
    case GET_CHANGES = 'GetChanges';
    case GET_FIELD_KEYS = 'GetFieldKeys';
    case GET_FIELD_VALUES = 'GetFieldValues';
    case GET_NAWS_DUMP = 'GetNAWSDump';
    case GET_SERVER_INFO = 'GetServerInfo';
    case GET_COVERAGE_AREA = 'GetCoverageArea';
}