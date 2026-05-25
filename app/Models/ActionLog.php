<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    protected $fillable = ['user_id', 'action'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}