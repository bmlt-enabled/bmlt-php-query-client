<?php

/**
 * Fluent Query Builder examples
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use BmltEnabled\BmltQueryClient\Client\BmltClient;
use BmltEnabled\BmltQueryClient\Client\MeetingQueryBuilder;
use BmltEnabled\BmltQueryClient\Client\QuickSearch;
use BmltEnabled\BmltQueryClient\Types\Coordinates;
use BmltEnabled\BmltQueryClient\Types\VenueType;
use BmltEnabled\BmltQueryClient\Types\Weekday;

// Initialize client with NYC demo server
$client = new BmltClient(
    rootServerUrl: 'https://latest.aws.bmlt.app/main_server'
);

function fluentQueryBuilderExamples(BmltClient $client): void
{
    echo "=== Fluent Query Builder Examples ===\n\n";

    try {
        // Example 1: Evening virtual meetings (equivalent to your TypeScript example)
        echo "1. Evening virtual meetings (5 PM - 9 PM)...\n";
        $eveningVirtual = (new MeetingQueryBuilder($client))
            ->virtualOnly()
            ->startingAfter(17, 0) // After 5 PM
            ->endingBefore(21, 0)  // Before 9 PM
            ->sortByDistance()
            ->paginate(5, 1)
            ->execute();

        echo "Found " . count($eveningVirtual) . " evening virtual meetings:\n";
        foreach ($eveningVirtual as $meeting) {
            echo "  - {$meeting->meeting_name} at {$meeting->start_time}\n";
            if ($meeting->virtual_meeting_link) {
                echo "    Link: {$meeting->virtual_meeting_link}\n";
            }
        }
        echo "\n";

        // Example 2: Weekend meetings near Brooklyn Bridge
        echo "2. Weekend meetings near Brooklyn Bridge...\n";
        $weekendMeetings = (new MeetingQueryBuilder($client))
            ->onWeekdays(Weekday::SATURDAY, Weekday::SUNDAY)
            ->inPersonOnly()
            ->executeNearAddress('Brooklyn Bridge, New York, NY', 3.0);

        echo "Found " . count($weekendMeetings) . " weekend meetings near Brooklyn Bridge:\n";
        foreach ($weekendMeetings as $meeting) {
            $dayNames = ['', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            $dayName = $dayNames[$meeting->weekday_tinyint] ?? 'Unknown';
            $distance = $meeting->distance_in_miles ? number_format($meeting->distance_in_miles, 1) : 'N/A';
            echo "  - {$dayName}: {$meeting->meeting_name}\n";
            echo "    {$meeting->location_text} ({$distance} miles)\n";
        }
        echo "\n";

        // Example 3: Complex query with multiple filters
        echo "3. Complex search - Weekday morning meetings with text search...\n";
        $complexSearch = (new MeetingQueryBuilder($client))
            ->onWeekdays(Weekday::MONDAY, Weekday::TUESDAY, Weekday::WEDNESDAY, Weekday::THURSDAY, Weekday::FRIDAY)
            ->startingAfter(6, 0)   // After 6 AM
            ->startingBefore(12, 0) // Before 12 PM
            ->searchText('step')
            ->paginate(10)
            ->execute();

        echo "Found " . count($complexSearch) . " weekday morning meetings with 'step' in description:\n";
        foreach ($complexSearch as $meeting) {
            $dayNames = ['', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            $dayName = $dayNames[$meeting->weekday_tinyint] ?? 'Unknown';
            echo "  - {$dayName} {$meeting->start_time}: {$meeting->meeting_name}\n";
        }
        echo "\n";

        // Example 4: Using coordinates directly
        echo "4. Meetings near Times Square coordinates...\n";
        $coordinates = new Coordinates(40.7580, -73.9855);
        $nearTimesSquare = (new MeetingQueryBuilder($client))
            ->nearCoordinates($coordinates, 1.0) // 1 mile radius
            ->virtualOnly() // Mix virtual with location search
            ->paginate(5)
            ->execute();

        echo "Found " . count($nearTimesSquare) . " virtual meetings near Times Square:\n";
        foreach ($nearTimesSquare as $meeting) {
            echo "  - {$meeting->meeting_name} at {$meeting->start_time}\n";
        }
        echo "\n";

        // Example 5: Parameter inspection (useful for debugging)
        echo "5. Parameter inspection example...\n";
        $queryBuilder = (new MeetingQueryBuilder($client))
            ->virtualOnly()
            ->startingAfter(17, 0)
            ->endingBefore(21, 0)
            ->paginate(10);

        echo "Query parameters: " . json_encode($queryBuilder->getParams(), JSON_PRETTY_PRINT) . "\n\n";

    } catch (Exception $error) {
        echo "Error in fluent query builder examples: " . $error->getMessage() . "\n";
    }
}

function quickSearchExamples(BmltClient $client): void
{
    echo "=== Quick Search Examples ===\n\n";

    try {
        // Example 1: Using QuickSearch for common patterns
        echo "1. Quick search patterns...\n";
        $quickSearch = new QuickSearch($client);

        // Today's meetings
        $todaysMeetings = $quickSearch->today()->paginate(3)->execute();
        echo "Today's meetings (" . count($todaysMeetings) . "):\n";
        foreach ($todaysMeetings as $meeting) {
            echo "  - {$meeting->meeting_name} at {$meeting->start_time}\n";
        }

        // Reset and search for morning meetings
        $morningMeetings = $quickSearch->reset()->morning()->paginate(3)->execute();
        echo "\nMorning meetings (" . count($morningMeetings) . "):\n";
        foreach ($morningMeetings as $meeting) {
            echo "  - {$meeting->meeting_name} at {$meeting->start_time}\n";
        }
        echo "\n";

        // Example 2: Combined quick search patterns
        echo "2. Combined patterns...\n";
        
        // Tonight's virtual meetings
        $tonightVirtual = (new QuickSearch($client))
            ->tonight()
            ->virtualOnly()
            ->paginate(5)
            ->execute();
            
        echo "Tonight's virtual meetings (" . count($tonightVirtual) . "):\n";
        foreach ($tonightVirtual as $meeting) {
            echo "  - {$meeting->meeting_name} at {$meeting->start_time}\n";
        }
        echo "\n";

        // Example 3: Specialized searches
        echo "3. Specialized searches...\n";
        
        // Meditation meetings
        $meditationMeetings = (new QuickSearch($client))
            ->meditation()
            ->paginate(3)
            ->execute();
            
        echo "Meditation meetings (" . count($meditationMeetings) . "):\n";
        foreach ($meditationMeetings as $meeting) {
            echo "  - {$meeting->meeting_name}\n";
        }
        echo "\n";

    } catch (Exception $error) {
        echo "Error in quick search examples: " . $error->getMessage() . "\n";
    }
}

function chainableMethodsDemo(BmltClient $client): void
{
    echo "=== Chainable Methods Demo ===\n\n";
    
    echo "Building a complex query step by step:\n";
    
    // Start with a basic builder
    $builder = new MeetingQueryBuilder($client);
    echo "1. Created basic builder\n";
    
    // Add virtual filter
    $builder->virtualOnly();
    echo "2. Added virtual only filter\n";
    
    // Add time constraints
    $builder->startingAfter(18, 0)->endingBefore(22, 0);
    echo "3. Added time constraints (6 PM - 10 PM)\n";
    
    // Add weekday filter
    $builder->onWeekdays(Weekday::MONDAY, Weekday::WEDNESDAY, Weekday::FRIDAY);
    echo "4. Added weekday filter (Mon, Wed, Fri)\n";
    
    // Add pagination
    $builder->paginate(5);
    echo "5. Added pagination (5 results)\n";
    
    echo "Final parameters: " . json_encode($builder->getParams(), JSON_PRETTY_PRINT) . "\n";
    
    // Execute the query
    $results = $builder->execute();
    echo "6. Executed query - found " . count($results) . " meetings\n\n";
}

// Run all examples
echo "BMLT PHP Query Client - Fluent Query Builder Examples\n";
echo "====================================================\n\n";

fluentQueryBuilderExamples($client);
quickSearchExamples($client);
chainableMethodsDemo($client);

echo "=== All fluent query builder examples completed! ===\n";