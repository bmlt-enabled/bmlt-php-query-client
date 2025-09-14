<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Types;

/**
 * Meeting venue types enumeration.
 * 
 * Defines the different types of meeting venues supported by BMLT.
 * These values correspond to the venue_type field in the BMLT database.
 * 
 * @package BmltEnabled\BmltQueryClient\Types
 * @author  Patrick Joyce
 * @since   1.0.0
 */
enum VenueType: int
{
    /** @var int In-person meetings held at a physical location */
    case IN_PERSON = 1;
    
    /** @var int Virtual meetings held online via video/phone */
    case VIRTUAL = 2;
    
    /** @var int Hybrid meetings available both in-person and virtually */
    case HYBRID = 3;
}
