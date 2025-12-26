<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Mail\ProposalApproved;
use App\Mail\ProposalRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminCountryController extends Controller
{
    public function index()
    {
        return response()->json(
            Country::with('proposedByUser:id,nickname,email')
                ->withCount('locations')
                ->orderBy('name')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:countries,name',
            'iso_code' => 'required|string|size:2|unique:countries,iso_code',
            'status' => 'required|in:pending,approved,rejected',
            'rejection_reason' => 'nullable|string|max:1000|required_if:status,rejected',
        ], [
            'name.unique' => 'Dieser Ländername existiert bereits.',
            'iso_code.unique' => 'Dieser ISO-Code existiert bereits.',
            'iso_code.size' => 'Der ISO-Code muss genau 2 Zeichen haben.',
            'rejection_reason.required_if' => 'Bitte gib einen Grund für die Ablehnung an.',
        ]);

        $country = Country::create([
            'name' => $request->name,
            'iso_code' => strtoupper($request->iso_code),
            'status' => $request->status,
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Reload to get relationships
        $country->load('proposedByUser:id,nickname,email');

        // Send notification email if created as approved/rejected (not pending)
        if ($country->status !== 'pending') {
            $this->sendProposalNotification($country, 'pending', $country->status);
        }

        return response()->json($country, 201);
    }

    public function update(Request $request, Country $country)
    {
        $oldStatus = $country->status;

        $request->validate([
            'name' => 'required|string|max:255|unique:countries,name,' . $country->id,
            'iso_code' => 'required|string|size:2|unique:countries,iso_code,' . $country->id,
            'status' => 'required|in:pending,approved,rejected',
            'rejection_reason' => 'nullable|string|max:1000|required_if:status,rejected',
        ], [
            'name.unique' => 'Dieser Ländername existiert bereits.',
            'iso_code.unique' => 'Dieser ISO-Code existiert bereits.',
            'iso_code.size' => 'Der ISO-Code muss genau 2 Zeichen haben.',
            'rejection_reason.required_if' => 'Bitte gib einen Grund für die Ablehnung an.',
        ]);

        $country->update([
            'name' => $request->name,
            'iso_code' => strtoupper($request->iso_code),
            'status' => $request->status,
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Reload to get fresh data including relationships
        $country->refresh();
        $country->load('proposedByUser:id,nickname,email');

        // Send notification email if status changed
        $this->sendProposalNotification($country, $oldStatus, $country->status);

        return response()->json($country);
    }

    /**
     * Send notification email to user who proposed the entry
     */
    private function sendProposalNotification(Country $country, string $oldStatus, string $newStatus)
    {
        // Only send if there's a user who proposed it and they want notifications
        if (!$country->proposedByUser || !$country->proposedByUser->email_notify_proposals) {
            return;
        }

        // Only send if status changed from pending
        if ($oldStatus !== 'pending') {
            return;
        }

        $user = $country->proposedByUser;

        if ($newStatus === 'approved') {
            Mail::to($user->email)->send(new ProposalApproved(
                $user,
                'country',
                $country->name,
                null,
                ['iso_code' => $country->iso_code]
            ));
        } elseif ($newStatus === 'rejected') {
            Mail::to($user->email)->send(new ProposalRejected(
                $user,
                'country',
                $country->name,
                $country->rejection_reason ?? 'Kein Grund angegeben.',
                null,
                ['iso_code' => $country->iso_code]
            ));
        }
    }

    public function destroy(Country $country)
    {
        if ($country->locations()->exists()) {
            return response()->json([
                'message' => 'Dieses Land kann nicht gelöscht werden, da noch Standorte damit verknüpft sind.'
            ], 422);
        }

        $country->delete();

        return response()->json(['message' => 'Land gelöscht.']);
    }
}

