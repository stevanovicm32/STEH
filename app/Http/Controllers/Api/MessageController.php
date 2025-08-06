<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Message::with(['user', 'room']);

        // Filter by room
        if ($request->has('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by system messages
        if ($request->has('is_system_message')) {
            $query->where('is_system_message', $request->boolean('is_system_message'));
        }

        // Search in content
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('content', 'like', "%{$search}%");
        }

        $messages = $query->latest()->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $messages
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
            'content' => 'required|string|max:1000',
            'room_id' => 'required|exists:rooms,id',
            'is_system_message' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $room = Room::findOrFail($request->room_id);
        $user = $request->user();

        // Check if user is member of the room
        if (!$room->hasUser($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this room'
            ], 403);
        }

        $message = Message::create([
            'content' => $request->content,
            'user_id' => $user->id,
            'room_id' => $request->room_id,
            'is_system_message' => $request->boolean('is_system_message', false),
        ]);

        $message->load(['user', 'room']);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $message = Message::with(['user', 'room'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $message
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
        $message = Message::findOrFail($id);
        $user = $request->user();

        // Check if user is the message author or room admin
        if ($message->user_id !== $user->id && !$message->room->isUserAdmin($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only message author or room admin can edit messages.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $message->update([
            'content' => $request->content
        ]);

        $message->load(['user', 'room']);

        return response()->json([
            'success' => true,
            'message' => 'Message updated successfully',
            'data' => $message
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $message = Message::findOrFail($id);
        $user = $request->user();

        // Check if user is the message author or room admin
        if ($message->user_id !== $user->id && !$message->room->isUserAdmin($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only message author or room admin can delete messages.'
            ], 403);
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully'
        ]);
    }

    /**
     * Get messages for a specific room.
     */
    public function roomMessages(Request $request, string $roomId): JsonResponse
    {
        $room = Room::findOrFail($roomId);
        $user = $request->user();

        // Check if user is member of the room
        if (!$room->hasUser($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this room'
            ], 403);
        }

        $query = $room->messages()->with('user');

        // Filter by system messages
        if ($request->has('is_system_message')) {
            $query->where('is_system_message', $request->boolean('is_system_message'));
        }

        // Search in content
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('content', 'like', "%{$search}%");
        }

        $messages = $query->latest()->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Send system message to a room.
     */
    public function sendSystemMessage(Request $request, string $roomId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $room = Room::findOrFail($roomId);
        $user = $request->user();

        // Check if user is admin of the room
        if (!$room->isUserAdmin($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only room admins can send system messages.'
            ], 403);
        }

        $message = Message::create([
            'content' => $request->content,
            'user_id' => $user->id,
            'room_id' => $roomId,
            'is_system_message' => true,
        ]);

        $message->load(['user', 'room']);

        return response()->json([
            'success' => true,
            'message' => 'System message sent successfully',
            'data' => $message
        ], 201);
    }
}
