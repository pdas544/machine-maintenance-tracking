<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketEscalation extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'escalation_level',
        'reason',
        'remarks',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The user that this escalation was directed to.
     * This relationship is optional – it will be null until we explicitly set it.
     */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_to_user_id');
    }
}
