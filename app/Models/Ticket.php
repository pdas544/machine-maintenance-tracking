<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'machine_id',
    'raised_by',
    'assigned_mechanic_id',
    'status',
    'issue_description',
    'mechanic_remarks',
    'acknowledged_at',
    'resolved_at',
    'raised_at',
    'escalation_level',
    'escalated_at',
    'escalated_from_user_id',
    'escalation_reason'
])]
class Ticket extends Model
{

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

    public function latestEscalation()
    {
        return $this->hasOne(TicketEscalation::class)->latestOfMany();
    }

    public function escalations()
    {
        return $this->hasMany(TicketEscalation::class);
    }
}
