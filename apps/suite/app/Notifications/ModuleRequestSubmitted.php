<?php

namespace App\Notifications;

use App\Models\ModuleRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ModuleRequestSubmitted extends Notification
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
        $tenantName = $this->request->tenant->name;
        $typeLabel  = $this->request->readable_type;
        $status     = ucfirst($this->request->status);
        $recipient  = isset($notifiable->name) ? $notifiable->name : 'Team';

        return (new MailMessage)
            ->subject("Module request received: {$moduleName}")
            ->greeting("Hello {$recipient},")
            ->line("A {$typeLabel} request for {$moduleName} has been submitted for tenant {$tenantName}.")
            ->line("Request status: {$status}.")
            ->when($this->request->notes, fn (MailMessage $mail) => $mail->line("Notes: {$this->request->notes}"))
            ->line('You will be notified when the request is reviewed.')
            ->action('View Requests', url('/admin/module-requests'))
            ->line('Thank you for using Xquisite.');
    }

    public function toArray($notifiable)
    {
        return [
            'tenant_id' => $this->request->tenant_id,
            'module'    => $this->request->module,
            'status'    => $this->request->status,
            'type'      => $this->request->type,
        ];
    }
}
