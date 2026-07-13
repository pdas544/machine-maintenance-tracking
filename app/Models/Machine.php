<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['line_or_group_id', 'machine_code', 'status'])]
class Machine extends Model
{

    public function linesOrGroup() {
        return $this->belongsTo(LinesOrGroup::class, 'line_or_group_id');
    }
}
