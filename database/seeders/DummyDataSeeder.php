<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskComment;
use App\Models\TaskDependency;
use App\Models\TaskProgress;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    use WithoutModelEvents;

    private $faker;

    public function run(): void
    {
        $this->faker = \Faker\Factory::create();

        $users = $this->seedUsers();
        $projects = $this->seedProjects($users);

        foreach ($projects as $project) {
            $tasks = $this->seedTasks($project, $users);
            $this->seedTaskProgress($tasks, $project);
            $this->seedTaskComments($tasks, $users);
            $this->seedTaskDependencies($tasks);
            $this->seedTaskActivities($tasks, $users);
            $this->seedTimesheets($tasks, $users);
        }

        $this->seedNotifications($users);

        $this->command->info('Dummy data seeded: '.count($users).' users, '.count($projects).' projects.');
    }

    private function seedUsers()
    {
        $departments = ['Engineering', 'Design', 'QA', 'Product', 'Operations'];
        $names = [
            ['Siti Rahma', 'siti.rahma'],
            ['Budi Santoso', 'budi.santoso'],
            ['Andi Wijaya', 'andi.wijaya'],
            ['Dewi Lestari', 'dewi.lestari'],
            ['Rian Pratama', 'rian.pratama'],
            ['Nadia Putri', 'nadia.putri'],
            ['Fajar Nugroho', 'fajar.nugroho'],
            ['Maya Kusuma', 'maya.kusuma'],
        ];

        $users = collect();

        foreach ($names as $i => [$name, $slug]) {
            $user = User::firstOrCreate(
                ['email' => $slug.'@pmtracker.test'],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'employee_id' => 'EMP-'.str_pad($i + 2, 3, '0', STR_PAD_LEFT),
                    'department' => $departments[$i % count($departments)],
                    'status' => 'Active',
                ]
            );

            if (! $user->roles()->exists()) {
                $user->assignRole($i < 2 ? 'project_manager' : 'team_member');
            }

            $users->push($user);
        }

        $admin = User::where('email', 'admin@pmtracker.test')->first();
        if ($admin) {
            $users->push($admin);
        }

        return $users;
    }

    private function seedProjects($users)
    {
        $pms = $users->filter(fn ($u) => $u->hasRole('project_manager'))->values();
        if ($pms->isEmpty()) {
            $pms = $users;
        }

        $defs = [
            ['code' => 'PRJ-001', 'name' => 'ERP Reconciliation Module', 'status' => 'On Going', 'daysAgoStart' => 60, 'durationDays' => 120],
            ['code' => 'PRJ-002', 'name' => 'Mobile Attendance App', 'status' => 'On Going', 'daysAgoStart' => 30, 'durationDays' => 90],
            ['code' => 'PRJ-003', 'name' => 'Internal Helpdesk Portal', 'status' => 'Planning', 'daysAgoStart' => -10, 'durationDays' => 75],
            ['code' => 'PRJ-004', 'name' => 'Legacy Data Migration', 'status' => 'Completed', 'daysAgoStart' => 150, 'durationDays' => 100],
        ];

        $projects = collect();

        foreach ($defs as $i => $def) {
            $start = Carbon::now()->subDays($def['daysAgoStart']);
            $end = $start->copy()->addDays($def['durationDays']);

            $project = Project::firstOrCreate(
                ['code' => $def['code']],
                [
                    'name' => $def['name'],
                    'description' => $this->faker->paragraph(3),
                    'pm_id' => $pms[$i % $pms->count()]->id,
                    'planned_start_date' => $start->toDateString(),
                    'planned_end_date' => $end->toDateString(),
                    'actual_start_date' => $def['status'] !== 'Planning' ? $start->toDateString() : null,
                    'actual_end_date' => $def['status'] === 'Completed' ? $end->copy()->subDays(5)->toDateString() : null,
                    'budget' => $this->faker->numberBetween(150, 900) * 1_000_000,
                    'status' => $def['status'],
                ]
            );

            $projects->push($project);
        }

        return $projects;
    }

    private function seedTasks(Project $project, $users)
    {
        $existing = Task::where('project_id', $project->id)->get();
        if ($existing->isNotEmpty()) {
            return $existing;
        }

        $assignees = $users->filter(fn ($u) => $u->hasRole('team_member'))->values();
        if ($assignees->isEmpty()) {
            $assignees = $users;
        }

        $statuses = ['To Do', 'In Progress', 'Review', 'Done'];
        $priorities = ['Low', 'Medium', 'High', 'Critical'];
        $types = ['New Feature', 'Bug', 'Improvement', 'Documentation'];
        $modules = ['Auth', 'Reporting', 'Dashboard', 'API', 'UI/UX'];

        $taskCount = $this->faker->numberBetween(8, 12);
        $weightPool = 100.0;
        $tasks = collect();

        $projectStart = Carbon::parse($project->planned_start_date);
        $projectEnd = Carbon::parse($project->planned_end_date);
        $isCompleted = $project->status === 'Completed';
        $isPlanning = $project->status === 'Planning';

        for ($i = 0; $i < $taskCount; $i++) {
            $weight = $i === $taskCount - 1
                ? round($weightPool, 2)
                : round($weightPool / ($taskCount - $i) * $this->faker->randomFloat(2, 0.6, 1.4), 2);
            $weight = max(1, min($weight, $weightPool));
            $weightPool = max(0, $weightPool - $weight);

            $taskStart = (clone $projectStart)->addDays($this->faker->numberBetween(0, 20));
            $taskEnd = (clone $taskStart)->addDays($this->faker->numberBetween(5, 20));
            if ($taskEnd->gt($projectEnd)) {
                $taskEnd = $projectEnd->copy();
            }

            if ($isCompleted) {
                $status = 'Done';
            } elseif ($isPlanning) {
                $status = 'To Do';
            } else {
                $status = $statuses[$this->faker->numberBetween(0, 3)];
            }

            $task = Task::create([
                'task_code' => $project->code.'-T'.str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'project_id' => $project->id,
                'name' => $this->faker->sentence(4),
                'description' => $this->faker->paragraph(2),
                'assigned_to' => $assignees[$this->faker->numberBetween(0, $assignees->count() - 1)]->id,
                'reporter_id' => $project->pm_id,
                'priority' => $priorities[$this->faker->numberBetween(0, 3)],
                'task_type' => $types[$this->faker->numberBetween(0, 3)],
                'module' => $modules[$this->faker->numberBetween(0, 4)],
                'weight' => $weight,
                'start_date' => $taskStart->toDateString(),
                'end_date' => $taskEnd->toDateString(),
                'status' => $status,
                'estimated_hours' => $this->faker->numberBetween(8, 80),
                'actual_hours' => $status === 'Done' ? $this->faker->numberBetween(8, 90) : null,
            ]);

            $tasks->push($task);
        }

        return $tasks;
    }

    private function seedTaskProgress($tasks, Project $project)
    {
        foreach ($tasks as $task) {
            if ($task->progress()->exists()) {
                continue;
            }

            $target = match ($task->status) {
                'Done' => 100,
                'Review' => $this->faker->numberBetween(80, 95),
                'In Progress' => $this->faker->numberBetween(20, 70),
                default => 0,
            };

            if ($target === 0) {
                continue;
            }

            $reports = $this->faker->numberBetween(2, 4);
            $taskStart = Carbon::parse($task->start_date ?? $project->planned_start_date);
            $cumulative = 0;

            for ($r = 1; $r <= $reports; $r++) {
                $cumulative = $r === $reports ? $target : round($target * $r / $reports);
                TaskProgress::create([
                    'task_id' => $task->id,
                    'reported_by' => $task->assigned_to ?? $project->pm_id,
                    'progress_percent' => $cumulative,
                    'report_date' => $taskStart->copy()->addDays($r * 5)->toDateString(),
                    'notes' => $this->faker->sentence(),
                ]);
            }
        }
    }

    private function seedTaskComments($tasks, $users)
    {
        foreach ($tasks->take(6) as $task) {
            if ($task->comments()->exists()) {
                continue;
            }

            $count = $this->faker->numberBetween(1, 3);
            for ($i = 0; $i < $count; $i++) {
                TaskComment::create([
                    'task_id' => $task->id,
                    'user_id' => $users->random()->id,
                    'comment' => $this->faker->sentence(10),
                ]);
            }
        }
    }

    private function seedTaskDependencies($tasks)
    {
        $tasks = $tasks->values();
        for ($i = 1; $i < min($tasks->count(), 5); $i++) {
            TaskDependency::firstOrCreate([
                'task_id' => $tasks[$i]->id,
                'depends_on_task_id' => $tasks[$i - 1]->id,
            ], [
                'dependency_type' => 'blocks',
            ]);
        }
    }

    private function seedTaskActivities($tasks, $users)
    {
        $actions = ['created', 'status_changed', 'assigned', 'commented'];

        foreach ($tasks as $task) {
            if ($task->activities()->exists()) {
                continue;
            }

            $created = TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => $task->reporter_id ?? $users->random()->id,
                'action' => 'created',
                'details' => json_encode(['message' => 'Task created']),
            ]);
            $created->forceFill(['created_at' => Carbon::parse($task->start_date ?? now())->subDays(1)])->save();

            if ($task->status !== 'To Do') {
                $changed = TaskActivity::create([
                    'task_id' => $task->id,
                    'user_id' => $task->assigned_to ?? $users->random()->id,
                    'action' => 'status_changed',
                    'details' => json_encode(['from' => 'To Do', 'to' => $task->status]),
                ]);
                $changed->forceFill(['created_at' => Carbon::parse($task->start_date ?? now())->addDays(2)])->save();
            }
        }
    }

    private function seedTimesheets($tasks, $users)
    {
        foreach ($tasks->filter(fn ($t) => $t->status !== 'To Do')->take(6) as $task) {
            if ($task->timesheets()->exists()) {
                continue;
            }

            $entries = $this->faker->numberBetween(1, 3);
            for ($i = 0; $i < $entries; $i++) {
                $start = $this->faker->time('H:i:s', '12:00:00');
                Timesheet::create([
                    'task_id' => $task->id,
                    'user_id' => $task->assigned_to ?? $users->random()->id,
                    'date' => Carbon::parse($task->start_date ?? now())->addDays($i + 1)->toDateString(),
                    'start_time' => $start,
                    'end_time' => Carbon::parse($start)->addHours($this->faker->numberBetween(1, 4))->format('H:i:s'),
                    'duration' => $this->faker->numberBetween(1, 4),
                    'notes' => $this->faker->sentence(6),
                ]);
            }
        }
    }

    private function seedNotifications($users)
    {
        $admin = $users->firstWhere('email', 'admin@pmtracker.test');
        if (! $admin) {
            return;
        }

        $messages = [
            'Task "Setup CI pipeline" was marked as Done',
            'You were assigned to a new task on PRJ-002',
            'Project PRJ-003 status changed to Planning',
        ];

        foreach ($messages as $i => $message) {
            \Illuminate\Support\Facades\DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\GenericNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $admin->id,
                'data' => json_encode(['message' => $message]),
                'read_at' => $i === 0 ? now() : null,
                'created_at' => now()->subHours($i + 1),
                'updated_at' => now()->subHours($i + 1),
            ]);
        }
    }
}
