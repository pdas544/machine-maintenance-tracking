<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    protected $fillable = ['line_or_group_id', 'machine_code', 'status'];

    public function linesOrGroup() {
        return $this->belongsTo(LinesOrGroup::class, 'line_or_group_id');
    }
}
