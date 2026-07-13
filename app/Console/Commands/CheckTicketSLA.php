<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketEscalation;
use App\Notifications\SlaBreachNotification;
use App\Enums\Role as RoleEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

class CheckTicketSLA extends Command
{
    // The terminal signature to run this command manually
    protected $signature = 'tickets:check-sla';

    // The console description
    protected $description = 'Sweep active tickets and trigger SLA breach notifications';

    public function handle()
    {
        $this->info('Starting SLA check...');

        // Pre-fetch ALL recipients once to avoid N+1 queries
//        $recipientsByLevel = [
//            1 => User::where('role_id', RoleEnum::FloorIncharge->value)->get(),
//            2 => User::where('role_id', RoleEnum::MaintenanceHead->value)->get(),
//            3 => User::where('role_id', RoleEnum::MaintenanceManager->value)->get(),
//            4 => User::where('role_id', RoleEnum::MaintenanceManager->value)->get(),
//        ];

        $now = Carbon::now();

        // Use chunk to avoid memory issues with large result sets
        Ticket::where('status', '!=', 'completed')
            ->chunk(200, function ($tickets) use ($now) {
                foreach ($tickets as $ticket) {
                    $raisedAt = Carbon::parse($ticket->raised_at);
                    $minutesElapsed = $raisedAt->diffInMinutes($now);

                    // Level 4: 48 Hours - Only notify MaintenanceManager
                    if ($minutesElapsed >= config('sla.thresholds.4') && $ticket->escalation_level < 4) {
                        $targetRoleId = RoleEnum::getTargetRoleForEscalation(4);
                        $incharge = User::where('role_id', $targetRoleId)->get();
                        Notification::send($incharge, new SlaBreachNotification($ticket, '48 Hour CRITICAL'));
                        $ticket->update([
                            'escalation_level' => 4,
                            'escalated_at' => now(),
                            'escalated_from_user_id' => $ticket->assigned_mechanic_id,
                            'escalation_reason' => 'SLA 48 Hour CRITICAL'
                        ]);
                        TicketEscalation::create([
                            'ticket_id' => $ticket->id,
                            'user_id' => $ticket->assigned_mechanic_id,
                            'escalation_level' => 4,
                            'reason' => 'SLA 48 Hour CRITICAL',
                            'remarks' => 'SLA elapsed'
                        ]);
                        $this->info("Ticket {$ticket->id} escalated to CRITICAL Level 4.");
                    }
                    // Level 3: 24 Hours - Only notify MaintenanceManager
                    elseif ($minutesElapsed >= config('sla.thresholds.3') && $ticket->escalation_level < 3) {
                        $targetRoleId = RoleEnum::getTargetRoleForEscalation(3);
                        $incharge = User::where('role_id', $targetRoleId)->get();
                        Notification::send($incharge, new SlaBreachNotification($ticket, '24 Hour'));
                        $ticket->update([
                            'escalation_level' => 3,
                            'escalated_at' => now(),
                            'escalated_from_user_id' => $ticket->assigned_mechanic_id,
                            'escalation_reason' => 'SLA 24 Hour'
                        ]);
                        TicketEscalation::create([
                            'ticket_id' => $ticket->id,
                            'user_id' => $ticket->assigned_mechanic_id,
                            'escalation_level' => 3,
                            'reason' => 'SLA 24 Hour',
                            'remarks' => 'SLA elapsed'
                        ]);
                        $this->info("Ticket {$ticket->id} escalated to Level 3.");
                    }
                    // Level 2: 1 Hour - Only notify MaintenanceHead
                    elseif ($minutesElapsed >= config('sla.thresholds.2') && $ticket->escalation_level < 2) {
                        $targetRoleId = RoleEnum::getTargetRoleForEscalation(2);
                        $incharge = User::where('role_id', $targetRoleId)->get();
                        Notification::send($incharge, new SlaBreachNotification($ticket, '1 Hour'));
                        $ticket->update([
                            'escalation_level' => 2,
                            'escalated_at' => now(),
                            'escalated_from_user_id' => $ticket->assigned_mechanic_id,
                            'escalation_reason' => 'SLA 1 Hour'
                        ]);
                        TicketEscalation::create([
                            'ticket_id' => $ticket->id,
                            'user_id' => $ticket->assigned_mechanic_id,
                            'escalation_level' => 2,
                            'reason' => 'SLA 1 Hour',
                            'remarks' => 'SLA elapsed'
                        ]);
                        $this->info("Ticket {$ticket->id} escalated to Level 2.");
                    }
                    // Level 1: 30 Minutes - Notify Floor Incharge
                    elseif ($minutesElapsed >= config('sla.thresholds.1') && $ticket->escalation_level < 1) {
                        $targetRoleId = RoleEnum::getTargetRoleForEscalation(1);
                        $incharge = User::where('role_id', $targetRoleId)->get();
                        Notification::send($incharge, new SlaBreachNotification($ticket, '30 Minute'));
                        $ticket->update([
                            'escalation_level' => 1,
                            'escalated_at' => now(),
                            'escalated_from_user_id' => $ticket->assigned_mechanic_id,
                            'escalation_reason' => 'SLA 30 Minute'
                        ]);
                        TicketEscalation::create([
                            'ticket_id' => $ticket->id,
                            'user_id' => $ticket->assigned_mechanic_id,
                            'escalation_level' => 1,
                            'reason' => 'SLA 30 Minute',
                            'remarks' => 'SLA elapsed'
                        ]);
                        $this->info("Ticket {$ticket->id} escalated to Level 1.");
                    }
                }
            });

        $this->info('SLA check completed.');
    }
}
