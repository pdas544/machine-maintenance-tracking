<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\Machine;
use App\Models\User;
use App\Enums\Role;
use App\Models\TicketEscalation;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ManualEscalationNotification;
use App\Notifications\TicketAssigned;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function storeTicket(array $data, int $userId): Ticket
    {
        return DB::transaction(function () use ($data, $userId) {
            $machine = Machine::with('linesOrGroup.segment.mechanics')->findOrFail($data['machine_id']);
            $mechanic = $machine->linesOrGroup->segment->mechanics->first();

            $assignedMechanicId = $mechanic?->id ?? throw new \RuntimeException('No mechanic assigned to this segment');

            $ticket = Ticket::create([
                'machine_id' => $data['machine_id'],
                'raised_by' => $userId,
                'assigned_mechanic_id' => $assignedMechanicId,
                'issue_description' => $data['issue_description'],
                'raised_at' => now(),
            ]);

            $ticket->load(['machine.linesOrGroup', 'raiser', 'mechanic']);

            $mechanicModel = User::find($assignedMechanicId);
            if ($mechanicModel) {
                $notification = new TicketAssigned($ticket);
                $notification->channels = ['database'];
                $mechanicModel->notify($notification);
            }

            return $ticket;
        });
    }

    public function updateStatus(Ticket $ticket, array $data, User $user): Ticket
    {
        $updateData = ['status' => $data['status']];

        if (!empty($data['mechanic_remarks'])) {
            $updateData['mechanic_remarks'] = $data['mechanic_remarks'];
        }

        if ($data['status'] === 'in_progress' && !$ticket->acknowledged_at) {
            $updateData['acknowledged_at'] = now();
        } elseif ($data['status'] === 'completed') {
            $updateData['resolved_at'] = now();
        } elseif ($data['status'] === 'unfixable_escalated') {
            $newLevel = $ticket->escalation_level + 1;

            $updateData['escalation_level'] = $newLevel;
            $updateData['escalated_at'] = now();
            $updateData['escalated_from_user_id'] = $user->id;
            $updateData['escalation_reason'] = 'Manual escalation';

            TicketEscalation::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'escalation_level' => $newLevel,
                'reason' => 'Manual escalation',
                'remarks' => $data['mechanic_remarks']
            ]);

            $targetRoleId = Role::getTargetRoleForEscalation($newLevel);
            $targetUsers = User::where('role_id', $targetRoleId)->get();

            if ($targetUsers->isNotEmpty()) {
                Notification::send($targetUsers, new ManualEscalationNotification($ticket));
            }
        }

        $ticket->update($updateData);
        $ticket->refresh();

        return $ticket;
    }

    public function getTicketsForUser(User $user): LengthAwarePaginator
    {
        $query = Ticket::with(['machine.linesOrGroup', 'raiser', 'mechanic', 'latestEscalation.user.reportingOfficer']);

        if (in_array($user->role_id, [Role::Operator->value, Role::LineLeader->value])) {
            return $query->where('raised_by', $user->id)
                ->latest('updated_at')
                ->paginate(20);
        }

        if ($user->role_id === Role::Mechanic->value) {
            return $query->where('assigned_mechanic_id', $user->id)
                ->latest('updated_at')
                ->paginate(20);
        }

        if ($user->role_id === Role::FloorIncharge->value) {
            return $query->where('status', '!=', 'completed')
                ->latest('updated_at')
                ->paginate(20);
        }

        return $query->latest('updated_at')->paginate(20);
    }

    public function getRelevantNotificationsForUser(User $user)
    {
        return $user->notifications()->latest()->get()->filter(function ($notification) use ($user) {
            $ticket = Ticket::find($notification->data['ticket_id'] ?? null);

            return $ticket && $ticket->status !== 'completed' && $ticket->escalation_level >= 1;
        });
    }
}
