<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'id', 'content', 'discussion_id', 'user_id', 'is_approved', 'is_private'
    ];

    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }
}
