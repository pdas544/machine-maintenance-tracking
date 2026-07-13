<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['segment_id', 'name'])]
class LinesOrGroup extends Model
{

    public function segment() {
        return $this->belongsTo(Segment::class);
    }
}
