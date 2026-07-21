<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Notifications\TaskDeadlineNotification;
use Carbon\Carbon;

class CheckTaskDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-task-deadlines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for tasks nearing their deadline and notify assignees';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        // Tasks due today or tomorrow that are not done
        $tasks = Task::whereIn('status', ['To Do', 'In Progress', 'Review'])
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>=', $today)
            ->whereDate('end_date', '<=', $tomorrow)
            ->get();

        $count = 0;
        foreach ($tasks as $task) {
            $daysLeft = $task->end_date->isSameDay($today) ? 0 : 1;
            
            // Notify Assignee
            if ($task->assignee) {
                $task->assignee->notify(new TaskDeadlineNotification($task, $daysLeft));
                $count++;
            }
        }

        $this->info("Checked task deadlines. Sent {$count} notifications.");
    }
}
