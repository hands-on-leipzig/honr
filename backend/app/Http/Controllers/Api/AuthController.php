<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        // TODO: Send confirmation email with verification link
        // Mail::to($user->email)->send(new VerifyEmail($user));

        return response()->json([
            'message' => 'Registrierung erfolgreich. Bitte bestätige deine E-Mail-Adresse.',
        ], 201);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // TODO: Implement actual password reset email
        // $user = User::where('email', $request->email)->first();
        // if ($user) {
        //     $token = Str::random(64);
        //     DB::table('email_verification_tokens')->insert([
        //         'user_id' => $user->id,
        //         'email' => $user->email,
        //         'token' => Hash::make($token),
        //         'type' => 'password_reset',
        //         'expires_at' => now()->addHours(24),
        //         'created_at' => now(),
        //     ]);
        //     Mail::to($user->email)->send(new ResetPassword($user, $token));
        // }

        // Always return success to prevent email enumeration
        return response()->json([
            'message' => 'Falls ein Konto mit dieser E-Mail existiert, wurde ein Link zum Zurücksetzen gesendet.',
        ]);
    }
}


