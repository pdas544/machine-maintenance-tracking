<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use App\Enums\Role;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role_id, [
            Role::Operator->value,
            Role::LineLeader->value,
            Role::Mechanic->value,
            Role::FloorIncharge->value,
            Role::MaintenanceHead->value,
            Role::MaintenanceManager->value,
        ]);
    }

    public function create(User $user): bool
    {
        return in_array($user->role_id, [
            Role::Operator->value,
            Role::LineLeader->value,
        ]);
    }

    public function update(User $user, Ticket $ticket): bool
    {
        // Mechanics can update if assigned to the ticket
        if ($user->role_id === Role::Mechanic->value) {
            return $ticket->assigned_mechanic_id === $user->id;
        }

        // Floor Incharges can always update escalated tickets
        if ($user->role_id === Role::FloorIncharge->value) {
            return true;
        }

        // Check if ticket is escalated
        if ($ticket->escalation_level > 0) {
            // User must have a role higher or equal to the escalation level requirements
            // escalation_level 1 requires role >= 4 (FloorIncharge)
            // escalation_level 2 requires role >= 5 (MaintenanceHead)
            // escalation_level 3 requires role >= 6 (MaintenanceManager)
            // escalation_level 4 requires role >= 6 (MaintenanceManager)
            $requiredRole = $ticket->escalation_level + 3;
            if ($user->role_id < $requiredRole) {
                return false;
            }
        }

        // Operators cannot update tickets
        if ($user->role_id === Role::Operator->value) {
            return false;
        }

        // Other roles (LineLeader, FloorIncharge, etc.) can update
        return true;
    }
}
