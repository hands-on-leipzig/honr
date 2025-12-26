<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Engagement;
use App\Models\User;
use App\Models\Role;
use App\Models\Event;
use App\Mail\EngagementRecognized;
use App\Http\Controllers\Api\BadgeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminEngagementController extends Controller
{
    public function index()
    {
        return response()->json(
            Engagement::with([
                'user:id,nickname,email',
                'role:id,name,first_program_id',
                'role.firstProgram:id,name',
                'event:id,date,level_id,location_id,season_id',
                'event.level:id,name',
                'event.location:id,name,city',
                'event.season:id,name',
            ])
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
            'event_id' => 'required|exists:events,id',
        ]);

        // Check for duplicate
        $exists = Engagement::where('user_id', $request->user_id)
            ->where('role_id', $request->role_id)
            ->where('event_id', $request->event_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Dieser Einsatz existiert bereits (gleiche Kombination aus Benutzer, Rolle und Veranstaltung).'
            ], 422);
        }

        // Check if should be recognized
        $user = User::find($request->user_id);
        $role = Role::find($request->role_id);
        $event = Event::find($request->event_id);

        $isRecognized = $user->status === 'active'
            && $role->status === 'approved'
            && $event->status === 'approved';

        $engagement = Engagement::create([
            'user_id' => $request->user_id,
            'role_id' => $request->role_id,
            'event_id' => $request->event_id,
            'is_recognized' => $isRecognized,
            'recognized_at' => $isRecognized ? now() : null,
        ]);

        // Send notification if engagement is recognized
        if ($isRecognized) {
            $this->sendRecognitionNotification($engagement);
        }

        // Check for badge threshold
        if ($isRecognized) {
            $badgeController = new BadgeController();
            $badgeController->checkBadgeThresholds($user, $engagement->role_id);
        }

        return response()->json($engagement->load([
            'user:id,nickname,email',
            'role:id,name',
            'event:id,date,level_id,location_id',
            'event.level:id,name',
            'event.location:id,name,city',
        ]), 201);
    }

    public function update(Request $request, Engagement $engagement)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
            'event_id' => 'required|exists:events,id',
        ]);

        // Check for duplicate (excluding current)
        $exists = Engagement::where('user_id', $request->user_id)
            ->where('role_id', $request->role_id)
            ->where('event_id', $request->event_id)
            ->where('id', '!=', $engagement->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Dieser Einsatz existiert bereits (gleiche Kombination aus Benutzer, Rolle und Veranstaltung).'
            ], 422);
        }

        // Check if should be recognized
        $user = User::find($request->user_id);
        $role = Role::find($request->role_id);
        $event = Event::find($request->event_id);

        $isRecognized = $user->status === 'active'
            && $role->status === 'approved'
            && $event->status === 'approved';

        $wasRecognized = $engagement->is_recognized;

        $engagement->update([
            'user_id' => $request->user_id,
            'role_id' => $request->role_id,
            'event_id' => $request->event_id,
            'is_recognized' => $isRecognized,
            'recognized_at' => $isRecognized && !$wasRecognized ? now() : $engagement->recognized_at,
        ]);

        // Send notification if engagement just became recognized
        if ($isRecognized && !$wasRecognized) {
            $this->sendRecognitionNotification($engagement);
        }

        // Check for badge threshold if engagement is recognized
        if ($isRecognized && !$wasRecognized) {
            $badgeController = new BadgeController();
            $badgeController->checkBadgeThresholds($user, $engagement->role_id);
        }

        return response()->json($engagement->load([
            'user:id,nickname,email',
            'role:id,name',
            'event:id,date,level_id,location_id',
            'event.level:id,name',
            'event.location:id,name,city',
        ]));
    }

    public function destroy(Engagement $engagement)
    {
        $engagement->delete();
        return response()->json(['message' => 'Einsatz gelöscht.']);
    }

    public function options()
    {
        return response()->json([
            'users' => User::where('status', 'active')->orderBy('nickname')->get(['id', 'nickname', 'email']),
            'roles' => Role::where('status', 'approved')->with('firstProgram:id,name')->orderBy('sort_order')->get(['id', 'name', 'first_program_id']),
            'events' => Event::where('status', 'approved')
                ->with(['level:id,name', 'location:id,name,city', 'season:id,name'])
                ->orderBy('date', 'desc')
                ->get(['id', 'date', 'level_id', 'location_id', 'season_id'            ]),
        ]);
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


