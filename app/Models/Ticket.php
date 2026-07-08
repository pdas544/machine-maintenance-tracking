<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'machine_id',
        'raised_by',
        'assigned_mechanic_id',
        'status',
        'issue_description',
        'mechanic_remarks',
        'acknowledged_at',
        'resolved_at'
    ];

    // This is the missing relationship that Laravel was looking for
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    // Defining these as well for future-proofing your views
    public function raiser()
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    public function mechanic()
    {
        return $this->belongsTo(User::class, 'assigned_mechanic_id');
    }
}
