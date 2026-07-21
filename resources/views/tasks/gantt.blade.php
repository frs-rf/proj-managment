<div class="card bg-dark text-white border-secondary">
    <div class="card-body overflow-auto">
        <svg id="gantt"></svg>
    </div>
</div>

<!-- Frappe Gantt CSS/JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.css" />
<style>
    .gantt .grid-header { fill: #212529; }
    .gantt .grid-row { fill: #2c3034; }
    .gantt .grid-row:nth-child(even) { fill: #343a40; }
    .gantt .tick { stroke: #495057; }
    .gantt .bar-wrapper { cursor: pointer; }
    .gantt .bar-label { fill: #fff; font-weight: 500; font-size: 12px; }
    .gantt .bar-progress { fill: #0d6efd; }
    .gantt .bar { fill: #6c757d; }
    /* Custom Priority Colors */
    .gantt .gantt-bar-urgent .bar { fill: #dc3545; }
    .gantt .gantt-bar-high .bar { fill: #fd7e14; }
    .gantt .gantt-bar-medium .bar { fill: #0d6efd; }
    .gantt .gantt-bar-low .bar { fill: #198754; }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.min.js"></script>

<script>
    $(document).ready(function() {
        $.get("{{ route('tasks.gantt_data') }}", function(tasks) {
            if (tasks && tasks.length > 0) {
                var gantt = new Gantt("#gantt", tasks, {
                    header_height: 50,
                    column_width: 30,
                    step: 24,
                    view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
                    bar_height: 25,
                    bar_corner_radius: 3,
                    arrow_curve: 5,
                    padding: 18,
                    view_mode: 'Day',
                    date_format: 'YYYY-MM-DD',
                    custom_popup_html: function(task) {
                        return `
                            <div class="p-2 bg-dark text-white border border-secondary rounded shadow-sm">
                                <h6>${task.name}</h6>
                                <p class="mb-1 small">Progress: ${task.progress}%</p>
                                <p class="mb-0 small text-muted">${task.start} - ${task.end}</p>
                            </div>
                        `;
                    },
                    on_click: function (task) {
                        if(typeof window.openTaskEditModal === 'function') {
                            window.openTaskEditModal(task.id);
                        }
                    },
                    on_date_change: function(task, start, end) {
                        // Call backend to save
                        let s = start.toISOString().split('T')[0];
                        let e = end.toISOString().split('T')[0];
                        $.ajax({
                            url: "{{ route('tasks.update_dates') }}",
                            type: "POST",
                            data: {
                                task_id: task.id,
                                start_date: s,
                                end_date: e
                            },
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            success: function(res) {
                                // updated
                            },
                            error: function(xhr) {
                                alert("Failed to update date.");
                            }
                        });
                    }
                });
                
                // Add view mode controls if not exists
                if ($('#gantt-view-modes').length === 0) {
                    let controls = `
                        <div id="gantt-view-modes" class="mb-3 d-flex justify-content-end gap-2">
                            <button class="btn btn-sm btn-outline-secondary" onclick="gantt.change_view_mode('Day')">Day</button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="gantt.change_view_mode('Week')">Week</button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="gantt.change_view_mode('Month')">Month</button>
                        </div>
                    `;
                    $('#gantt').parent().prepend(controls);
                }
            } else {
                $('#gantt').parent().html('<div class="text-center text-muted p-5">No tasks available for Gantt chart.</div>');
            }
        });
    });
</script>
