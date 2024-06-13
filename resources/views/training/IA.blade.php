@extends('layouts.app')

@section('styles')
<link href="{{ asset('/css/calendar.css') }}" rel="stylesheet">
<link href='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css' rel='stylesheet' />
@endsection

@section('content')
<div class="container pt-3 pb-2 mb-3 ">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-boton">
        <h1 style="color: white;">HORARIO DE ENTRENAMIENTO <span class="badge bg-warning text-dark">beta</span></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-success" id="newScheduleBtn">
                    <i class="bi bi-plus"></i> Nuevo Horario
                 </button>
                <a href="{{ route('home') }}" class="btn btn-danger">
                    <i class="bi bi-arrow-return-left"></i> Regresar
                </a>
            </div>
        </div>
    </div>
    <span id="selectedDays"></span>

    <div class="calendar-container">
        <div id="calendar"></div>
    </div>

    <!-- Modal para seleccionar días -->
    <div class="modal fade" id="daysModal" tabindex="-1" aria-labelledby="daysModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="daysModalLabel">Seleccione un día</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="row mb-3">
                            @foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves'] as $day)
                                <div class="col-3">
                                    <button type="button" class="btn btn-primary w-100 mb-2" onclick="highlightButton(this)">{{ $day }}</button>
                                </div>
                            @endforeach
                        </div>
                        <div class="row mb-3">
                            @foreach (['Viernes', 'Sábado', 'Domingo'] as $day)
                                <div class="col-4">
                                    <button type="button" class="btn btn-primary w-100 mb-2" onclick="highlightButton(this)">{{ $day }}</button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="studyHours" class="form-label">Horas de estudio:</label>
                        <input type="number" class="form-control" id="studyHours" min="1" placeholder="Ingrese horas de estudio">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="scheduleBtn">Programar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para falta de datos -->
    <div class="modal fade" id="noDataModal" tabindex="-1" aria-labelledby="noDataModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="noDataModalLabel">Datos insuficientes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Necesita realizar más datos.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="window.location.href='{{ route('exams.index') }}'">Ir a Exams</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar horario -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Está seguro de que desea eliminar el horario actual?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('inline_scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<script>
    var selectedDay = null;

    function highlightButton(button) {
        $(button).toggleClass('active');
        selectedDay = $(button).text();
        var selectedDays = [];
        $('#daysModal .btn.active').each(function() {
            selectedDays.push($(this).text());
        });
        $('#selectedDays').text('(' + selectedDays.join(', ') + ')');
    }

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            events: [],
            eventClick: function(info) {
                var categoryId = info.event.extendedProps.categoryId; // Assuming you have a categoryId in extendedProps
                var trainingRoute = "{{ route('training.training', ['id' => ':id']) }}";
                trainingRoute = trainingRoute.replace(':id', categoryId);
                window.location.href = trainingRoute;
            }
        });
        calendar.render();

        $('#daysModal').modal('show');

        $('#scheduleBtn').click(function() {
            var selectedDays = [];
            $('#daysModal .btn.active').each(function() {
                selectedDays.push($(this).text());
            });

            var categories = [];
            @foreach ($response->json() as $item)
                @if ($item['nota'] < 10)
                    categories.push({
                        id: '{{ DB::table('question_categories')->where('id', $item['categoria'])->value('id') }}',
                        name: '{{ DB::table('question_categories')->where('id', $item['categoria'])->value('name') }}'
                    });
                @endif
            @endforeach

            var daysMapping = {
                "Monday": "Lunes",
                "Tuesday": "Martes",
                "Wednesday": "Miércoles",
                "Thursday": "Jueves",
                "Friday": "Viernes",
                "Saturday": "Sábado",
                "Sunday": "Domingo"
            };

            var events = [];
            var currentDate = moment().startOf('month');
            while (currentDate.month() == moment().month()) {
                var dayName = daysMapping[currentDate.format('dddd')];
                if (selectedDays.includes(dayName)) {
                    var randomCategory = categories[Math.floor(Math.random() * categories.length)];
                    events.push({
                        title: randomCategory.name,
                        start: currentDate.format("YYYY-MM-DD"),
                        allDay: true,
                        extendedProps: {
                            categoryId: randomCategory.id
                        }
                    });
                }
                currentDate.add(1, 'day');
            }

            calendar.addEventSource(events);

            $('#daysModal').modal('hide');
        });

        $('#newScheduleBtn').click(function() {
            $('#confirmDeleteModal').modal('show');
        });

        $('#confirmDeleteBtn').click(function() {
            calendar.removeAllEvents();
            $('#daysModal').modal('show');
            $('#confirmDeleteModal').modal('hide');
        });
    });
</script>
@endsection