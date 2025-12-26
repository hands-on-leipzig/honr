<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Mail\ProposalApproved;
use App\Mail\ProposalRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminLevelController extends Controller
{
    public function index()
    {
        return response()->json(
            Level::with('proposedByUser:id,nickname,email')
                ->withCount('events')
                ->orderBy('sort_order')
                ->get()
        );
    }

    /**
     * Send notification email to user who proposed the entry
     */
    private function sendProposalNotification(Level $level, string $oldStatus, string $newStatus)
    {
        // Only send if there's a user who proposed it and they want notifications
        if (!$level->proposedByUser || !$level->proposedByUser->email_notify_proposals) {
            return;
        }

        // Only send if status changed from pending
        if ($oldStatus !== 'pending') {
            return;
        }

        $user = $level->proposedByUser;

        if ($newStatus === 'approved') {
            Mail::to($user->email)->send(new ProposalApproved(
                $user,
                'level',
                $level->name,
                $level->description
            ));
        } elseif ($newStatus === 'rejected') {
            Mail::to($user->email)->send(new ProposalRejected(
                $user,
                'level',
                $level->name,
                $level->rejection_reason ?? 'Kein Grund angegeben.',
                $level->description
            ));
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:levels,name',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'required|integer',
            'status' => 'required|in:pending,approved,rejected',
            'rejection_reason' => 'nullable|string|max:1000|required_if:status,rejected',
        ], [
            'name.unique' => 'Dieser Level-Name existiert bereits.',
            'rejection_reason.required_if' => 'Bitte gib einen Grund für die Ablehnung an.',
        ]);

        $level = Level::create($request->only(['name', 'description', 'sort_order', 'status', 'rejection_reason']));

        // Reload to get relationships
        $level->load('proposedByUser:id,nickname,email');

        // Send notification email if created as approved/rejected (not pending)
        if ($level->status !== 'pending') {
            $this->sendProposalNotification($level, 'pending', $level->status);
        }

        return response()->json($level, 201);
    }

    public function update(Request $request, Level $level)
    {
        $oldStatus = $level->status;

        $request->validate([
            'name' => 'required|string|max:255|unique:levels,name,' . $level->id,
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'required|integer',
            'status' => 'required|in:pending,approved,rejected',
            'rejection_reason' => 'nullable|string|max:1000|required_if:status,rejected',
        ], [
            'name.unique' => 'Dieser Level-Name existiert bereits.',
            'rejection_reason.required_if' => 'Bitte gib einen Grund für die Ablehnung an.',
        ]);

        $level->update($request->only(['name', 'description', 'sort_order', 'status', 'rejection_reason']));

        // Reload to get fresh data including relationships
        $level->refresh();
        $level->load('proposedByUser:id,nickname,email');

        // Send notification email if status changed
        $this->sendProposalNotification($level, $oldStatus, $level->status);

        return response()->json($level);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:levels,id',
            'items.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->items as $item) {
            Level::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Reihenfolge aktualisiert.']);
    }

    public function destroy(Level $level)
    {
        if ($level->events()->exists()) {
            return response()->json([
                'message' => 'Dieses Level kann nicht gelöscht werden, da noch Veranstaltungen damit verknüpft sind.'
            ], 422);
        }

        $level->delete();

        return response()->json(['message' => 'Level gelöscht.']);
    }
}

