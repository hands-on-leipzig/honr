<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Country;
use App\Services\ApprovalValidationService;
use App\Mail\ProposalApproved;
use App\Mail\ProposalRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminLocationController extends Controller
{
    public function index()
    {
        return response()->json(
            Location::with(['country:id,name,iso_code', 'regionalPartner:id,name', 'proposedByUser:id,nickname,email,email_notify_proposals'])
                ->withCount('events')
                ->orderBy('name')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:locations,name',
            'country_id' => 'required|exists:countries,id',
            'regional_partner_id' => 'nullable|exists:regional_partners,id',
            'street_address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:pending,approved,rejected',
            'rejection_reason' => 'nullable|string|max:1000|required_if:status,rejected',
        ], [
            'name.unique' => 'Dieser Standortname existiert bereits.',
            'rejection_reason.required_if' => 'Bitte gib einen Grund für die Ablehnung an.',
        ]);

        // Validate approval dependencies if trying to approve
        if ($request->status === 'approved') {
            $country = Country::find($request->country_id);
            if ($country && $country->status !== 'approved') {
                return response()->json([
                    'message' => "Das Land '{$country->name}' muss zuerst genehmigt werden."
                ], 422);
            }
        }

        $location = Location::create($request->only([
            'name', 'country_id', 'regional_partner_id', 'street_address', 'city', 'postal_code',
            'latitude', 'longitude', 'status', 'rejection_reason'
        ]));

        // Reload to get relationships
        $location->load(['country:id,name,iso_code', 'regionalPartner:id,name', 'proposedByUser:id,nickname,email,email_notify_proposals']);

        // Send notification email if created as approved/rejected (not pending)
        if ($location->status !== 'pending') {
            $this->sendProposalNotification($location, 'pending', $location->status);
        }

        return response()->json($location, 201);
    }

    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:locations,name,' . $location->id,
            'country_id' => 'required|exists:countries,id',
            'regional_partner_id' => 'nullable|exists:regional_partners,id',
            'street_address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:pending,approved,rejected',
            'rejection_reason' => 'nullable|string|max:1000|required_if:status,rejected',
        ], [
            'name.unique' => 'Dieser Standortname existiert bereits.',
            'rejection_reason.required_if' => 'Bitte gib einen Grund für die Ablehnung an.',
        ]);

        // Validate approval dependencies
        $oldStatus = $location->status;
        $newStatus = $request->status;
        $oldCountryId = $location->country_id;

        // If trying to approve, check dependencies with NEW country
        if ($oldStatus !== 'approved' && $newStatus === 'approved') {
            $newCountry = Country::find($request->country_id);
            if (!$newCountry || $newCountry->status !== 'approved') {
                return response()->json([
                    'message' => "Das Land '{$newCountry->name}' muss zuerst genehmigt werden."
                ], 422);
            }
        }

        $location->update($request->only([
            'name', 'country_id', 'regional_partner_id', 'street_address', 'city', 'postal_code',
            'latitude', 'longitude', 'status', 'rejection_reason'
        ]));

        // Reload to get fresh data including relationships
        $location->refresh();
        $location->load(['country:id,name,iso_code', 'regionalPartner:id,name', 'proposedByUser:id,nickname,email,email_notify_proposals']);

        // Send notification email if status changed
        $this->sendProposalNotification($location, $oldStatus, $newStatus);

        // If location status changed or country changed, we need to update related events
        // But events don't auto-update, so we just validate on approval
        // The events will be validated when someone tries to approve them

        return response()->json($location);
    }

    public function destroy(Location $location)
    {
        if ($location->events()->exists()) {
            return response()->json([
                'message' => 'Dieser Standort kann nicht gelöscht werden, da noch Veranstaltungen damit verknüpft sind.'
            ], 422);
        }

        $location->delete();

        return response()->json(['message' => 'Standort gelöscht.']);
    }

    public function countries()
    {
        return response()->json(
            Country::orderBy('name')
                ->get(['id', 'name', 'iso_code', 'status'])
        );
    }

    /**
     * Send notification email to user who proposed the entry
     */
    private function sendProposalNotification(Location $location, string $oldStatus, string $newStatus)
    {
        // Only send if there's a user who proposed it and they want notifications
        if (!$location->proposedByUser || !$location->proposedByUser->email_notify_proposals) {
            return;
        }

        // Only send if status changed from pending
        if ($oldStatus !== 'pending') {
            return;
        }

        $user = $location->proposedByUser;
        $context = $location->country ? ['country' => $location->country->name] : null;

        if ($newStatus === 'approved') {
            Mail::to($user->email)->send(new ProposalApproved(
                $user,
                'location',
                $location->name,
                null,
                $context
            ));
        } elseif ($newStatus === 'rejected') {
            Mail::to($user->email)->send(new ProposalRejected(
                $user,
                'location',
                $location->name,
                $location->rejection_reason ?? 'Kein Grund angegeben.',
                null,
                $context
            ));
        }
    }
}

