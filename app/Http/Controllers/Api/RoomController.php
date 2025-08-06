<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Room::with(['creator', 'users']);

        // Filter by search term
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by privacy
        if ($request->has('is_private')) {
            $query->where('is_private', $request->boolean('is_private'));
        }

        // Show only rooms user is member of
        if ($request->boolean('my_rooms')) {
            $query->whereHas('users', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Show only public rooms
        if ($request->boolean('public_only')) {
            $query->where('is_private', false);
        }

        $rooms = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $rooms
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
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_private' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $room = Room::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_private' => $request->boolean('is_private', false),
            'created_by' => $request->user()->id,
        ]);

        // Add creator as admin member
        $room->users()->attach($request->user()->id, [
            'is_admin' => true,
            'joined_at' => now()
        ]);

        $room->load(['creator', 'users']);

        return response()->json([
            'success' => true,
            'message' => 'Room created successfully',
            'data' => $room
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $room = Room::with(['creator', 'users', 'messages.user'])
                    ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $room
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
        $room = Room::findOrFail($id);
        $user = $request->user();

        // Check if user is admin of the room
        if (!$room->isUserAdmin($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only room admins can update room details.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_private' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $room->update($request->only(['name', 'description', 'is_private']));
        $room->load(['creator', 'users']);

        return response()->json([
            'success' => true,
            'message' => 'Room updated successfully',
            'data' => $room
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $room = Room::findOrFail($id);
        $user = $request->user();

        // Check if user is admin of the room
        if (!$room->isUserAdmin($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only room admins can delete rooms.'
            ], 403);
        }

        $room->delete();

        return response()->json([
            'success' => true,
            'message' => 'Room deleted successfully'
        ]);
    }

    /**
     * Join a room.
     */
    public function join(Request $request, string $id): JsonResponse
    {
        $room = Room::findOrFail($id);
        $user = $request->user();

        if ($room->hasUser($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are already a member of this room'
            ], 400);
        }

        $room->users()->attach($user->id, [
            'joined_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully joined the room'
        ]);
    }

    /**
     * Leave a room.
     */
    public function leave(Request $request, string $id): JsonResponse
    {
        $room = Room::findOrFail($id);
        $user = $request->user();

        if (!$room->hasUser($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this room'
            ], 400);
        }

        $room->users()->detach($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Successfully left the room'
        ]);
    }

    /**
     * Get room members.
     */
    public function members(string $id): JsonResponse
    {
        $room = Room::with('users')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $room->users
        ]);
    }
}
