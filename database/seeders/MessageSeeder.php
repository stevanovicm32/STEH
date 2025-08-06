<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = Room::all();
        $users = User::all();

        // Create sample messages for each room
        foreach ($rooms as $room) {
            $roomUsers = $room->users;
            
            // Create welcome message
            Message::create([
                'content' => "Welcome to {$room->name}! Feel free to start chatting.",
                'user_id' => $room->creator->id,
                'room_id' => $room->id,
                'is_system_message' => true,
            ]);

            // Create some regular messages
            $messages = [
                'Hello everyone! 👋',
                'How is everyone doing today?',
                'This is a great room!',
                'Anyone want to chat?',
                'Thanks for the warm welcome!',
                'What topics are we discussing today?',
                'I\'m new here, nice to meet you all!',
                'This chat is really active!',
                'Anyone have any interesting stories to share?',
                'Great discussion going on here!',
            ];

            foreach ($messages as $index => $content) {
                $user = $roomUsers->random();
                Message::create([
                    'content' => $content,
                    'user_id' => $user->id,
                    'room_id' => $room->id,
                    'is_system_message' => false,
                    'created_at' => now()->subMinutes(rand(1, 60)),
                    'updated_at' => now()->subMinutes(rand(1, 60)),
                ]);
            }
        }

        // Create additional random messages
        Message::factory(50)->create();
    }
}
