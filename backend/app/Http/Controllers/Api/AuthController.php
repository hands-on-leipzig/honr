<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken;
use App\Mail\VerifyEmail;
use App\Mail\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Die Anmeldedaten sind ungültig.'],
            ]);
        }

        // Only allow login if user status is 'active' (email verified)
        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Bitte bestätige zuerst deine E-Mail-Adresse.'],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Erfolgreich abgemeldet.']);
    }

    public function checkNickname(Request $request)
    {
        $request->validate([
            'nickname' => 'required|string',
        ]);

        $query = User::where('nickname', $request->nickname);

        // When called from wizard/settings, exclude the current user so their own nickname is "available"
        $token = $request->bearerToken();
        if ($token) {
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($accessToken && $accessToken->tokenable instanceof User) {
                $query->where('id', '!=', $accessToken->tokenable->id);
            }
        }

        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'nickname' => 'required|string|max:255|unique:users,nickname',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique' => 'Diese E-Mail-Adresse ist bereits registriert.',
            'nickname.unique' => 'Dieser Name ist bereits vergeben.',
        ]);

        $user = User::create([
            'email' => $request->email,
            'nickname' => $request->nickname,
            'password' => Hash::make($request->password),
            'status' => 'requested', // Requires email confirmation
        ]);

        // Generate verification token and send email
        $token = EmailVerificationToken::createToken($user->id, $user->email, 'registration');
        Mail::to($user->email)->send(new VerifyEmail($user, $token));

        return response()->json([
            'message' => 'Registrierung erfolgreich. Bitte bestätige deine E-Mail-Adresse.',
        ], 201);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Use Laravel's built-in password reset tokens
            $token = Password::createToken($user);
            
            // Send reset email
            Mail::to($user->email)->send(new ResetPassword($user, $token));
        }

        // Always return success to prevent email enumeration
        return response()->json([
            'message' => 'Falls ein Konto mit dieser E-Mail existiert, wurde ein Link zum Zurücksetzen gesendet.',
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $verificationToken = EmailVerificationToken::verifyToken(
            $request->token,
            $request->email,
            'registration'
        );

        if (!$verificationToken) {
            return response()->json([
                'message' => 'Ungültiger oder abgelaufener Bestätigungslink.',
            ], 400);
        }

        $user = User::findOrFail($verificationToken->user_id);

        // Verify email matches
        if ($user->email !== $request->email) {
            return response()->json([
                'message' => 'E-Mail-Adresse stimmt nicht überein.',
            ], 400);
        }

        // Update user status to active
        $user->update(['status' => 'active']);

        // Delete used token
        $verificationToken->delete();

        return response()->json([
            'message' => 'E-Mail-Adresse erfolgreich bestätigt. Du kannst dich jetzt anmelden.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.confirmed' => 'Die Passwort-Bestätigung stimmt nicht überein.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Benutzer nicht gefunden.',
            ], 404);
        }

        // Verify token using Laravel's password reset
        $status = Password::reset(
            [
                'email' => $request->email,
                'password' => $request->password,
                'password_confirmation' => $request->input('password_confirmation', $request->password),
                'token' => $request->token,
            ],
            function ($user, $password) {
                $user->update([
                    'password' => Hash::make($password),
                ]);
            }
        );

        if ($status === Password::INVALID_TOKEN) {
            return response()->json([
                'message' => 'Ungültiger oder abgelaufener Reset-Link.',
            ], 400);
        }

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Fehler beim Zurücksetzen des Passworts.',
            ], 400);
        }

        return response()->json([
            'message' => 'Passwort erfolgreich zurückgesetzt.',
        ]);
    }

    public function verifyEmailChange(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $verificationToken = EmailVerificationToken::verifyToken(
            $request->token,
            $request->email,
            'email_change'
        );

        if (!$verificationToken) {
            return response()->json([
                'message' => 'Ungültiger oder abgelaufener Bestätigungslink.',
            ], 400);
        }

        $user = User::findOrFail($verificationToken->user_id);

        // Update user email
        $user->update(['email' => $request->email]);

        // Delete used token
        $verificationToken->delete();

        return response()->json([
            'message' => 'E-Mail-Adresse erfolgreich geändert.',
        ]);
    }

    /**
     * Redirect to Keycloak for SSO login.
     * Uses stateless() so we don't require session (avoids InvalidState when callback has no session cookie).
     */
    public function redirectToKeycloak()
    {
        return Socialite::driver('keycloak')->stateless()->redirect();
    }

    /**
     * Handle Keycloak callback. preferred_username is mapped to email (Keycloak usernames are required to be email addresses).
     */
    public function handleKeycloakCallback(Request $request)
    {
        $frontendUrl = rtrim(config('app.frontend_url'), '/');
        $loginPath = '/login';

        Log::info('SSO callback hit', [
            'query_keys' => array_keys($request->query()),
            'has_code' => $request->has('code'),
            'error' => $request->query('error'),
            'error_description' => $request->query('error_description'),
        ]);

        try {
            $oauthUser = Socialite::driver('keycloak')->stateless()->user();
        } catch (\Throwable $e) {
            Log::error('SSO callback: Socialite user() failed', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect($frontendUrl . $loginPath . '?sso_error=' . urlencode('SSO-Anmeldung fehlgeschlagen.'));
        }

        // Require Keycloak realm role "volunteer"
        $accessToken = $oauthUser->token ?? null;
        $volunteerOk = $accessToken && $this->keycloakUserHasVolunteerRole($accessToken, $oauthUser->getNickname());
        if (! $volunteerOk) {
            return redirect($frontendUrl . $loginPath . '?sso_error=' . urlencode('Rolle "volunteer" in Keycloak fehlt.'));
        }

        $email = $oauthUser->getNickname(); // preferred_username – required to be email in Keycloak
        if (empty($email) || ! is_string($email)) {
            Log::warning('SSO callback: preferred_username missing', ['oauth_user_nickname' => $oauthUser->getNickname()]);
            return redirect($frontendUrl . $loginPath . '?sso_error=' . urlencode('Keycloak preferred_username fehlt.'));
        }

        $nameFromIdp = $oauthUser->getName() ?: $email;

        $user = User::where('email', $email)->first();

        if (! $user) {
            $baseNickname = $nameFromIdp ?: $email;
            $nickname = $baseNickname;
            $suffix = 0;
            while (User::where('nickname', $nickname)->exists()) {
                $suffix++;
                $nickname = $baseNickname . ' (' . $suffix . ')';
            }
            $user = User::create([
                'email' => $email,
                'password' => Hash::make(Str::random(64)),
                'status' => 'active',
                'nickname' => $nickname,
            ]);
        } else {
            if ($nameFromIdp !== '' && $user->nickname !== $nameFromIdp) {
                $user->update(['nickname' => $nameFromIdp]);
            }
        }

        $user->update(['last_login_at' => now()]);
        $token = $user->createToken('auth-token')->plainTextToken;

        return redirect($frontendUrl . $loginPath . '?sso_token=' . urlencode($token));
    }

    /**
     * Decode Keycloak access token (JWT) and check for realm role "volunteer".
     */
    private function keycloakUserHasVolunteerRole(string $accessToken, ?string $nickname = null): bool
    {
        $parts = explode('.', $accessToken);
        if (count($parts) !== 3) {
            Log::warning('SSO volunteer check: invalid JWT segment count', ['count' => count($parts), 'nickname' => $nickname]);
            return false;
        }
        $payload = json_decode($this->base64UrlDecode($parts[1]), true);
        if (! is_array($payload)) {
            Log::warning('SSO volunteer check: JWT payload decode failed', ['nickname' => $nickname]);
            return false;
        }
        $realmRoles = $payload['realm_access']['roles'] ?? [];
        $hasVolunteer = in_array('volunteer', $realmRoles, true);

        if (! $hasVolunteer) {
            $resourceAccess = $payload['resource_access'] ?? [];
            Log::info('SSO volunteer check: volunteer role not in realm_access.roles', [
                'nickname' => $nickname,
                'realm_roles' => $realmRoles,
                'resource_access_keys' => array_keys($resourceAccess),
                'resource_access_roles' => array_map(fn ($r) => $r['roles'] ?? [], $resourceAccess),
            ]);
        }

        return $hasVolunteer;
    }

    private function base64UrlDecode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $input .= str_repeat('=', 4 - $remainder);
        }

        return (string) base64_decode(strtr($input, '-_', '+/'), true);
    }
}


