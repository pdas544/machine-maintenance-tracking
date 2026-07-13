<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name'])]
class Segment extends Model
{

    // Finds the mechanic assigned to this entire segment
    public function mechanics() {
        return $this->belongsToMany(User::class, 'segment_mechanics');
    }
}
