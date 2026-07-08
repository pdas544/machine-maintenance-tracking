<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = ['machine_id', 'raised_by', 'assigned_mechanic_id', 'status', 'issue_description', 'mechanic_remarks', 'acknowledged_at', 'resolved_at'];

    protected static function booted()
    {
        static::creating(function ($ticket) {
            // Find the machine -> find its line/group -> find its segment
            $machine = Machine::with('linesOrGroup.segment.mechanics')->find($ticket->machine_id);

            if ($machine && $machine->linesOrGroup && $machine->linesOrGroup->segment) {
                // Get the first mechanic assigned to this segment ('parts' or 'assembly')
                $mechanic = $machine->linesOrGroup->segment->mechanics->first();
                if ($mechanic) {
                    $ticket->assigned_mechanic_id = $mechanic->id;
                }
            }
        });
    }
}
