<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeerRating extends Model
{
    protected $fillable = ['from_id', 'to_id', 'rating'];

    public function from() {
        return $this->belongsTo(User::class, 'from_id');
    }

    public function to() {
        return $this->belongsTo(User::class, 'to_id');
    }
}
