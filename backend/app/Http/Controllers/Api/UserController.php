<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Engagement;
use App\Models\EmailVerificationToken;
use App\Mail\VerifyEmailChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'nickname' => 'sometimes|string|max:255',
            'short_bio' => 'sometimes|nullable|string|max:1000',
            'contact_link' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        return; // Allow empty/null
                    }
                    
                    $value = trim($value);
                    
                    // Check if it's a valid email
                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return; // Valid email, will be converted to mailto:
                    }
                    
                    // Check if it's already a valid URL (http, https, mailto, tel)
                    if (preg_match('/^(https?|mailto|tel):/i', $value)) {
                        if (!filter_var($value, FILTER_VALIDATE_URL) && !preg_match('/^mailto:/i', $value) && !preg_match('/^tel:/i', $value)) {
                            $fail('Der Kontakt-Link ist ungültig.');
                        }
                        return;
                    }
                    
                    // Check if it could be a URL without protocol
                    if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}/', $value)) {
                        return; // Looks like a domain, will add https://
                    }
                    
                    $fail('Der Kontakt-Link muss eine gültige URL oder E-Mail-Adresse sein.');
                },
            ],
            'email_notify_proposals' => 'sometimes|boolean',
            'email_tool_info' => 'sometimes|boolean',
            'email_volunteer_newsletter' => 'sometimes|boolean',
            'wizard_completed' => 'sometimes|boolean',
        ]);

        $data = $request->only([
            'nickname', 
            'short_bio', 
            'contact_link', 
            'email_notify_proposals',
            'email_tool_info',
            'email_volunteer_newsletter',
            'wizard_completed',
        ]);
        
        // Convert email addresses to mailto: links and normalize URLs
        if (isset($data['contact_link']) && $data['contact_link']) {
            $data['contact_link'] = $this->normalizeContactLink($data['contact_link']);
        }

        $user = $request->user();
        $user->update($data);

        return response()->json($user);
    }

    private function normalizeContactLink($link)
    {
        $link = trim($link);
        
        // If it's already a valid URL with protocol, return as is
        if (preg_match('/^(https?|mailto|tel):/i', $link)) {
            return $link;
        }
        
        // If it looks like an email address, convert to mailto:
        if (filter_var($link, FILTER_VALIDATE_EMAIL)) {
            return 'mailto:' . $link;
        }
        
        // If it looks like a domain (starts with www. or contains a dot), add https://
        if (preg_match('/^(www\.|[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,})/', $link)) {
            if (strpos($link, 'www.') === 0) {
                return 'https://' . $link;
            }
            return 'https://' . $link;
        }
        
        return $link;
    }

    public function requestEmailChange(Request $request)
    {
        $request->validate([
            'new_email' => 'required|email|unique:users,email',
            'password' => 'required',
        ], [
            'new_email.unique' => 'Diese E-Mail-Adresse ist bereits registriert.',
        ]);

        $user = $request->user();

        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Das Passwort ist falsch.'],
            ]);
        }

        // Generate verification token and send email to new address
        $token = EmailVerificationToken::createToken($user->id, $request->new_email, 'email_change');
        Mail::to($request->new_email)->send(new VerifyEmailChange($user, $request->new_email, $token));

        return response()->json([
            'message' => 'Ein Bestätigungslink wurde an die neue E-Mail-Adresse gesendet.',
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',      // at least one uppercase
                'regex:/[a-z]/',      // at least one lowercase
                'regex:/[0-9]/',      // at least one number
            ],
        ], [
            'password.regex' => 'Das Passwort muss mindestens einen Großbuchstaben, einen Kleinbuchstaben und eine Zahl enthalten.',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Das aktuelle Passwort ist falsch.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Passwort erfolgreich geändert.']);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = $request->user();

        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Das Passwort ist falsch.'],
            ]);
        }

        // Revoke all tokens
        $user->tokens()->delete();

        // Delete user
        $user->delete();

        return response()->json(['message' => 'Account erfolgreich gelöscht.']);
    }

    public function index(Request $request)
    {
        $query = User::where('status', 'active');

        // Search by nickname
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('nickname', 'like', "%{$search}%");
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by role_id - users with recognized engagements in this role
        if ($request->has('role_id') && $request->role_id) {
            $query->whereHas('engagements', function ($q) use ($request) {
                $q->where('role_id', $request->role_id)
                  ->where('is_recognized', true);
            });
        }

        // Filter by first_program_id - users with recognized engagements in this program
        if ($request->has('first_program_id') && $request->first_program_id) {
            $query->whereHas('engagements', function ($q) use ($request) {
                $q->where('is_recognized', true)
                  ->whereHas('event', function ($eq) use ($request) {
                      $eq->where('first_program_id', $request->first_program_id);
                  });
            });
        }

        // Filter by season_id - users with recognized engagements in this season
        if ($request->has('season_id') && $request->season_id) {
            $query->whereHas('engagements', function ($q) use ($request) {
                $q->where('is_recognized', true)
                  ->whereHas('event', function ($eq) use ($request) {
                      $eq->where('season_id', $request->season_id);
                  });
            });
        }

        // Filter by country_id - users with recognized engagements in this country
        if ($request->has('country_id') && $request->country_id) {
            $query->whereHas('engagements', function ($q) use ($request) {
                $q->where('is_recognized', true)
                  ->whereHas('event', function ($eq) use ($request) {
                      $eq->whereHas('location', function ($lq) use ($request) {
                          $lq->where('country_id', $request->country_id);
                      });
                  });
            });
        }

        // Filter by event_id - users with recognized engagements at this event
        if ($request->has('event_id') && $request->event_id) {
            $query->whereHas('engagements', function ($q) use ($request) {
                $q->where('event_id', $request->event_id)
                  ->where('is_recognized', true);
            });
        }

        $users = $query->orderBy('nickname')
            ->get(['id', 'nickname', 'short_bio', 'status']);

        return response()->json($users);
    }

    public function show(Request $request, $id)
    {
        $user = User::where('status', 'active')
            ->findOrFail($id);

        return response()->json([
            'id' => $user->id,
            'nickname' => $user->nickname,
            'short_bio' => $user->short_bio,
            'contact_link' => $user->contact_link,
            'status' => $user->status,
        ]);
    }

    public function getUserEngagements(Request $request, $id)
    {
        $user = User::where('status', 'active')
            ->findOrFail($id);

        $engagements = Engagement::where('user_id', $user->id)
            ->with([
                'role:id,name,short_name,first_program_id,status,logo_path,role_category',
                'role.firstProgram:id,name,logo_path',
                'event:id,date,season_id,level_id,location_id,status,first_program_id',
                'event.firstProgram:id,name,logo_path,sort_order',
                'event.season:id,name,logo_path,start_year',
                'event.level:id,name',
                'event.location:id,name,city,country_id,regional_partner_id,latitude,longitude',
                'event.location.country:id,name,iso_code',
                'event.location.regionalPartner:id,name',
            ])
            ->join('events', 'engagements.event_id', '=', 'events.id')
            ->orderBy('events.date', 'desc')
            ->select('engagements.*')
            ->get();

        return response()->json($engagements);
    }
}
