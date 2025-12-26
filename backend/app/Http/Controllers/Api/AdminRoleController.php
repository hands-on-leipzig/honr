<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\FirstProgram;
use App\Services\EngagementRecognitionService;
use App\Mail\ProposalApproved;
use App\Mail\ProposalRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class AdminRoleController extends Controller
{
    public function index()
    {
        return response()->json(
            Role::with(['firstProgram:id,name', 'proposedByUser:id,nickname,email'])
                ->withCount('engagements')
                ->orderBy('sort_order')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'first_program_id' => 'required|exists:first_programs,id',
            'role_category' => 'nullable|in:volunteer,regional_partner,coach',
            'status' => 'required|in:pending,approved,rejected',
            'rejection_reason' => 'nullable|string|max:1000|required_if:status,rejected',
        ], [
            'rejection_reason.required_if' => 'Bitte gib einen Grund für die Ablehnung an.',
        ]);

        $maxSortOrder = Role::max('sort_order') ?? 0;

        $role = Role::create([
            'name' => $request->name,
            'short_name' => $request->short_name,
            'description' => $request->description,
            'sort_order' => $maxSortOrder + 1,
            'first_program_id' => $request->first_program_id,
            'role_category' => $request->role_category,
            'status' => $request->status,
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Reload to get relationships
        $role->load(['firstProgram:id,name', 'proposedByUser:id,nickname,email']);

        // If role was created as approved, update all related engagements
        if ($request->status === 'approved') {
            $service = new EngagementRecognitionService();
            $service->updateEngagementsForRole($role);
        }

        // Send notification email if created as approved/rejected (not pending)
        if ($role->status !== 'pending') {
            $this->sendProposalNotification($role, 'pending', $role->status);
        }

        return response()->json($role, 201);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'first_program_id' => 'required|exists:first_programs,id',
            'role_category' => 'nullable|in:volunteer,regional_partner,coach',
            'status' => 'required|in:pending,approved,rejected',
            'rejection_reason' => 'nullable|string|max:1000|required_if:status,rejected',
        ], [
            'rejection_reason.required_if' => 'Bitte gib einen Grund für die Ablehnung an.',
        ]);

        $oldStatus = $role->status;
        $newStatus = $request->status;

        $role->update([
            'name' => $request->name,
            'short_name' => $request->short_name,
            'description' => $request->description,
            'first_program_id' => $request->first_program_id,
            'role_category' => $request->role_category,
            'status' => $newStatus,
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Reload to get fresh data including relationships
        $role->refresh();
        $role->load(['firstProgram:id,name', 'proposedByUser:id,nickname,email']);

        // If role was just approved, update all related engagements
        if ($oldStatus !== 'approved' && $newStatus === 'approved') {
            $service = new EngagementRecognitionService();
            $service->updateEngagementsForRole($role);
        }

        // Send notification email if status changed
        $this->sendProposalNotification($role, $oldStatus, $newStatus);

        return response()->json($role);
    }

    /**
     * Send notification email to user who proposed the entry
     */
    private function sendProposalNotification(Role $role, string $oldStatus, string $newStatus)
    {
        // Only send if there's a user who proposed it and they want notifications
        if (!$role->proposedByUser || !$role->proposedByUser->email_notify_proposals) {
            return;
        }

        // Only send if status changed from pending
        if ($oldStatus !== 'pending') {
            return;
        }

        $user = $role->proposedByUser;
        $context = $role->firstProgram ? ['first_program' => $role->firstProgram->name] : null;

        if ($newStatus === 'approved') {
            Mail::to($user->email)->send(new ProposalApproved(
                $user,
                'role',
                $role->name,
                $role->description,
                $context
            ));
        } elseif ($newStatus === 'rejected') {
            Mail::to($user->email)->send(new ProposalRejected(
                $user,
                'role',
                $role->name,
                $role->rejection_reason ?? 'Kein Grund angegeben.',
                $role->description,
                $context
            ));
        }
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:roles,id',
        ]);

        foreach ($request->ids as $index => $id) {
            Role::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['message' => 'Reihenfolge aktualisiert.']);
    }

    public function destroy(Role $role)
    {
        if ($role->engagements()->exists()) {
            return response()->json([
                'message' => 'Diese Rolle kann nicht gelöscht werden, da noch Einsätze damit verknüpft sind.'
            ], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Rolle gelöscht.']);
    }

    public function programs()
    {
        return response()->json(
            FirstProgram::orderBy('sort_order')->get(['id', 'name'])
        );
    }

    public function uploadLogo(Request $request, Role $role)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        // Delete old logo if exists
        if ($role->logo_path && Storage::disk('public')->exists($role->logo_path)) {
            Storage::disk('public')->delete($role->logo_path);
        }

        // Get file extension
        $extension = $request->file('logo')->getClientOriginalExtension();
        
        // Rename to {id}.{ext}
        $filename = $role->id . '.' . $extension;
        $path = 'logos/roles/' . $filename;

        // Store file
        $request->file('logo')->storeAs('logos/roles', $filename, 'public');

        // Update database
        $role->update(['logo_path' => $path]);

        return response()->json([
            'logo_path' => $path,
            'logo_url' => Storage::url($path),
        ]);
    }

    public function deleteLogo(Role $role)
    {
        if ($role->logo_path && Storage::disk('public')->exists($role->logo_path)) {
            Storage::disk('public')->delete($role->logo_path);
        }

        $role->update(['logo_path' => null]);

        return response()->json(['message' => 'Logo gelöscht.']);
    }
}

