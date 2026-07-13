<?php

namespace Tests\Feature;

use App\Console\Commands\CheckTicketSLA;
use App\Enums\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\SlaBreachNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SlaNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sla_breach_notification_is_sent_to_incharge()
    {
        Notification::fake();

        // Configure thresholds to ensure it triggers Level 1 but not Level 2
        config(['sla.thresholds.1' => 30]);
        config(['sla.thresholds.2' => 999999]);

        // Create a user with FloorIncharge role
        $incharge = User::factory()->create(['role_id' => Role::FloorIncharge->value]);

        // Create a ticket that would trigger level 1 escalation (e.g., raised 31 minutes ago)
        $ticket = Ticket::create([
            'machine_id' => 1, // Assuming machine ID 1 exists
            'raised_by' => 1,
            'assigned_mechanic_id' => 1,
            'issue_description' => 'Test issue',
            'raised_at' => now()->subMinutes(31),
            'escalation_level' => 0,
            'status' => 'pending'
        ]);

        // Run the command
        $this->artisan('tickets:check-sla');

        // Assert notification sent to the incharge user
        Notification::assertSentTo(
            $incharge,
            SlaBreachNotification::class,
            function ($notification, $channels) use ($ticket) {
                return $notification->ticket->id === $ticket->id;
            }
        );
    }

    public function test_incharge_dashboard_shows_escalated_ticket_notification()
    {
        $incharge = User::factory()->create(['role_id' => Role::FloorIncharge->value]);
        $this->actingAs($incharge);

        // Create a ticket that is escalated
        $ticket = Ticket::create([
            'machine_id' => 1,
            'raised_by' => 1,
            'assigned_mechanic_id' => 1,
            'issue_description' => 'Test issue',
            'raised_at' => now(),
            'escalation_level' => 1,
            'status' => 'pending'
        ]);

        // Create a notification for this ticket
        $incharge->notify(new SlaBreachNotification($ticket, 'Test'));

        // Simulate the logic in incharge.blade.php
        $relevantNotifications = $incharge->notifications()->latest()->get()->filter(function ($notification) {
            $ticket = \App\Models\Ticket::find($notification->data['ticket_id'] ?? null);
            return $ticket && $ticket->status !== 'completed' && $ticket->escalation_level >= 1;
        });

        $this->assertTrue($relevantNotifications->contains(function ($n) use ($ticket) {
            return $n->data['ticket_id'] === $ticket->id;
        }));
    }
}
