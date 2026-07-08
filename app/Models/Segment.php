<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    protected $fillable = ['name'];

    // Finds the mechanic assigned to this entire segment
    public function mechanics() {
        return $this->belongsToMany(User::class, 'segment_mechanics');
    }
}
