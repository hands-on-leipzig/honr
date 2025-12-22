<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Location;
use App\Models\Level;
use App\Models\Country;

class ApprovalValidationService
{
    /**
     * Check if an event can be approved
     * Event can only be approved if location AND level are approved
     */
    public function canApproveEvent(Event $event): bool
    {
        if (!$event->relationLoaded('location')) {
            $event->load('location');
        }
        if (!$event->relationLoaded('level')) {
            $event->load('level');
        }

        return $event->location->status === 'approved'
            && $event->level->status === 'approved';
    }

    /**
     * Get validation error message if event cannot be approved
     */
    public function getEventApprovalError(Event $event): ?string
    {
        if (!$event->relationLoaded('location')) {
            $event->load('location');
        }
        if (!$event->relationLoaded('level')) {
            $event->load('level');
        }

        $errors = [];

        if ($event->location->status !== 'approved') {
            $errors[] = "Der Standort '{$event->location->name}' muss zuerst genehmigt werden.";
        }

        if ($event->level->status !== 'approved') {
            $errors[] = "Das Level '{$event->level->name}' muss zuerst genehmigt werden.";
        }

        return !empty($errors) ? implode(' ', $errors) : null;
    }

    /**
     * Check if a location can be approved
     * Location can only be approved if country is approved
     */
    public function canApproveLocation(Location $location): bool
    {
        if (!$location->relationLoaded('country')) {
            $location->load('country');
        }

        return $location->country->status === 'approved';
    }

    /**
     * Get validation error message if location cannot be approved
     */
    public function getLocationApprovalError(Location $location): ?string
    {
        if (!$location->relationLoaded('country')) {
            $location->load('country');
        }

        if ($location->country->status !== 'approved') {
            return "Das Land '{$location->country->name}' muss zuerst genehmigt werden.";
        }

        return null;
    }

    /**
     * Update all events when a location is approved
     */
    public function updateEventsForLocation(Location $location): int
    {
        $updated = 0;

        // Get all events with this location
        $events = Event::where('location_id', $location->id)
            ->with(['level'])
            ->get();

        foreach ($events as $event) {
            // If event is pending and both location and level are now approved, we can approve it
            // But we don't auto-approve, we just check if it can be approved
            // The admin still needs to manually approve the event
            // This is just for validation purposes
        }

        return $updated;
    }

    /**
     * Update all events when a level is approved
     */
    public function updateEventsForLevel(Level $level): int
    {
        $updated = 0;

        // Get all events with this level
        $events = Event::where('level_id', $level->id)
            ->with(['location'])
            ->get();

        foreach ($events as $event) {
            // Similar to location - we don't auto-approve events
        }

        return $updated;
    }
}


