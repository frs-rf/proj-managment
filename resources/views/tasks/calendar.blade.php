<div class="card bg-dark text-white border-secondary">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

<!-- FullCalendar CSS/JS loaded locally or via CDN -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet" />
<style>
    .fc-theme-standard .fc-scrollgrid { border-color: #495057; }
    .fc-theme-standard td, .fc-theme-standard th { border-color: #495057; }
    .fc .fc-toolbar-title { font-size: 1.25em; font-weight: 600; }
    .fc .fc-button-primary { background-color: #0d6efd; border-color: #0d6efd; }
    .fc .fc-button-primary:not(:disabled):active, .fc .fc-button-primary:not(:disabled).fc-button-active { background-color: #0b5ed7; border-color: #0a58ca; }
    .fc-daygrid-day-number { color: #dee2e6; text-decoration: none; }
    .fc-col-header-cell-cushion { color: #dee2e6; text-decoration: none; }
    .fc-event { cursor: pointer; }
</style>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            themeSystem: 'standard',
            events: "{{ route('tasks.calendar_data') }}",
            editable: true,
            droppable: true,
            eventClick: function(info) {
                // Trigger edit modal
                let taskId = info.event.id;
                // Find the edit button logic or trigger click
                if(typeof window.openTaskEditModal === 'function') {
                    window.openTaskEditModal(taskId);
                } else {
                    // Fallback to trigger click if edit button exists
                    let fakeBtn = $('<button class="edit-task" style="display:none" data-id="'+taskId+'"></button>');
                    $('body').append(fakeBtn);
                    fakeBtn.click();
                    fakeBtn.remove();
                }
            },
            eventDrop: function(info) {
                // Update start/end date
                let taskId = info.event.id;
                let start = info.event.startStr.split('T')[0];
                let end = info.event.end ? info.event.endStr.split('T')[0] : start;
                
                // Fullcalendar end date is exclusive, subtract 1 day for our DB logic
                if(info.event.end) {
                    let d = new Date(info.event.end);
                    d.setDate(d.getDate() - 1);
                    end = d.toISOString().split('T')[0];
                }

                $.ajax({
                    url: "{{ route('tasks.update_dates') }}",
                    type: "POST",
                    data: {
                        task_id: taskId,
                        start_date: start,
                        end_date: end
                    },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        // success
                    },
                    error: function(xhr) {
                        alert('Error updating dates');
                        info.revert();
                    }
                });
            }
        });
        calendar.render();
        
        // Trigger resize when tab/view is shown to fix layout issues
        setTimeout(() => { calendar.updateSize(); }, 200);
    });
</script>
