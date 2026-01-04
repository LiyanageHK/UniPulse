<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
        'admin_id',
        'avatar',
        'last_message',
        'last_message_at'
    ];

    protected $casts = [
        'last_message_at' => 'datetime'
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members')
                    ->withTimestamps()
                    ->withPivot('joined_at');
    }

    public function requests()
    {
        return $this->hasMany(GroupRequest::class);
    }

    public function pendingRequests()
    {
        return $this->hasMany(GroupRequest::class)->where('status', 'pending');
    }

    public function isMember($userId)
    {
        return $this->members()->where('user_id', $userId)->exists();
    }

    public function isAdmin($userId)
    {
        return $this->admin_id == $userId;
    }
}
