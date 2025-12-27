<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken;
use App\Mail\InviteUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminUserController extends Controller
{
    public function index()
    {
        return response()->json(
            User::orderByRaw('COALESCE(nickname, email) ASC')->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
        ], [
            'email.unique' => 'Diese E-Mail-Adresse ist bereits registriert.',
        ]);

        // Generate random password (min 8 characters, meets password policy)
        $randomPassword = $this->generateRandomPassword();

        // Generate temporary unique nickname from email
        $nickname = $this->generateUniqueNickname($request->email);

        $user = User::create([
            'email' => $request->email,
            'nickname' => $nickname,
            'password' => Hash::make($randomPassword),
            'status' => 'invited', // Admin-initiated invitation
        ]);

        // Send invitation email with verification link and password
        $token = EmailVerificationToken::createToken($user->id, $user->email, 'registration');
        Mail::to($user->email)->send(new InviteUser($user, $token, $randomPassword));

        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|in:active,disabled',
            'is_admin' => 'boolean',
        ]);

        $user->update($request->only(['status', 'is_admin']));

        return response()->json($user);
    }

    /**
     * Generate a random password that meets the password policy (min 8 characters)
     */
    private function generateRandomPassword(): string
    {
        // Generate a secure random password with at least 12 characters
        // Using a mix of uppercase, lowercase, numbers, and special characters
        $length = 12;
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $password;
    }

    /**
     * Generate a unique nickname from email address
     */
    private function generateUniqueNickname(string $email): string
    {
        // Extract username part from email (before @)
        $baseNickname = explode('@', $email)[0];
        
        // Clean up: remove special characters, keep only alphanumeric
        $baseNickname = preg_replace('/[^a-zA-Z0-9]/', '', $baseNickname);
        
        // Ensure minimum length
        if (strlen($baseNickname) < 3) {
            $baseNickname = 'user' . $baseNickname;
        }
        
        // Limit length to 20 characters (nickname field limit)
        $baseNickname = substr($baseNickname, 0, 20);
        
        // Check if nickname exists, if so add random suffix
        $nickname = $baseNickname;
        $attempts = 0;
        while (User::where('nickname', $nickname)->exists() && $attempts < 10) {
            $suffix = random_int(1000, 9999);
            $nickname = substr($baseNickname, 0, 16) . $suffix; // Ensure total length <= 20
            $attempts++;
        }
        
        // If still not unique after 10 attempts, use timestamp
        if (User::where('nickname', $nickname)->exists()) {
            $nickname = substr($baseNickname, 0, 12) . time() % 10000;
        }
        
        return $nickname;
    }

    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Du kannst dich nicht selbst löschen.'], 403);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Benutzer gelöscht.']);
    }
}

