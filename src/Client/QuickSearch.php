<?php

declare(strict_types=1);

namespace BmltEnabled\BmltQueryClient\Client;

use BmltEnabled\BmltQueryClient\Types\VenueType;
use BmltEnabled\BmltQueryClient\Types\Weekday;

/**
 * Quick search helper with pre-built search patterns.
 * 
 * Extends MeetingQueryBuilder to provide convenient methods for common
 * search patterns like "today's meetings", "evening meetings", etc.
 * All methods are chainable and return the QuickSearch instance.
 * 
 * @package BmltEnabled\BmltQueryClient\Client
 * @author  Patrick Joyce
 * @since   1.0.0
 * 
 * @example
 * ```php
 * $quickSearch = new QuickSearch($client);
 * 
 * // Get today's virtual meetings
 * $meetings = $quickSearch->today()->virtualOnly()->execute();
 * 
 * // Get tonight's in-person meetings
 * $meetings = $quickSearch->tonight()->inPersonOnly()->execute();
 * ```
 */
class QuickSearch extends MeetingQueryBuilder
{
    /**
     * Get meetings happening today.
     * 
     * Filters meetings to only those occurring on the current day of the week.
     * 
     * @return self Returns $this for method chaining
     * 
     * @example
     * ```php
     * $todaysMeetings = $quickSearch->today()->execute();
     * ```
     */
    public function today(): self
    {
        $todayWeekday = (int) date('w') + 1; // PHP: 0=Sunday, BMLT: 1=Sunday
        if ($todayWeekday > 7) {
            $todayWeekday = 1;
        }
        
        return $this->onWeekdays($todayWeekday);
    }

    /**
     * Get meetings happening tomorrow
     */
    public function tomorrow(): self
    {
        $tomorrowWeekday = (int) date('w') + 2; // PHP: 0=Sunday, BMLT: 1=Sunday
        if ($tomorrowWeekday > 7) {
            $tomorrowWeekday = 1;
        }
        
        return $this->onWeekdays($tomorrowWeekday);
    }

    /**
     * Get this week's meetings
     */
    public function thisWeek(): self
    {
        // Return all meetings (no weekday filter)
        return $this;
    }

    /**
     * Get weekend meetings (Saturday and Sunday)
     */
    public function weekend(): self
    {
        return $this->onWeekdays(Weekday::SATURDAY, Weekday::SUNDAY);
    }

    /**
     * Get weekday meetings (Monday through Friday)
     */
    public function weekdays(): self
    {
        return $this->onWeekdays(
            Weekday::MONDAY,
            Weekday::TUESDAY,
            Weekday::WEDNESDAY,
            Weekday::THURSDAY,
            Weekday::FRIDAY
        );
    }

    /**
     * Get morning meetings (before 12 PM)
     */
    public function morning(): self
    {
        return $this->startingBefore(12, 0);
    }

    /**
     * Get afternoon meetings (12 PM - 5 PM)
     */
    public function afternoon(): self
    {
        return $this->startingAfter(12, 0)->startingBefore(17, 0);
    }

    /**
     * Get evening meetings (after 5 PM)
     */
    public function evening(): self
    {
        return $this->startingAfter(17, 0);
    }

    /**
     * Get late night meetings (after 9 PM)
     */
    public function lateNight(): self
    {
        return $this->startingAfter(21, 0);
    }

    /**
     * Get virtual meetings only
     */
    public function virtual(): self
    {
        return $this->virtualOnly();
    }

    /**
     * Get in-person meetings only
     */
    public function inPerson(): self
    {
        return $this->inPersonOnly();
    }

    /**
     * Get hybrid meetings only
     */
    public function hybrid(): self
    {
        return $this->hybridOnly();
    }

    /**
     * Get early morning meetings (before 9 AM)
     */
    public function earlyMorning(): self
    {
        return $this->startingBefore(9, 0);
    }

    /**
     * Get lunch time meetings (11 AM - 2 PM)
     */
    public function lunchtime(): self
    {
        return $this->startingAfter(11, 0)->startingBefore(14, 0);
    }

    /**
     * Get beginner-friendly meetings (searches for beginner-related text)
     */
    public function beginnerFriendly(): self
    {
        return $this->searchText('beginner');
    }

    /**
     * Get meetings with meditation (searches for meditation-related text)
     */
    public function meditation(): self
    {
        return $this->searchText('meditation');
    }

    /**
     * Get step meetings (searches for step-related text)
     */
    public function stepMeetings(): self
    {
        return $this->searchText('step');
    }

    /**
     * Get book study meetings (searches for book-related text)
     */
    public function bookStudy(): self
    {
        return $this->searchText('book');
    }

    /**
     * Get speaker meetings (searches for speaker-related text)
     */
    public function speakerMeetings(): self
    {
        return $this->searchText('speaker');
    }

    /**
     * Get discussion meetings (searches for discussion-related text)
     */
    public function discussionMeetings(): self
    {
        return $this->searchText('discussion');
    }

    /**
     * Combine today + virtual for quick remote access
     */
    public function todayVirtual(): self
    {
        return $this->today()->virtualOnly();
    }

    /**
     * Combine weekend + in-person
     */
    public function weekendInPerson(): self
    {
        return $this->weekend()->inPersonOnly();
    }

    /**
     * Get tonight's meetings (today + evening)
     */
    public function tonight(): self
    {
        return $this->today()->evening();
    }

    /**
     * Get this morning's meetings (today + morning)
     */
    public function thisMorning(): self
    {
        return $this->today()->morning();
    }

    /**
     * Get open meetings (searches for open-related text)
     */
    public function openMeetings(): self
    {
        return $this->searchText('open');
    }

    /**
     * Get closed meetings (searches for closed-related text)
     */
    public function closedMeetings(): self
    {
        return $this->searchText('closed');
    }
}