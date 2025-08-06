<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['rooms', 'messages']);

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by online status (users who have been active in last 5 minutes)
        if ($request->boolean('online_only')) {
            $query->where('updated_at', '>=', now()->subMinutes(5));
        }

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::with(['rooms', 'messages', 'createdRooms'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $currentUser = $request->user();

        // Users can only update their own profile, or admins can update any profile
        if ($user->id !== $currentUser->id && !$currentUser->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only update your own profile.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'sometimes|string|in:user,moderator,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Only admins can change roles
        if ($request->has('role') && !$currentUser->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can change user roles.'
            ], 403);
        }

        $user->update($request->only(['name', 'email', 'role']));

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $currentUser = $request->user();

        // Only admins can delete users
        if (!$currentUser->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can delete users.'
            ], 403);
        }

        // Prevent admin from deleting themselves
        if ($user->id === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get user's rooms.
     */
    public function rooms(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $rooms = $user->rooms()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $rooms
        ]);
    }

    /**
     * Get user's messages.
     */
    public function messages(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $messages = $user->messages()->with('room')->latest()->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Get online users.
     */
    public function onlineUsers(): JsonResponse
    {
        $users = User::where('updated_at', '>=', now()->subMinutes(5))
                    ->select('id', 'name', 'email', 'updated_at')
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Get user statistics.
     */
    public function statistics(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $stats = [
            'total_rooms' => $user->rooms()->count(),
            'total_messages' => $user->messages()->count(),
            'created_rooms' => $user->createdRooms()->count(),
            'admin_rooms' => $user->rooms()->wherePivot('is_admin', true)->count(),
            'last_message' => $user->messages()->latest()->first(),
            'joined_at' => $user->created_at,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
