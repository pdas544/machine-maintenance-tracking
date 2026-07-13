<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name'])]
class Role extends Model
{

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
