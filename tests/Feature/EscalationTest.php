<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Machine;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EscalationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mechanic_can_update_escalated_ticket()
    {
        $mechanic = User::factory()->create(['role_id' => Role::Mechanic->value]);
        $segment = \App\Models\Segment::create(['name' => 'Segment 1']);
        $line = \App\Models\LinesOrGroup::create(['name' => 'Line 1', 'segment_id' => $segment->id]);
        $machine = \App\Models\Machine::create(['machine_code' => 'M1', 'line_or_group_id' => $line->id]);

        $ticket = Ticket::create([
            'machine_id' => $machine->id,
            'raised_by' => 1,
            'assigned_mechanic_id' => $mechanic->id,
            'issue_description' => 'Test issue',
            'raised_at' => now(),
            'escalation_level' => 1,
            'status' => 'pending'
        ]);

        $this->actingAs($mechanic);
        $response = $this->patchJson(route('tickets.updateStatus', $ticket), [
            'status' => 'in_progress',
            'mechanic_remarks' => 'Doing it'
        ]);

        $response->assertStatus(302);
    }

    public function test_incharge_can_update_escalated_ticket()
    {
        $incharge = User::factory()->create(['role_id' => Role::FloorIncharge->value]);
        $segment = \App\Models\Segment::create(['name' => 'Segment 2']);
        $line = \App\Models\LinesOrGroup::create(['name' => 'Line 2', 'segment_id' => $segment->id]);
        $machine = \App\Models\Machine::create(['machine_code' => 'M2', 'line_or_group_id' => $line->id]);

        $ticket = Ticket::create([
            'machine_id' => $machine->id,
            'raised_by' => 1,
            'assigned_mechanic_id' => 1,
            'issue_description' => 'Test issue',
            'raised_at' => now(),
            'escalation_level' => 1,
            'status' => 'pending'
        ]);

        $this->actingAs($incharge);
        $response = $this->patchJson(route('tickets.updateStatus', $ticket), [
            'status' => 'in_progress',
        ]);

        $response->assertStatus(302); // Redirect after successful update
    }
}
