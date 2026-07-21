<!DOCTYPE html>
<html>
<head>
    <title>Tasks Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Tasks Report</h2>
        <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Task Name</th>
                <th>Project</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Assignee</th>
                <th>Due Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
            <tr>
                <td>{{ $task->task_code }}</td>
                <td>{{ $task->name }}</td>
                <td>{{ $task->project ? $task->project->name : '-' }}</td>
                <td>{{ $task->status }}</td>
                <td>{{ $task->priority }}</td>
                <td>{{ $task->assignee ? $task->assignee->name : '-' }}</td>
                <td>{{ $task->end_date ? $task->end_date->format('Y-m-d') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
