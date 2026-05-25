<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionFile extends Model
{
    protected $fillable = [
        'submission_id',
        'path',
        'original_name',
        'size',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }
}