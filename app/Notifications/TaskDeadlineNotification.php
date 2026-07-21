<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Task;

class TaskDeadlineNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $daysLeft;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, $daysLeft)
    {
        $this->task = $task;
        $this->daysLeft = $daysLeft;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $message = $this->daysLeft == 0 
            ? "Task '{$this->task->name}' is due TODAY!"
            : "Task '{$this->task->name}' is due in {$this->daysLeft} day(s).";

        return [
            'task_id' => $this->task->id,
            'task_code' => $this->task->task_code,
            'task_name' => $this->task->name,
            'message' => $message,
            'due_date' => $this->task->end_date->format('Y-m-d'),
        ];
    }
}
