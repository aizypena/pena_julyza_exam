<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return response()->json($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'sometimes|string|in:admin,guest',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'guest',
            'status' => 'active',
            'email_verified_at' => now(), // Admin-created users are verified
        ]);

        // Log the activity
        ActivityLogService::logCreate('User', $user->id, $user->name, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:6',
            'role' => 'sometimes|string|in:admin,guest',
            'status' => 'sometimes|string|in:active,inactive',
        ]);

        $oldValues = $user->toArray();

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        // Log the activity
        ActivityLogService::logUpdate('User', $user->id, $user->name, $oldValues, $user->toArray());

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $oldValues = $user->toArray();
        $userName = $user->name;

        $user->delete();

        // Log the activity
        ActivityLogService::logDelete('User', $oldValues['id'], $userName, $oldValues);

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}
