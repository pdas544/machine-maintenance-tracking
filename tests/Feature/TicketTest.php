<?php

namespace Tests\Feature;

use App\Models\Segment;
use App\Models\LinesOrGroup;
use App\Models\Machine;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    private function seedRequiredData(): void
    {
        // Roles are seeded globally via TestCase::$seed = true (DatabaseSeeder)
        // No manual role insertion needed here.
    }

    private function createOperator(): User
    {
        return User::factory()->create(['role_id' => 1]);
    }

    private function createMechanic(): User
    {
        return User::factory()->create(['role_id' => 3]);
    }

    private function createSegmentWithMachineAndMechanic(): array
    {
        $segment = Segment::create(['name' => 'test_segment_' . uniqid()]);
        $mechanic = $this->createMechanic();
        \DB::table('segment_mechanics')->insert([
            'user_id' => $mechanic->id,
            'segment_id' => $segment->id,
        ]);
        $line = LinesOrGroup::create(['segment_id' => $segment->id, 'name' => 'TestLine_' . uniqid()]);
        $machine = Machine::create(['line_or_group_id' => $line->id, 'machine_code' => 'TEST-MAC-' . uniqid()]);

        return [$segment, $mechanic, $machine];
    }

    public function test_operator_can_raise_ticket(): void
    {
        $this->seedRequiredData();
        $operator = $this->createOperator();
        [, , $machine] = $this->createSegmentWithMachineAndMechanic();

        $response = $this->actingAs($operator)
            ->post(route('tickets.store'), [
                'machine_id' => $machine->id,
                'issue_description' => 'Machine stopped working',
            ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('tickets', [
            'raised_by' => $operator->id,
            'machine_id' => $machine->id,
            'issue_description' => 'Machine stopped working',
            'status' => 'pending',
        ]);
    }

    public function test_mechanic_assigned_correctly(): void
    {
        $this->seedRequiredData();
        $operator = $this->createOperator();
        [$segment, $mechanic, $machine] = $this->createSegmentWithMachineAndMechanic();

        $response = $this->actingAs($operator)
            ->post(route('tickets.store'), [
                'machine_id' => $machine->id,
                'issue_description' => 'Belt broken',
            ]);

        $response->assertRedirect();
        $ticket = Ticket::where('machine_id', $machine->id)->first();
        $this->assertNotNull($ticket);
        $this->assertEquals($mechanic->id, $ticket->assigned_mechanic_id);
    }

    public function test_mechanic_can_update_status(): void
    {
        $this->seedRequiredData();
        $operator = $this->createOperator();
        [, $mechanic, $machine] = $this->createSegmentWithMachineAndMechanic();

        $ticket = Ticket::create([
            'machine_id' => $machine->id,
            'raised_by' => $operator->id,
            'assigned_mechanic_id' => $mechanic->id,
            'issue_description' => 'Test issue',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($mechanic)
            ->patch("/tickets/{$ticket->id}/status", [
                'status' => 'in_progress',
                'mechanic_remarks' => 'Looking into it',
            ]);

        $response->assertRedirect(route('dashboard'));
        $ticket->refresh();
        $this->assertEquals('in_progress', $ticket->status);
        $this->assertNotNull($ticket->acknowledged_at);
        $this->assertEquals('Looking into it', $ticket->mechanic_remarks);
    }

    public function test_operator_cannot_update_status(): void
    {
        $this->seedRequiredData();
        $operator = $this->createOperator();
        $mechanic = $this->createMechanic();
        [, , $machine] = $this->createSegmentWithMachineAndMechanic();

        $ticket = Ticket::create([
            'machine_id' => $machine->id,
            'raised_by' => $operator->id,
            'assigned_mechanic_id' => $mechanic->id,
            'issue_description' => 'Test issue',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($operator)
            ->patch("/tickets/{$ticket->id}/status", [
                'status' => 'in_progress',
            ]);

        $response->assertForbidden();
        $ticket->refresh();
        $this->assertEquals('pending', $ticket->status);
    }

    public function test_unauthenticated_user_cannot_raise_ticket(): void
    {
        $this->seedRequiredData();
        [, , $machine] = $this->createSegmentWithMachineAndMechanic();

        $response = $this->post(route('tickets.store'), [
            'machine_id' => $machine->id,
            'issue_description' => 'Unauthorized',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_ticket_list_is_paginated(): void
    {
        $this->seedRequiredData();
        $operator = $this->createOperator();
        [, , $machine] = $this->createSegmentWithMachineAndMechanic();

        for ($i = 0; $i < 25; $i++) {
            Ticket::create([
                'machine_id' => $machine->id,
                'raised_by' => $operator->id,
                'issue_description' => "Issue #{$i}",
                'status' => 'pending',
            ]);
        }

        $response = $this->actingAs($operator)->get(route('dashboard'));
        $response->assertOk();
        $response->assertViewHas('tickets');
        $this->assertTrue($response->viewData('tickets')->hasPages());
    }

    public function test_ticket_status_enum_values_are_valid(): void
    {
        $this->seedRequiredData();
        $operator = $this->createOperator();
        $mechanic = $this->createMechanic();
        [, , $machine] = $this->createSegmentWithMachineAndMechanic();

        $ticket = Ticket::create([
            'machine_id' => $machine->id,
            'raised_by' => $operator->id,
            'assigned_mechanic_id' => $mechanic->id,
            'issue_description' => 'Test issue',
            'status' => 'pending',
        ]);

        // Invalid status should be rejected by UpdateTicketStatusRequest
        $response = $this->actingAs($mechanic)
            ->patch("/tickets/{$ticket->id}/status", [
                'status' => 'invalid_status',
            ]);

        $response->assertSessionHasErrors('status');
        $ticket->refresh();
        $this->assertEquals('pending', $ticket->status);

        // Valid status should be accepted
        $response2 = $this->actingAs($mechanic)
            ->patch("/tickets/{$ticket->id}/status", [
                'status' => 'in_progress',
            ]);

        $response2->assertRedirect();
        $ticket->refresh();
        $this->assertEquals('in_progress', $ticket->status);
    }
}