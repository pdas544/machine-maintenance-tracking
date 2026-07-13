<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['type', 'filename', 'total_rows', 'processed_rows', 'status', 'created_by'])]
class ImportJob extends Model
{
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
