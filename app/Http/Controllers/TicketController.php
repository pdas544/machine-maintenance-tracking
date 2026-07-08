<?php
namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    // 1. Blade Server-Side Fetch (No JS)
    public function index()
    {
        $user = Auth::user();

        $query = Ticket::with(['machine.linesOrGroup', 'raiser', 'mechanic']);


        if (in_array($user->role_id, [1, 2])) {
            $tickets = $query->where('raised_by', $user->id)->latest('updated_at')->get();
            return view('dashboards.operator', compact('tickets'));
        }


        elseif ($user->role_id === 3) {
            $tickets = $query->where('assigned_mechanic_id', $user->id)->latest('updated_at')->get();
            return view('dashboards.mechanic', compact('tickets'));
        }

        // Fallback for other roles
        $tickets = $query->latest('updated_at')->get();
        return view('dashboard', compact('tickets'));
    }

    // 2. Raise Ticket via Standard Form Post
    public function store(Request $request)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'issue_description' => 'required|string'
        ]);

        $machine = Machine::with('linesOrGroup.segment.mechanics')->findOrFail($request->machine_id);
        $mechanic = $machine->linesOrGroup->segment->mechanics->first();

        Ticket::create([
            'machine_id' => $request->machine_id,
            'raised_by' => Auth::id(),
            'assigned_mechanic_id' => $mechanic ? $mechanic->id : null,
            'issue_description' => $request->issue_description,
        ]);

        // Redirect back to dashboard instead of returning JSON
        return redirect()->route('dashboard')->with('success', 'Ticket raised successfully!');
    }

    // 3. Update Status via Standard Form Patch
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:in_progress,completed,unfixable_escalated',
            'mechanic_remarks' => 'nullable|string'
        ]);

        $updateData = ['status' => $request->status];

        if ($request->mechanic_remarks) {
            $updateData['mechanic_remarks'] = $request->mechanic_remarks;
        }

        if ($request->status === 'in_progress' && !$ticket->acknowledged_at) {
            $updateData['acknowledged_at'] = now();
        } elseif ($request->status === 'completed') {
            $updateData['resolved_at'] = now();
        }

        $ticket->update($updateData);

        return redirect()->route('dashboard')->with('success', 'Job status updated!');
    }


    public function print(Ticket $ticket){
        // Ensure relationships are loaded for the print view
        $ticket->load(['machine.linesOrGroup', 'raiser']);
        return view('print-job-card', compact('ticket'));
    }
}


