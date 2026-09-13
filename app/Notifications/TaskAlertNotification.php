<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class TaskAlertNotification extends Notification
{
    public function __construct(
        public Task $task,
        public ?User $actor = null,
        public string $body = '',
    ) {
        $this->task->loadMissing(['creator']);
    }

    abstract public function alertType(): string;

    abstract public function alertTitle(): string;

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $company = (string) setting('company_name', config('app.name'));

        return (new MailMessage)
            ->subject($this->alertTitle().' · '.$company)
            ->view('mail.task-alert', [
                'title' => $this->alertTitle(),
                'alertMessage' => $this->messageFor($notifiable),
                'taskTitle' => $this->task->title,
                'companyName' => $company,
                'brandColor' => brand_color(),
                'actionUrl' => route('tasks.show', $this->task),
                'recipientName' => $notifiable->name ?? 'there',
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->alertType(),
            'title' => $this->alertTitle(),
            'message' => $this->messageFor($notifiable),
            'task_id' => $this->task->id,
            'url' => route('tasks.show', $this->task),
            'actor_name' => $this->actor?->name,
            'actor_avatar' => $this->actor?->avatar_url,
        ];
    }

    protected function messageFor(object $notifiable): string
    {
        if ($this->body !== '') {
            return $this->body;
        }

        return 'There is an update on "'.$this->task->title.'".';
    }
}
