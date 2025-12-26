<?php

namespace App\Services;

use App\Models\Engagement;
use App\Models\Role;
use App\Models\Event;
use App\Models\User;
use App\Mail\EngagementRecognized;
use App\Http\Controllers\Api\BadgeController;
use Illuminate\Support\Facades\Mail;

class EngagementRecognitionService
{
    /**
     * Recalculate and update recognition status for all engagements with a specific role
     */
    public function updateEngagementsForRole(Role $role): int
    {
        $updated = 0;

        // Get all engagements with this role
        $engagements = Engagement::where('role_id', $role->id)
            ->with(['user', 'event'])
            ->get();

        foreach ($engagements as $engagement) {
            $shouldBeRecognized = $this->shouldBeRecognized($engagement);
            $wasRecognized = $engagement->is_recognized;
            
            if ($engagement->is_recognized !== $shouldBeRecognized) {
                $engagement->update([
                    'is_recognized' => $shouldBeRecognized,
                    'recognized_at' => $shouldBeRecognized && !$engagement->recognized_at ? now() : $engagement->recognized_at,
                ]);
                $updated++;

                // Send notification if engagement just became recognized
                if ($shouldBeRecognized && !$wasRecognized) {
                    $this->sendRecognitionNotification($engagement);
                    
                    // Check for badge threshold
                    $badgeController = new BadgeController();
                    $badgeController->checkBadgeThresholds($engagement->user, $engagement->role_id);
                }
            }
        }

        return $updated;
    }

    /**
     * Recalculate and update recognition status for all engagements with a specific event
     */
    public function updateEngagementsForEvent(Event $event): int
    {
        $updated = 0;

        // Get all engagements with this event
        $engagements = Engagement::where('event_id', $event->id)
            ->with(['user', 'role'])
            ->get();

        foreach ($engagements as $engagement) {
            $shouldBeRecognized = $this->shouldBeRecognized($engagement);
            $wasRecognized = $engagement->is_recognized;
            
            if ($engagement->is_recognized !== $shouldBeRecognized) {
                $engagement->update([
                    'is_recognized' => $shouldBeRecognized,
                    'recognized_at' => $shouldBeRecognized && !$engagement->recognized_at ? now() : $engagement->recognized_at,
                ]);
                $updated++;

                // Send notification if engagement just became recognized
                if ($shouldBeRecognized && !$wasRecognized) {
                    $this->sendRecognitionNotification($engagement);
                    
                    // Check for badge threshold
                    $badgeController = new BadgeController();
                    $badgeController->checkBadgeThresholds($engagement->user, $engagement->role_id);
                }
            }
        }

        return $updated;
    }

    /**
     * Check if an engagement should be recognized
     */
    public function shouldBeRecognized(Engagement $engagement): bool
    {
        // Load relationships if not already loaded
        if (!$engagement->relationLoaded('user')) {
            $engagement->load('user');
        }
        if (!$engagement->relationLoaded('role')) {
            $engagement->load('role');
        }
        if (!$engagement->relationLoaded('event')) {
            $engagement->load('event');
        }

        // Engagement is recognized if:
        // 1. User is active
        // 2. Role is approved
        // 3. Event is approved
        return $engagement->user->status === 'active'
            && $engagement->role->status === 'approved'
            && $engagement->event->status === 'approved';
    }

    /**
     * Send notification email when engagement becomes recognized
     */
    private function sendRecognitionNotification(Engagement $engagement)
    {
        // Reload engagement with relationships
        $engagement->load(['user', 'role.firstProgram', 'event.level', 'event.location', 'event.season']);

        // Only send if user wants proposal notifications (reusing this preference for recognition)
        if (!$engagement->user->email_notify_proposals) {
            return;
        }

        Mail::to($engagement->user->email)->send(new EngagementRecognized($engagement->user, $engagement));
    }
}


