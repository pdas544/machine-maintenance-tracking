<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinesOrGroup extends Model
{
    protected $fillable = ['segment_id', 'name'];

    public function segment() {
        return $this->belongsTo(Segment::class);
    }
}
