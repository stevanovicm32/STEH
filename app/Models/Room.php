<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_private',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_private' => 'boolean',
    ];

    /**
     * Get the users that belong to this room.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_room')
                    ->withPivot('joined_at', 'is_admin')
                    ->withTimestamps();
    }

    /**
     * Get the user who created this room.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the messages in this room.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the latest messages in this room.
     */
    public function latestMessages($limit = 50)
    {
        return $this->messages()->latest()->limit($limit)->get();
    }

    /**
     * Check if a user is a member of this room.
     */
    public function hasUser($userId)
    {
        return $this->users()->where('user_id', $userId)->exists();
    }

    /**
     * Check if a user is an admin of this room.
     */
    public function isUserAdmin($userId)
    {
        return $this->users()->where('user_id', $userId)->where('is_admin', true)->exists();
    }
}
