@extends('layouts.marketplace-master')
@section('styles')
    <link href="{!! url('assets/css/marketplace.css') !!}" rel="stylesheet">
    <link href="{!! url('assets/css/global/drag.css') !!}" rel="stylesheet">
@endsection
@section('content')
    <section>
        <button type="button" class="btn btn-danger btn" id="btn-back-to-top" style="display: none">
            <i class="fas fa-arrow-down"></i>
        </button>
        <div class="col-md-10 m-auto mt-3 p-0">
            <div class="wrapper">
                <div class="box box1">
                    <div id='marketplace_titles' class="d-flex align-items-center marketplace_titles justify-content-center">
                        <div class="fw-bolder col-md-12">
                            <div style="position: relative">
                                <div id="date-paginator" class="align-items-center d-flex">
                                    <div id="prev-date" class="col-md-1 cursor_pointer">
                                        <i class="fa-solid fa-chevron-left"></i>
                                    </div>
                                    <div class="col-md-10 d-flex">
                                        <div class="col-md-4">
                                            <label><input type="text" id="current-weekday-before" value="{{ date('l',strtotime('-1 days')) }}" disabled></label><br>
                                            <label><input type="text" id="current-date-before" value="{{ date('jS M Y',strtotime('-1 days')) }}" disabled></label>
                                        </div>
                                        <div class="col-md-4" id="selected_date">
                                            <label><input type="text" id="current-weekday" value="{{ date('l') }}" disabled></label><br>
                                            <label><input type="text" id="current-date" value="{{ date('jS M Y') }}" disabled></label>
                                        </div>
                                        <div class="col-md-4">
                                            <label><input type="text" id="current-weekday-next" value="{{ date('l',strtotime('+1 days')) }}" disabled></label><br>
                                            <label><input type="text" id="current-date-next" value="{{ date('jS M Y',strtotime('+1 days')) }}" disabled></label>
                                        </div>
                                    </div>
                                    <div id="next-date" class="col-md-1 cursor_pointer">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="scrollable_section">
                        @foreach($users as $user)
                            <div class="card_wrapper single_card"  data-id="{{ $user->id }}">
                                <div class="card-box-1">
                                    <div class="profile_wrapper">
                                        <div class="col-6 col-xl-12 user_card_img">
                                            @if($user->profile_picture)
                                                <img src="{{ url('uploads/users/profile/'. $user->profile_picture) }}" alt="Avatar">
                                            @else
                                                <div class="marketplace_avatar">
                                                    {!! getAvatar($user) !!}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-6 col-xl-12">
                                            <div class="user_card_details">
                                                @if($user->marketplaceNickname)
                                                    <div class="user_name">{{ $user->marketplaceNickname }}</div>
                                                @else
                                                    <div class="user_name">{{ $user->first_name }} {{ $user->last_name }}</div>
                                                @endif
                                                <div class="user_info">
                                                    <div class="user_info_title fw-bolder">
                                                        <i class="fa-solid fa-location-dot text-danger"></i>
                                                        {{ trans('marketplace.location') }}
                                                    </div>
                                                    <div class="user_info_details">
                                                        @if($user->additional_location_confirm == 1)
                                                            {{ ucfirst($user->additional_location) }}, Online
                                                        @else
                                                            @isset($user->location)
                                                                {{ $user->location->city }}, {{ $user->location->country }}
                                                            @else
                                                                Not mentioned
                                                            @endisset
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="user_info">
                                                    <div class="user_info_title fw-bolder">
                                                        <i class="fa-solid fa-language"></i>
                                                        {{ trans('marketplace.languages') }}
                                                    </div>
                                                    <div class="languages_section">
                                                        @foreach($user->languages as $language)
                                                            <div class="language">
                                                                {{ $language->language }} : {{ $language->pivot->level }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-box-2 rates_section">
                                    @foreach($appointmentTypes[$user->id] as $appointmentType)
                                        <div>
                                            <div class="skills font-akshar fs-6 col-md-6 rounded-1"
                                                 data-id="{{ $appointmentType->id }}"
                                                 data-location-id="{{ $appointmentType->location_id }}"
                                                 data-user-id="{{ $user->id }}"
                                                 data-price-per-kilometer="{{ $appointmentType->price_per_kilometer }}"
                                                 data-price-per-kilometer-type="{{ $appointmentType->price_per_kilometer_type }}"
                                                 data-price="{{ $appointmentType->price }}"
                                                 data-name="{{ $appointmentType->name }}">
                                                <span>{{ $appointmentType->name }} </span> : <span>€ {{ number_format($appointmentType->price,2) }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="card-box-3">
                                    <div class="form-check form-switch mt-2 callCheckboxSection" style="display: none">
                                        <input class="form-check-input" type="checkbox" name="call_checkbox" id="callCheckbox_{{ $user->id }}">
                                        <label class="form-check-label" for="callCheckbox_{{ $user->id }}">Call me back to make an appointment</label>
                                    </div>
                                    <div class="accordion" id="accordionPanelsStayOpenExample">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="panelsStayOpen-heading-day-{{ $user->id }}">
                                                <button class="accordion-button p-2" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapse-day-{{ $user->id }}" aria-expanded="true" aria-controls="panelsStayOpen-collapse-day-{{ $user->id }}">
                                                    Day hours
                                                </button>
                                            </h2>
                                            <div id="panelsStayOpen-collapse-day-{{ $user->id }}" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-heading-day-{{ $user->id }}">
                                                <div class="accordion-body" style="padding: 10px">
                                                    <div class="slots_section" id="user_day_{{ $user->id }}" data-user-id="{{ $user->id }}">

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="panelsStayOpen-heading-evening-{{ $user->id }}">
                                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapse-evening-{{ $user->id }}" aria-expanded="false" aria-controls="panelsStayOpen-collapse-evening-{{ $user->id }}">
                                                    Evening hours
                                                </button>
                                            </h2>
                                            <div id="panelsStayOpen-collapse-evening-{{ $user->id }}" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-heading-evening-{{ $user->id }}">
                                                <div class="accordion-body" style="padding: 10px">
                                                    <div class="slots_section" id="user_evening_{{ $user->id }}" data-user-id="{{ $user->id }}">

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="panelsStayOpen-heading-night-{{ $user->id }}">
                                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapse-night-{{ $user->id }}" aria-expanded="false" aria-controls="panelsStayOpen-collapse-night-{{ $user->id }}">
                                                    Night hours
                                                </button>
                                            </h2>
                                            <div id="panelsStayOpen-collapse-night-{{ $user->id }}" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-heading-night-{{ $user->id }}">
                                                <div class="accordion-body" style="padding: 10px">
                                                    <div class="slots_section" id="user_night_{{ $user->id }}" data-user-id="{{ $user->id }}">

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="box box2">
                    <div class="card_header">
                        <div class="card_header_title fw-bolder font-akshar">{{ trans('marketplace.make_an_appointment') }}</div>
                    </div>
                    <div class="mt-3">
                        @include('layouts.partials.messages')
                        <form method="post" enctype="multipart/form-data" action="{{ route('test.marketplace.schedule') }}">
                            @csrf
                            <input id="date" type="hidden" name="date" value="">
                            <input id="user_id" type="hidden" name="user_id" value="">
                            <input id="location_id" type="hidden" name="location_id" value="">
                            <input id="type" type="hidden" name="type" value="test">
                            <input id="call_checkbox_input" type="hidden" name="call_checkbox_input" value="false">
                            <div class="form-group">
                                <label>
                                    <input id="appointment_type" type="text" autocomplete="true"
                                           placeholder="{{ trans('marketplace.appointment_type') }}" title="Select from rates section"
                                           class="form-control readonly" value="" required readonly>
                                    <input id="appointment_type_id" type="hidden" name="appointment_type_id" autocomplete="false"
                                           class="form-control" value="">
                                </label>
                            </div>
                            <div class="d-flex">
                                <div class="form-group" style="margin-right: 5px; width: 100%">
                                    <label>
                                        <input id="start_time" type="text" name="start_time" autocomplete="false"
                                               placeholder="{{ trans('marketplace.start_time') }}" title="Select slot"
                                               class="form-control" required value="" readonly>
                                    </label>
                                </div>
                                <div class="form-group" style="margin-left: 5px; width: 50%;display: none">
                                    <label>
                                        <input id="end_time" type="text" name="end_time" autocomplete="false"
                                               placeholder="End time" title="Select slot"
                                               class="form-control" required value="" readonly>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input id="name" type="text" name="name" autocomplete="true"
                                           placeholder="{{ trans('marketplace.first_and_last_name') }}"
                                           value="{{ old('name') }}"
                                           class="form-control" required>
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="tel" name="phone" autocomplete="true"
                                           placeholder="{{ trans('marketplace.phone') }}"
                                           value="{{ old('phone') }}"
                                           class="form-control" required>
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="email" name="email" autocomplete="true"
                                           value="{{ old('email') }}"
                                           placeholder="{{ trans('marketplace.email') }}"
                                           class="form-control" required>
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <select id="city" class="form-control" name="city">
                                        <option value="">-- Select city --</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->city }}">{{ $city->city }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input id="kilometer" type="number" min="0" step=".01" name="kilometer" autocomplete="false"
                                           placeholder="Kilometer"
                                           class="form-control" value="">
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <textarea name="comment" id="comment" class="form-control"
                                              placeholder="{{ trans('marketplace.comments') }}">{{ old('comment') }}</textarea>
                                </label>
                            </div>
                            <div class="form-group" style="display: flex;align-items: baseline">
                                <label>{{ trans('marketplace.make_a_calculation') }}</label>
                            </div>
                            <div class="form-group mb-2">
                                <label>
                                    <input id="total_m2" type="number" min="0" name="total_m2" autocomplete="false"
                                           placeholder="Select number of hours, m2/m3"
                                           class="form-control" value="">
                                </label>
                            </div>
                            <div class="form-group mb-2">
                                <label for="task-dropdown">Select Task</label>
                                <select id="task-dropdown" class="form-control">
                                    <option value="">-- Select Task --</option>
                                    <!-- Dynamic tasks will be loaded here -->
                                </select>
                            </div>
                            <div id="tasks-section" class="mt-3"></div>
                            <div class="d-flex">
                                <div class="form-group mb-2" style="margin-right: 5px; width: 50%">
                                </div>
                                <div class="form-group mb-2" style="margin-left: 5px; width: 50%">
                                    <label for="total_price" class="m-0" style="font-size: 12px">{{ trans('marketplace.total_price') }}</label>
                                    <input id="total_price" type="text" name="total_price" autocomplete="false"
                                           placeholder="{{ trans('marketplace.total_price') }}"
                                           class="form-control" value="" readonly>
                                </div>
                            </div>
                            <div class="form-group mb-4" style="position: relative">
                                <div class="p-0 text-left">
                                    <label for="files">{{ trans('marketplace.upload_pictures_for_request') }}</label>
                                </div>
                                <div class="drop bg-white">
                                    <div style="padding: 10px 0 0 0;">
                                        <i class="fa fa-cloud-upload "></i>
                                        <div class="tit">Drag & Drop</div>
                                    </div>
                                    <output id="list"></output>
                                    <input id="files" multiple name="images[]" type="file" />
                                </div>
                            </div>
                            <div class="form-group" style="display: flex;align-items: baseline">
                                <input type="checkbox" name="terms_and_conditions" id="terms_and_conditions" style="margin-right: 10px">
                                <label for="terms_and_conditions">I accept the <a href="https://workspace.spudu.com/guest/5/contract/26/en">Terms and Conditions</a>, <a href="https://workspace.spudu.com/guest/3/contract/26/en">Privacy Policy</a> and <a href="https://workspace.spudu.com/guest/1/contract/26/en">Cookie Policy</a></label>
                            </div>
                            <input type="submit" name="submit" id="schedule_button" class="font-akshar" value="{{ trans('marketplace.schedule') }}">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            let $current_date = $('#current-date');
            let $current_date_before = $('#current-date-before');
            let $current_date_next = $('#current-date-next');

            let currentDate = new Date(parseData($current_date.val()));
            let currentDateBefore = new Date(parseData($current_date_before.val()));
            let currentDateNext = new Date(parseData($current_date_next.val()));
            updateDate();

            $('#prev-date').on('click', function() {
                currentDate.setDate(currentDate.getDate() - 1);
                currentDateBefore.setDate(currentDateBefore.getDate() - 1);
                currentDateNext.setDate(currentDateNext.getDate() - 1);
                updateDate();
            });
            $('#next-date').on('click', function() {
                currentDate.setDate(currentDate.getDate() + 1);
                currentDateBefore.setDate(currentDateBefore.getDate() + 1);
                currentDateNext.setDate(currentDateNext.getDate() + 1);
                updateDate();
            });

            function updateDate() {

                $('#current-weekday-before').val(currentDateBefore.toLocaleDateString("en-US", {weekday: 'long'}));
                $current_date_before.val(getFormat(currentDateBefore));
                $('#current-weekday').val(currentDate.toLocaleDateString("en-US", {weekday: 'long'}));
                $current_date.val(getFormat(currentDate));
                $('#current-weekday-next').val(currentDateNext.toLocaleDateString("en-US", {weekday: 'long'}))
                $current_date_next.val(getFormat(currentDateNext));

                let momentDate = moment(currentDate, "ddd MMM DD YYYY HH:mm:ss [GMT]Z (ZZ)");
                let selected_date = momentDate.format("YYYY-MM-DD");
                $('#date').val(selected_date);
                $('.slots_section').empty();
                $('.callCheckboxSection').hide();
                let selectedSkill = document.querySelector(".selected_skill");
                if(selectedSkill){
                    let appointment_type_id = selectedSkill.getAttribute("data-id");
                    let user_id = selectedSkill.getAttribute("data-user-id");
                    let base_url = document.location.origin;
                    selectAppointmentType(appointment_type_id, selected_date, user_id, base_url)
                }
            }

            let selectedTasks = [];

            $('.skills').on('click', function(){
                $('.skills').css('background','#dddddd').removeClass('selected_skill')
                $(this).css('background','#48b178').addClass('selected_skill');
                let appointment_type_id = $(this).data('id');
                let appointment_type = $(this).data('name');
                let user_id = $(this).data('user-id');
                let location_id = $(this).data('location-id');

                let $current_date = $('#current-date');
                let currentDate = new Date(parseData($current_date.val()));
                let momentDate = moment(currentDate, "ddd MMM DD YYYY HH:mm:ss [GMT]Z (ZZ)");
                let selected_date = momentDate.format("YYYY-MM-DD");
                let base_url = document.location.origin;

                $('#appointment_type_id').val(appointment_type_id);
                $('#appointment_type').val(appointment_type);
                $('#location_id').val(location_id);

                fetchRelatedTasks(appointment_type_id);
                calculatePrices();

                $('.slots_section').empty();
                $('.accordion-header button').css('background', '#FFFFFF');
                $('.callCheckboxSection').hide();
                $('#user_id').val(user_id);
                selectAppointmentType(appointment_type_id, selected_date, user_id, base_url)

            });

            function calculatePrices() {
                let selectedSkill = $('.selected_skill');
                let total_m2 = $("#total_m2").val();
                let kilometer = $("#kilometer").val();
                let price_per_kilometer = selectedSkill.data('price-per-kilometer') ?? 0;
                let price_per_kilometer_type = selectedSkill.data('price-per-kilometer-type') ?? 0;

                let totalTaskPrice = calculateTotalTaskPrice();
                let skillPrice = selectedSkill.data('price') ?? 0;

                let totalM2Price = +skillPrice * +total_m2;

                let totalKilometerPrice = 0;
                if(price_per_kilometer_type === 'fixed'){
                    totalKilometerPrice = +price_per_kilometer;
                }else{
                    totalKilometerPrice = +kilometer * +price_per_kilometer;
                }

                $('#total_price').val(totalTaskPrice + totalM2Price + totalKilometerPrice);
            }

            function fetchRelatedTasks(appointment_type_id) {
                $('#tasks-section').empty();
                $('#task-dropdown').empty();
                selectedTasks = [];
                $.ajax({
                    url: '/get-as-appointment-type-tasks',
                    method: 'GET',
                    data: { appointment_type_id: appointment_type_id },
                    success: function(response) {
                        let tasks = response.tasks;
                        $('#task-dropdown').empty().append('<option value="">-- Select Task --</option>');

                        tasks.forEach(function(task) {
                            // Only add task to dropdown if it's not already selected
                            if (!selectedTasks.includes(task.id.toString())) {
                                $('#task-dropdown').append(`<option value="${task.id}" data-price="${task.price}">${task.name}</option>`);
                            }
                        });
                    },
                    error: function() {
                        console.error('Error fetching tasks for the selected appointment type.');
                    }
                });
            }

            $(document).on('change', '#task-dropdown', function() {
                const taskId = $(this).val();
                const taskPrice = $(this).find('option:selected').data('price');
                const taskName = $(this).find('option:selected').text();

                if (taskId && !selectedTasks.includes(taskId)) {
                    selectedTasks.push(taskId);
                    appendTaskSection(taskId, taskName, taskPrice);
                    $(this).find(`option[value="${taskId}"]`).remove();
                }
            });

            function appendTaskSection(taskId, taskName, taskPrice) {
                console.log(taskPrice)
                const taskSection = `
                    <div class="d-flex align-items-center task-row mb-2" id="task-${taskId}" data-price="${taskPrice}">
                        <input type="text" name="tasks[]" class="form-control me-2" value="${taskName}" style="width: 50%;" readonly>
                        <input type="hidden" name="appointment_type_task_ids[]" value="${taskId}">
                        <input type="number" name="task_total_m2[]" class="form-control me-2 task-total-m2" placeholder="Total m²" min="0" style="width: 20%;">
                        <input type="number" name="task_prices[]" class="form-control me-2 task-price" placeholder="Price" min="0" style="width: 20%;" readonly>
                        <button type="button" class="btn btn-danger remove-task" data-task-id="${taskId}" data-price="${taskPrice}"><i class="fas fa-minus"></i></button>
                    </div>
                `;
                $('#tasks-section').append(taskSection);
            }

            $(document).on('input', '.task-total-m2', function() {
                const taskRow = $(this).closest('.task-row'); // Find the closest parent task-row div
                const taskPrice = parseFloat(taskRow.data('price')); // Get the base price from data-price
                const totalM2 = parseFloat($(this).val()); // Get the entered m² value

                // Calculate total price if totalM2 and taskPrice are valid numbers
                if (!isNaN(totalM2) && !isNaN(taskPrice)) {
                    const totalPrice = totalM2 * taskPrice;
                    taskRow.find('.task-price').val(totalPrice.toFixed(2)); // Update the task-price input with calculated total
                } else {
                    taskRow.find('.task-price').val(''); // Clear if input is invalid
                }
                calculatePrices();
            });

            $(document).on('click', '.remove-task', function() {
                const taskId = $(this).data('task-id').toString();
                const taskPrice = $(this).data('price');
                const taskName = $(this).siblings('input[name="tasks[]"]').val();

                $(`#task-${taskId}`).remove();

                selectedTasks = selectedTasks.filter(task => task !== taskId);
                // Re-add the removed task to the dropdown
                $('#task-dropdown').append(`<option value="${taskId}" data-price="${taskPrice}">${taskName}</option>`);
                calculatePrices();
            });

            $('#total_m2 , #kilometer').on('input', calculatePrices);

            function calculateTotalTaskPrice() {
                let totalTask = 0;
                document.querySelectorAll('.task-price').forEach(input => {
                    const value = parseFloat(input.value) || 0;
                    totalTask += value;
                });
                return totalTask
            }

            // Remove task row when minus icon is clicked
            document.addEventListener('click', function (e) {
                if (e.target.closest('.remove-task')) {
                    e.target.closest('.task-row').remove();
                    // taskNumber--
                    calculatePrices();
                }
            });

            function createCheckboxContainers(container, containerName, slots, user_id) {

                if (slots.length !== 0) {
                    container.closest('.accordion-collapse')
                        .prev('.accordion-header')
                        .find('button')
                        .css('background', 'rgba(13, 110, 253, 0.25)');
                }else{
                    container.closest('.accordion-collapse')
                        .prev('.accordion-header')
                        .find('button')
                        .css('background', '#FFFFFF');
                }

                $.each(slots, function(index, slot) {
                    let interval = slot['time'];
                    let noAvailable = !slot['available'];
                    let checkboxId = 'user_' + containerName + '_' + user_id + '_slot_' + index;
                    let checkboxLabel = interval;
                    let checkboxDisabled = noAvailable ? 'disabled' : '';

                    let checkboxDiv = $('<div class="app-border rounded-2">');
                    let checkboxInput = $('<input type="checkbox" id="' + checkboxId + '" class="option-input radio" name="time_slot[]" value="' + interval + '" ' + checkboxDisabled + '/>');
                    let checkboxInputLabel = $('<label for="' + checkboxId + '" class="app-label">' + checkboxLabel + '</label>');

                    checkboxDiv.append(checkboxInput);
                    checkboxDiv.append(checkboxInputLabel);

                    if (noAvailable) {
                        checkboxDiv.attr('title', 'Already Booked');
                        checkboxDiv.addClass('booked_slot');
                        checkboxInput.addClass('booked');
                    }

                    container.append(checkboxDiv);
                });
            }
            function selectAppointmentType(appointment_type_id, selected_date, user_id, base_url)
            {

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    }
                });
                $.ajax({
                    type: "POST",
                    url: base_url + '/test_appointment_types/get_time_slots',
                    data: {
                        'appointment_type_id' : appointment_type_id,
                        'selected_date' : selected_date,
                        'user_id': user_id
                    },
                    cache: false,
                    success: function(data){
                        let available_hours = JSON.parse(data);
                        let intervals = JSON.parse(available_hours);
                        let nightContainer = $('#user_night_' + user_id);
                        let dayContainer = $('#user_day_' + user_id);
                        let eveningContainer = $('#user_evening_' + user_id);
                        let callCheckbox = $('#callCheckbox_' + user_id);
                        callCheckbox.parent().css('display','block');
                        createCheckboxContainers(nightContainer,'nightContainer', intervals.NightSlots, user_id);
                        createCheckboxContainers(dayContainer,'dayContainer', intervals.DaySlots, user_id);
                        createCheckboxContainers(eveningContainer,'eveningContainer', intervals.EveningSlots, user_id);

                    }
                });
            }
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            const appointment_type_id = urlParams.get('appointment_type')

            let skills = document.querySelectorAll('.skills');
            skills.forEach(function(elem) {
                let dataId = elem.getAttribute('data-id');
                let user_id = elem.getAttribute('data-user-id');
                let location_id = elem.getAttribute('data-location-id');
                let appointment_type = elem.getAttribute('data-name');
                if (dataId === appointment_type_id) {
                    $(elem).css('background','#dddddd').removeClass('selected_skill')
                    $(elem).css('background','#48b178').addClass('selected_skill');
                    let $singleCard = $(elem).closest('.single_card');
                    // Move the parent single_card element to the top of the scrollable section
                    $singleCard.prependTo('.scrollable_section');
                    $singleCard.find('#callCheckbox_' + user_id).css('display','block');

                    $('#appointment_type_id').val(appointment_type_id);
                    $('#appointment_type').val(appointment_type);
                    $('#location_id').val(location_id);
                    $('#user_id').val(user_id);
                    let $current_date = $('#current-date');
                    let currentDate = new Date(parseData($current_date.val()));
                    let momentDate = moment(currentDate, "ddd MMM DD YYYY HH:mm:ss [GMT]Z (ZZ)");
                    let selected_date = momentDate.format("YYYY-MM-DD");
                    let base_url = document.location.origin;
                    selectAppointmentType(appointment_type_id, selected_date, user_id, base_url)
                }
            });
        });
    </script>
    <script !src="">
        $(document).on('change', '.slots_section input[type=checkbox]', function() {
            let $this = $(this);
            let $userSection = $this.closest('.slots_section');
            let userId = $this.closest('.slots_section').data('user-id');
            let $allCheckboxesInSection = $userSection.find('input[type=checkbox]:checked');
            let $allUserSections = $('.slots_section');

            if (!$this.prop('disabled')) {
                if ($allCheckboxesInSection.length >= 1) {

                    let startTime = $allCheckboxesInSection[0].value;
                    let endTime = $allCheckboxesInSection[$allCheckboxesInSection.length - 1].value;
                    let endDate = new Date('2000-01-01 ' + endTime + ':00');
                    endDate.setMinutes(endDate.getMinutes() + 15);
                    let formattedEndTime = `${endDate.getHours()}:${String(endDate.getMinutes()).padStart(2, '0')}`;

                    $('#start_time').val(startTime);
                    $('#end_time').val(formattedEndTime);
                    $('#user_id').val(userId);

                    $allUserSections.not($userSection).find('input[type=checkbox]').not('.booked').prop('disabled', true).prop('checked', false);
                    $allUserSections.find('input[type=checkbox]').not('.booked').parent().css('background', '#f5f5f5');
                    $allCheckboxesInSection.parent().css('background', '#48b178');
                } else {
                    $('#start_time').val('');
                    $('#end_time').val('');
                    $('#user_id').val('');

                    $allUserSections.find('input[type=checkbox]').not('.booked').prop('disabled', false);
                    $allUserSections.find('input[type=checkbox]').not('.booked').parent().css('background', '#f5f5f5');
                }
            }
        });
        $(function () {
            $('.slots_section').on('click', '[type=checkbox]', function (e) {
                let $el = $(e.currentTarget),
                    $checkboxes = $el.closest('.slots_section').find('[type=checkbox]'),
                    $checkedEls = $checkboxes.filter(':checked').filter(function (index, el) {
                        return el !== $el[0];
                    }),
                    elIndex = $checkboxes.index($el),
                    firstCheckedIndex = $checkboxes.index($checkedEls.first()),
                    lastCheckedIndex = $checkboxes.index($checkedEls.last());

                return !$checkedEls.length
                    || (elIndex === firstCheckedIndex - 1 || elIndex === lastCheckedIndex + 1);
            });
        });

        let myButton = document.getElementById("btn-back-to-top");

        window.onscroll = function () {
            scrollFunction();
        };

        function scrollFunction() {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                myButton.children[0].style.transform = "rotate(180deg)";
            } else {
                myButton.children[0].style.transform = "rotate(0deg)";
            }
        }

        myButton.addEventListener("click", function() {
            if (document.body.scrollTop === 0 && document.documentElement.scrollTop === 0) {
                // If at the top of the page, scroll down
                window.scrollBy(0, window.innerHeight);
            } else {
                // If at the bottom of the page, scroll to the top
                document.body.scrollTop = 0;
                document.documentElement.scrollTop = 0;
            }
        });

        let date = document.getElementById('dateInput');

        function checkValue(str, max) {
            if (str.charAt(0) !== '0' || str === '00') {
                let num = parseInt(str);
                if (isNaN(num) || num <= 0 || num > max) num = 1;
                str = num > parseInt(max.toString().charAt(0)) && num.toString().length === 1 ? '0' + num : num.toString();
            }
            return str;
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let checkboxSections = document.querySelectorAll('.callCheckboxSection');

            checkboxSections.forEach(function (checkboxSection) {
                let checkbox = checkboxSection.querySelector('input[name="call_checkbox"]');
                checkbox.addEventListener('change', function () {
                    document.getElementById('call_checkbox_input').value = !!checkbox.checked;
                    document.getElementById('start_time').required = !checkbox.checked
                    document.getElementById('start_time').placeholder = (checkbox.checked ? "Does not apply " : "Start time")
                    document.getElementById('end_time').required = !checkbox.checked
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ratesSection = document.querySelector('.rates_section');
            const skillDivs = ratesSection.querySelectorAll('.skills');

            function applyStyles() {
                if (window.innerWidth <= 768) { // Mobile version threshold, adjust as needed
                    if (skillDivs.length > 2) {
                        ratesSection.style.maxHeight = (skillDivs[0].offsetHeight+10) * 2 + 'px'; // Adjust this height as needed
                        ratesSection.style.overflow = 'auto';
                    } else {
                        ratesSection.style.maxHeight = '';
                        ratesSection.style.overflow = '';
                    }
                } else {
                    if (skillDivs.length > 4) {
                        ratesSection.style.maxHeight = (skillDivs[0].offsetHeight+10) * 4 + 'px'; // Adjust this height as needed
                        ratesSection.style.overflow = 'auto';
                    } else {
                        ratesSection.style.maxHeight = '';
                        ratesSection.style.overflow = '';
                    }
                }
            }

            applyStyles();

            window.addEventListener('resize', applyStyles);
        });
    </script>
    <script>
        $('#files').change(handleFileSelect);
    </script>
@endsection
