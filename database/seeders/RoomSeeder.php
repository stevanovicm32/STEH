<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $moderator = User::where('email', 'moderator@example.com')->first();
        $john = User::where('email', 'john@example.com')->first();
        $jane = User::where('email', 'jane@example.com')->first();
        $bob = User::where('email', 'bob@example.com')->first();

        // Create public rooms
        $generalRoom = Room::create([
            'name' => 'General Chat',
            'description' => 'General discussion room for all users',
            'is_private' => false,
            'created_by' => $admin->id,
        ]);

        $techRoom = Room::create([
            'name' => 'Tech Talk',
            'description' => 'Discussion about technology and programming',
            'is_private' => false,
            'created_by' => $moderator->id,
        ]);

        $musicRoom = Room::create([
            'name' => 'Music Lovers',
            'description' => 'Share your favorite music and discuss artists',
            'is_private' => false,
            'created_by' => $john->id,
        ]);

        // Create private rooms
        $privateRoom = Room::create([
            'name' => 'Private Discussion',
            'description' => 'Private room for invited members only',
            'is_private' => true,
            'created_by' => $admin->id,
        ]);

        // Add users to rooms
        $generalRoom->users()->attach([
            $admin->id => ['is_admin' => true, 'joined_at' => now()],
            $moderator->id => ['is_admin' => false, 'joined_at' => now()],
            $john->id => ['is_admin' => false, 'joined_at' => now()],
            $jane->id => ['is_admin' => false, 'joined_at' => now()],
            $bob->id => ['is_admin' => false, 'joined_at' => now()],
        ]);

        $techRoom->users()->attach([
            $moderator->id => ['is_admin' => true, 'joined_at' => now()],
            $admin->id => ['is_admin' => false, 'joined_at' => now()],
            $john->id => ['is_admin' => false, 'joined_at' => now()],
            $jane->id => ['is_admin' => false, 'joined_at' => now()],
        ]);

        $musicRoom->users()->attach([
            $john->id => ['is_admin' => true, 'joined_at' => now()],
            $jane->id => ['is_admin' => false, 'joined_at' => now()],
            $bob->id => ['is_admin' => false, 'joined_at' => now()],
        ]);

        $privateRoom->users()->attach([
            $admin->id => ['is_admin' => true, 'joined_at' => now()],
            $moderator->id => ['is_admin' => false, 'joined_at' => now()],
        ]);

        // Create additional rooms using factory
        Room::factory(5)->create()->each(function ($room) {
            $users = User::inRandomOrder()->limit(rand(2, 5))->get();
            $room->users()->attach($users->pluck('id')->toArray(), [
                'joined_at' => now(),
                'is_admin' => false
            ]);
        });
    }
}
