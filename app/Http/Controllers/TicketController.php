<?php
namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Machine;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketStatusRequest;
use App\Services\TicketService;
use Illuminate\Support\Facades\Auth;
use App\Enums\Role;

class TicketController extends Controller
{
    public function __construct(
        protected TicketService $ticketService
    ) {}

    public function index()
    {
        $user = Auth::user();
        $tickets = $this->ticketService->getTicketsForUser($user);
        $machines = Machine::orderBy('machine_code')->get();

        if ($user->role_id === Role::Operator->value || $user->role_id === Role::LineLeader->value) {
            return view('dashboards.operator', compact('tickets', 'machines'));
        } elseif ($user->role_id === Role::Mechanic->value) {
            return view('dashboards.mechanic', compact('tickets', 'machines'));
        } elseif ($user->role_id === Role::FloorIncharge->value) {
            $relevantNotifications = $this->ticketService->getRelevantNotificationsForUser($user);
            return view('dashboards.incharge', compact('tickets', 'machines', 'relevantNotifications'));
        }

        return view('dashboard', compact('tickets', 'machines'));
    }

    public function store(StoreTicketRequest $request)
    {
        $this->authorize('create', Ticket::class);

        $this->ticketService->storeTicket(
            $request->validated(),
            Auth::id()
        );

        return redirect()->route('dashboard')->with('success', 'Ticket raised successfully!');
    }

    public function updateStatus(UpdateTicketStatusRequest $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $this->ticketService->updateStatus(
            $ticket,
            $request->validated(),
            Auth::user()
        );

        return redirect()->route('dashboard')->with('success', 'Job status updated!');
    }

    public function print(Ticket $ticket)
    {
        $ticket->load(['machine.linesOrGroup', 'raiser']);
        return view('print-job-card', compact('ticket'));
    }

    public function markNotificationAsRead($id)
    {
        Auth::user()->notifications()->where('id', $id)->first()?->markAsRead();
        return redirect()->back()->with('success', 'Notification closed!');
    }
}


