<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketAssigned extends Notification
{
    use Queueable;

    public array $channels = ['mail', 'database'];

    public function __construct(
        public Ticket $ticket
    ) {}

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'message' => 'A new maintenance ticket has been assigned to you: #' . $this->ticket->id,
            'machine' => $this->ticket->machine?->machine_code ?? 'N/A',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Ticket Assigned #' . $this->ticket->id)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new maintenance ticket has been assigned to you.')
            ->line('**Ticket ID:** #' . $this->ticket->id)
            ->line('**Machine:** ' . ($this->ticket->machine?->machine_code ?? 'N/A'))
            ->line('**Issue:** ' . $this->ticket->issue_description)
            ->line('**Status:** ' . strtoupper(str_replace('_', ' ', $this->ticket->status)))
            ->action('View Ticket', url('/dashboard'))
            ->line('Please acknowledge the ticket and start working on it.');
    }
}
