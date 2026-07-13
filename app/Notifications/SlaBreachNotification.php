<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlaBreachNotification extends Notification
{
    use Queueable;

    public Ticket $ticket;
    public string $breachThreshold;

    public function __construct(Ticket $ticket, $breachThreshold)
    {
        $this->ticket = $ticket;
        $this->breachThreshold = $breachThreshold;
    }

    // Route to both email and database channels
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject("SLA Breach Alert: Job #{$this->ticket->id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Job #{$this->ticket->id} has breached the {$this->breachThreshold} SLA.")
            ->line("Machine ID: {$this->ticket->machine_id}")
            ->line("Issue: {$this->ticket->issue_description}")
            ->action('View Dashboard', url('/dashboard'))
            ->line('Please take immediate action.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'message' => "Job #{$this->ticket->id} breached the {$this->breachThreshold} SLA.",
            'machine_id' => $this->ticket->machine_id
        ];
    }
}
