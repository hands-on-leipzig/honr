<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        return response()->json(User::orderBy('nickname')->get());
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

