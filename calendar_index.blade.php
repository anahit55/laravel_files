@php
/* @var $events */
@endphp
@extends('layouts.app-master')
@section('content')
    <div id="content_headerbar">
        <h1 class="headerbar-title">
            {{ trans('Calendar') }}
        </h1>
        @include('layouts.partials.data_stream', ['dataStreamUsers' => $dataStreamUsers ?? []])
    </div>
    <div id="content" class="table-content">
        <div class="col-lg-8 m-auto" style="float: unset; margin-top: 2% !important;background: white;padding: 25px;border: 2px solid #ececec;">
            <div id="filter_results">
                <div id='calendar'></div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function initializeCalendar(events, workingHours) {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                droppable: true,
                dayMaxEventRows: 2,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                weekNumbers: true,
                weekNumberCalculation: 'ISO',
                businessHours: workingHours,
                events: events,
                eventClick: function(info) {
                    console.log('Event clicked:', info.event.title);
                }
            });
            calendar.render();
        }
        document.addEventListener('DOMContentLoaded', function() {
            var events = @json($events);
            var workingHours = [
                {
                    daysOfWeek: [0],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->sunday_night_start : '00:00'  }}',
                    endTime: '{{ !is_null($working_hours) ? $working_hours->sunday_night_end : '08:00'  }}'
                },
                {
                    daysOfWeek: [0],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->sunday_day_start : '08:00'  }}',
                    endTime: '{{ !is_null($working_hours) ? $working_hours->sunday_day_end : '17:00'  }}'
                },
                {
                    daysOfWeek: [0],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->sunday_evening_start : '17:00'  }}',
                    endTime: '{{ !is_null($working_hours) ? ($working_hours->sunday_evening_end == '00:00:00' ? '24:00' : $working_hours->sunday_evening_end) : '24:00'  }}'
                },
                {
                    daysOfWeek: [1],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->monday_night_start : '00:00' }}',
                    endTime: '{{ !is_null($working_hours) ? $working_hours->monday_night_end : '08:00' }}'
                },
                {
                    daysOfWeek: [1],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->monday_day_start : '08:00' }}',
                    endTime: '{{ !is_null($working_hours) ? $working_hours->monday_day_end : '17:00' }}'
                },
                {
                    daysOfWeek: [1],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->monday_evening_start : '17:00' }}',
                    endTime: '{{ !is_null($working_hours) ? ($working_hours->monday_evening_end == '00:00:00' ? '24:00' : $working_hours->monday_evening_end) : '24:00' }}'
                },
                {
                    daysOfWeek: [2],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->tuesday_night_start : '00:00' }}',
                    endTime: '{{ !is_null($working_hours) ? $working_hours->tuesday_night_end : '08:00' }}'
                },
                {
                    daysOfWeek: [2],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->tuesday_day_start : '08:00' }}',
                    endTime: '{{ !is_null($working_hours) ? $working_hours->tuesday_day_end : '17:00' }}'
                },
                {
                    daysOfWeek: [2],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->tuesday_evening_start : '17:00' }}',
                    endTime: '{{ !is_null($working_hours) ? ($working_hours->tuesday_evening_end == '00:00:00' ? '24:00' : $working_hours->tuesday_evening_end) : '24:00' }}'
                },
                {
                    daysOfWeek: [3],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->wednesday_night_start : '00:00' }}',
                    endTime: '{{ !is_null($working_hours) ? $working_hours->wednesday_night_end : '08:00' }}'
                },
                {
                    daysOfWeek: [3],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->wednesday_day_start : '08:00' }}',
                    endTime: '{{ !is_null($working_hours) ? $working_hours->wednesday_day_end : '17:00' }}'
                },
                {
                    daysOfWeek: [3],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->wednesday_evening_start : '17:00' }}',
                    endTime: '{{ !is_null($working_hours) ? ($working_hours->wednesday_evening_end == '00:00:00' ? '24:00' : $working_hours->wednesday_evening_end) : '24:00' }}'
                },
                {
                    daysOfWeek: [4],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->thursday_night_start : '00:00' }}',
                    endTime: '{{ !is_null($working_hours) ? $working_hours->thursday_night_end : '08:00' }}'
                },
                {
                    daysOfWeek: [4],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->thursday_day_start : '08:00' }}',
                    endTime: '{{ !is_null($working_hours) ? $working_hours->thursday_day_end : '17:00' }}'
                },
                {
                    daysOfWeek: [4],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->thursday_evening_start : '17:00' }}',
                    endTime: '{{ !is_null($working_hours) ? ($working_hours->thursday_evening_end == '00:00:00' ? '24:00' : $working_hours->thursday_evening_end) : '24:00' }}'
                },
                {
                    daysOfWeek: [5],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->friday_night_start : '00:00' }}',
                    endTime: '{{ !is_null($working_hours) ? $working_hours->friday_night_end : '08:00' }}'
                },
                {
                    daysOfWeek: [5],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->friday_day_start : '08:00' }}',
                    endTime: '{{ !is_null($working_hours) ? $working_hours->friday_day_end : '17:00' }}'
                },
                {
                    daysOfWeek: [5],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->friday_evening_start : '17:00' }}',
                    endTime: '{{ !is_null($working_hours) ? ($working_hours->friday_evening_end == '00:00:00' ? '24:00' : $working_hours->friday_evening_end) : '24:00' }}'
                },
                {
                    daysOfWeek: [6],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->saturday_night_start : '00:00' }}',
                    endTime: '{{ !is_null($working_hours) ? $working_hours->saturday_night_end : '08:00' }}'
                },
                {
                    daysOfWeek: [6],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->saturday_day_start : '08:00' }}',
                    endTime: '{{ !is_null($working_hours) ? $working_hours->saturday_day_end : '17:00' }}'
                },
                {
                    daysOfWeek: [6],
                    startTime: '{{ !is_null($working_hours) ? $working_hours->saturday_evening_start : '17:00' }}',
                    endTime: '{{ !is_null($working_hours) ? ($working_hours->saturday_evening_end == '00:00:00' ? '24:00' : $working_hours->saturday_evening_end) : '24:00' }}'
                },
            ];
            initializeCalendar(events.data, workingHours);
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.mm-dropdown ul').on('click', 'li', function() {
                var dataStreamId = $(this).data('value');

                $.ajax({
                    url: '/filter-calendar', // Update this with the actual server endpoint URL
                    method: 'GET',
                    data: {
                        dataStreamId: dataStreamId
                    },
                    success: function(response) {
                        console.log('response',response);

                        $('#filter_results').html('<div id="calendar"></div>');
                        var newEvents = response.events.data;
                        var newWorkingHours = response.working_hours;
                        console.log(newWorkingHours)
                        var workingHours = [
                            {
                                daysOfWeek: [0],
                                startTime: newWorkingHours.sunday_night_start || '00:00',
                                endTime: newWorkingHours.sunday_night_end || '08:00'
                            },
                            {
                                daysOfWeek: [0],
                                startTime: newWorkingHours.sunday_day_start || '08:00',
                                endTime: newWorkingHours.sunday_day_end || '17:00'
                            },
                            {
                                daysOfWeek: [0],
                                startTime: newWorkingHours.sunday_evening_start || '17:00',
                                endTime: newWorkingHours.sunday_evening_end === '00:00:00' ? '24:00' : newWorkingHours.sunday_evening_end || '24:00'
                            },
                            {
                                daysOfWeek: [1],
                                startTime: newWorkingHours.monday_night_start || '00:00',
                                endTime: newWorkingHours.monday_night_end || '08:00'
                            },
                            {
                                daysOfWeek: [1],
                                startTime: newWorkingHours.monday_day_start || '08:00',
                                endTime: newWorkingHours.monday_day_end || '17:00'
                            },
                            {
                                daysOfWeek: [1],
                                startTime: newWorkingHours.monday_evening_start || '17:00',
                                endTime: newWorkingHours.monday_evening_end === '00:00:00' ? '24:00' : newWorkingHours.monday_evening_end || '24:00'
                            },
                            {
                                daysOfWeek: [2],
                                startTime: newWorkingHours.tuesday_night_start || '00:00',
                                endTime: newWorkingHours.tuesday_night_end || '08:00'
                            },
                            {
                                daysOfWeek: [2],
                                startTime: newWorkingHours.tuesday_day_start || '08:00',
                                endTime: newWorkingHours.tuesday_day_end || '17:00'
                            },
                            {
                                daysOfWeek: [2],
                                startTime: newWorkingHours.tuesday_evening_start || '17:00',
                                endTime: newWorkingHours.tuesday_evening_end === '00:00:00' ? '24:00' : newWorkingHours.tuesday_evening_end || '24:00'
                            },
                            {
                                daysOfWeek: [3],
                                startTime: newWorkingHours.wednesday_night_start || '00:00',
                                endTime: newWorkingHours.wednesday_night_end || '08:00'
                            },
                            {
                                daysOfWeek: [3],
                                startTime: newWorkingHours.wednesday_day_start || '08:00',
                                endTime: newWorkingHours.wednesday_day_end || '17:00'
                            },
                            {
                                daysOfWeek: [3],
                                startTime: newWorkingHours.wednesday_evening_start || '17:00',
                                endTime: newWorkingHours.wednesday_evening_end === '00:00:00' ? '24:00' : newWorkingHours.wednesday_evening_end || '24:00'
                            },
                            {
                                daysOfWeek: [4],
                                startTime: newWorkingHours.thursday_night_start || '00:00',
                                endTime: newWorkingHours.thursday_night_end || '08:00'
                            },
                            {
                                daysOfWeek: [4],
                                startTime: newWorkingHours.thursday_day_start || '08:00',
                                endTime: newWorkingHours.thursday_day_end || '17:00'
                            },
                            {
                                daysOfWeek: [4],
                                startTime: newWorkingHours.thursday_evening_start || '17:00',
                                endTime: newWorkingHours.thursday_evening_end === '00:00:00' ? '24:00' : newWorkingHours.thursday_evening_end || '24:00'
                            },
                            {
                                daysOfWeek: [5],
                                startTime: newWorkingHours.friday_night_start || '00:00',
                                endTime: newWorkingHours.friday_night_end || '08:00'
                            },
                            {
                                daysOfWeek: [5],
                                startTime: newWorkingHours.friday_day_start || '08:00',
                                endTime: newWorkingHours.friday_day_end || '17:00'
                            },
                            {
                                daysOfWeek: [5],
                                startTime: newWorkingHours.friday_evening_start || '17:00',
                                endTime: newWorkingHours.friday_evening_end === '00:00:00' ? '24:00' : newWorkingHours.friday_evening_end || '24:00'
                            },
                            {
                                daysOfWeek: [6],
                                startTime: newWorkingHours.saturday_night_start || '00:00',
                                endTime: newWorkingHours.saturday_night_end || '08:00'
                            },
                            {
                                daysOfWeek: [6],
                                startTime: newWorkingHours.saturday_day_start || '08:00',
                                endTime: newWorkingHours.saturday_day_end || '17:00'
                            },
                            {
                                daysOfWeek: [6],
                                startTime: newWorkingHours.saturday_evening_start || '17:00',
                                endTime: newWorkingHours.saturday_evening_end === '00:00:00' ? '24:00' : newWorkingHours.saturday_evening_end || '24:00'
                            }
                        ];
                        // Reinitialize the calendar
                        initializeCalendar(newEvents, workingHours);
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });
        });
    </script>
@endsection
