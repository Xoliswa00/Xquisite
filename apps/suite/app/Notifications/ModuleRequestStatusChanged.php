<?php

namespace App\Notifications;

use App\Models\ModuleRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ModuleRequestStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public ModuleRequest $request)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $moduleName = $this->request->module_name;
        $status     = ucfirst($this->request->status);
        $recipient  = isset($notifiable->name) ? $notifiable->name : 'Team';

        $mail = (new MailMessage)
            ->subject("Module request {$status}: {$moduleName}")
            ->greeting("Hello {$recipient},")
            ->line("Your {$this->request->readable_type} request for {$moduleName} has been {$status}.")
            ->when($this->request->review_notes, fn (MailMessage $mail) => $mail->line("Review notes: {$this->request->review_notes}"));

        if ($this->request->status === 'approved') {
            $mail->line('The module has been activated or updated according to your request.');
        } else {
            $mail->line('No changes were made. Please contact support if you need help.');
        }

        return $mail->line('Thank you for using Xquisite.');
    }

    public function toArray($notifiable)
    {
        return [
            'module' => $this->request->module,
            'status' => $this->request->status,
            'type'   => $this->request->type,
        ];
    }
}
