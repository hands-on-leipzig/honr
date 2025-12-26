<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken;
use App\Mail\VerifyEmail;
use App\Mail\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

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

        $exists = User::where('nickname', $request->nickname)->exists();

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
}


