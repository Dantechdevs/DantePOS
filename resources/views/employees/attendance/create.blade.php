@extends('layouts.layout')
@section('title', '| Employees Attendance')
@section('content')
<style type="text/css">
    /* Modern CSS Reset and Variables */
    :root {
        --primary-color: #4361ee;
        --primary-dark: #114190;
        --success-color: #06d6a0;
        --warning-color: #ffd166;
        --danger-color: #ef476f;
        --light-color: #f8f9fa;
        --dark-color: #212529;
        --border-radius: 8px;
        --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s ease;
    }

    /* Enhanced Switch Toggle */
    .switch-toggle {
        width: auto;
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--box-shadow);
        transition: var(--transition);
    }

    .switch-toggle:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    }

    .switch-toggle label:not(.disabled) {
        cursor: pointer;
        padding: 10px 16px;
        font-weight: 500;
        transition: var(--transition);
    }

    .switch-candy {
        background: linear-gradient(145deg, #ffffff, #f5f7fb);
        border: 1px solid #e1e5eb;
    }

    .switch-candy a {
        border: none;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(67, 97, 238, 0.2);
        background-color: var(--primary-color);
        background-image: linear-gradient(to bottom, rgba(255, 255, 255, 0.15), transparent);
        transition: var(--transition);
    }

    .switch-toggle.switch-candy, .switch-light.switch-candy > span {
        background-color: #f0f4ff;
        border-radius: var(--border-radius);
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #e1e5eb;
    }

    /* Status-specific colors */
    .switch-toggle input[value="Present"]:checked ~ a {
        background-color: var(--success-color);
    }

    .switch-toggle input[value="Leave"]:checked ~ a {
        background-color: var(--warning-color);
    }

    .switch-toggle input[value="Absent"]:checked ~ a {
        background-color: var(--danger-color);
    }

    /* Enhanced UI Elements */
    .card {
        border-radius: var(--border-radius);
        border: none;
        box-shadow: var(--box-shadow);
        overflow: hidden;
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        border-bottom: none;
        padding: 1.25rem 1.5rem;
    }

    .card-title {
        font-weight: 600;
        font-size: 1.4rem;
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success-color), #05c493);
        border: none;
        border-radius: 6px;
        padding: 10px 20px;
        font-weight: 500;
        transition: var(--transition);
        box-shadow: 0 2px 6px rgba(6, 214, 160, 0.25);
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(6, 214, 160, 0.35);
    }

    /* Attendance Table Styling */
    .table-responsive {
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--box-shadow);
    }

    .table-sm thead th {
        background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
        border: none;
        color: var(--dark-color);
        font-weight: 600;
        padding: 1rem 0.75rem;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .table-bordered {
        border: 1px solid #e1e5eb;
    }

    .table-bordered th, .table-bordered td {
        border: 1px solid #e1e5eb;
    }

    .table tbody tr {
        transition: var(--transition);
    }

    .table tbody tr:hover {
        background-color: rgba(67, 97, 238, 0.03);
        transform: translateY(-1px);
    }

    /* Status header buttons */
    .present_all, .leave_all, .absent_all {
        color: white !important;
        font-weight: 600;
        transition: var(--transition);
        cursor: pointer;
        padding: 10px 5px;
    }

    .present_all:hover {
        background-color: rgba(6, 214, 160, 0.9) !important;
    }

    .leave_all:hover {
        background-color: rgba(255, 209, 102, 0.9) !important;
    }

    .absent_all:hover {
        background-color: rgba(239, 71, 111, 0.9) !important;
    }

    .present_all {
        background-color: var(--success-color) !important;
    }

    .leave_all {
        background-color: var(--warning-color) !important;
    }

    .absent_all {
        background-color: var(--danger-color) !important;
    }

    /* Form Styling */
    .form-control {
        border-radius: 6px;
        border: 1px solid #d1d9e6;
        padding: 0.6rem 0.75rem;
        transition: var(--transition);
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.15);
    }

    .form-group label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Badge for employee wage type */
    .wage-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        background-color: #e9ecef;
        color: #495057;
        letter-spacing: 0.3px;
    }

    /* Animation for attendance changes */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }

    .attendance-changed {
        animation: pulse 0.5s ease;
    }

    /* Date picker styling */
    #datepicker {
        background-color: white;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%234361ee' class='bi bi-calendar' viewBox='0 0 16 16'%3E%3Cpath d='M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 40px;
        cursor: pointer;
    }

    /* Submit button */
    .submit-btn {
        padding: 12px 30px;
        font-weight: 600;
        font-size: 1rem;
        border-radius: 6px;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        border: none;
        color: white;
        transition: var(--transition);
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(67, 97, 238, 0.35);
    }

    /* Breadcrumb styling */
    .breadcrumb {
        background-color: transparent;
        padding: 0.5rem 0;
    }

    .breadcrumb-item a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition);
    }

    .breadcrumb-item a:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }

    /* Page header styling */
    .content-header h1 {
        font-weight: 700;
        color: var(--dark-color);
        position: relative;
        padding-bottom: 10px;
    }

    .content-header h1:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 4px;
        background: linear-gradient(to right, var(--primary-color), var(--success-color));
        border-radius: 2px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .switch-toggle label:not(.disabled) {
            padding: 8px 12px;
            font-size: 0.85rem;
        }

        .table thead {
            display: none;
        }

        .table, .table tbody, .table tr, .table td {
            display: block;
            width: 100%;
        }

        .table tr {
            margin-bottom: 1rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1rem;
        }

        .table td {
            text-align: right;
            padding-left: 50%;
            position: relative;
            border: none;
            border-bottom: 1px solid #e1e5eb;
        }

        .table td:before {
            content: attr(data-label);
            position: absolute;
            left: 0;
            width: 45%;
            padding-left: 15px;
            font-weight: 600;
            text-align: left;
            color: var(--dark-color);
        }

        .table td:last-child {
            border-bottom: none;
        }
    }
</style>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Employees Attendance</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Employees Attendance</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title m-0">
                            @if(isset($editData))
                            <i class="fas fa-edit mr-2"></i>Edit Employee Attendance
                            @else
                            <i class="fas fa-plus-circle mr-2"></i>Add Employee Attendance
                            @endif
                        </h3>
                        <a href="{{ url('/employees-attendance') }}" class="btn btn-success" style="display: inline-block;">
                            <i class="fa fa-list mr-1"></i>Attendance List
                        </a>
                    </div>
                    <!-- /.card-header -->
                    <form method="post" action="{{url('/add-employee-attendance')}}" id="attendanceForm">@csrf
                        @if(isset($editData))
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label"><i class="far fa-calendar-alt mr-2"></i>Attendance Date</label>
                                        <input type="text" value="{{date('d-m-Y',strtotime($editData['0']['date']))}}" name="date" id="datepicker" class="form-control form-control-sm datepicker-input">
                                    </div>
                                </div>
                                <div class="col-md-8 d-flex align-items-end">
                                    <div class="alert alert-info mb-0 w-100 py-2">
                                        <small><i class="fas fa-info-circle mr-1"></i> You are editing attendance for <strong>{{date('d F, Y',strtotime($editData['0']['date']))}}</strong>. Click on status buttons to change individual attendance.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="text-center align-middle">SL.</th>
                                            <th rowspan="2" class="text-center align-middle">Employee Name</th>
                                            <th colspan="3" class="text-center" style="width: 30%">Attendance Status</th>
                                        </tr>
                                        <tr>
                                            <th class="text-center present_all">
                                                <i class="fas fa-check-circle mr-1"></i>Present
                                            </th>
                                            <th class="text-center leave_all">
                                                <i class="fas fa-umbrella-beach mr-1"></i>Leave
                                            </th>
                                            <th class="text-center absent_all">
                                                <i class="fas fa-times-circle mr-1"></i>Absent
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($editData as $key => $data)
                                        <tr id="div{{$data->id}}" class="text-center attendance-row">
                                            <input type="hidden" name="employee_id[]" value="{{$data->employee_id}}" class="employee_id">
                                            <td class="align-middle">{{$key+1}}</td>
                                            <td class="align-middle font-weight-bold">{{$data['employee']['name']}}</td>

                                            <td colspan="3" class="align-middle">
                                                <div class="switch-toggle switch-3 switch-candy">
                                                    <input type="radio" class="present" id="present{{$key}}" name="attend_status{{$key}}" value="Present" {{($data->attend_status=='Present')?'checked':''}} />
                                                    <label for="present{{$key}}">Present</label>

                                                    <input type="radio" id="leave{{$key}}" name="attend_status{{$key}}" value="Leave" {{($data->attend_status=='Leave')?'checked':''}} />
                                                    <label for="leave{{$key}}">Leave</label>

                                                    <input type="radio" id="absent{{$key}}" name="attend_status{{$key}}" value="Absent" {{($data->attend_status=='Absent')?'checked':''}} />
                                                    <label for="absent{{$key}}">Absent</label>
                                                    <a></a>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4 d-flex justify-content-between align-items-center">
                                <div class="attendance-summary">
                                    <span class="badge badge-success mr-2"><i class="fas fa-check-circle"></i> Present: <span id="presentCount">{{ count(array_filter($editData->toArray(), function($item) { return $item['attend_status'] == 'Present'; })) }}</span></span>
                                    <span class="badge badge-warning mr-2"><i class="fas fa-umbrella-beach"></i> Leave: <span id="leaveCount">{{ count(array_filter($editData->toArray(), function($item) { return $item['attend_status'] == 'Leave'; })) }}</span></span>
                                    <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Absent: <span id="absentCount">{{ count(array_filter($editData->toArray(), function($item) { return $item['attend_status'] == 'Absent'; })) }}</span></span>
                                </div>
                                <button type="submit" class="btn submit-btn">
                                    <i class="fas fa-save mr-2"></i>{{(@$editData)?'Update Attendance':'Save Attendance'}}
                                </button>
                            </div>
                        </div>
                        @else
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label"><i class="far fa-calendar-alt mr-2"></i>Attendance Date</label>
                                        <input type="text" name="date" id="datepicker" placeholder="DD-MM-YYYY" class="form-control form-control-sm datepicker-input">
                                    </div>
                                </div>
                                <div class="col-md-8 d-flex align-items-end">
                                    <div class="alert alert-light mb-0 w-100 py-2 border">
                                        <small><i class="fas fa-lightbulb mr-1"></i> Select a date and set attendance status for each employee. Use the header buttons to quickly set all employees to the same status.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="text-center align-middle">SL.</th>
                                            <th rowspan="2" class="text-center align-middle">Employee Name</th>
                                            <th colspan="3" class="text-center" style="width: 30%">Attendance Status</th>
                                        </tr>
                                        <tr>
                                            <th class="text-center present_all">
                                                <i class="fas fa-check-circle mr-1"></i>Present
                                            </th>
                                            <th class="text-center leave_all">
                                                <i class="fas fa-umbrella-beach mr-1"></i>Leave
                                            </th>
                                            <th class="text-center absent_all">
                                                <i class="fas fa-times-circle mr-1"></i>Absent
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employees as $key => $employee)
                                        <tr id="div{{$employee->id}}" class="text-center attendance-row">
                                            <input type="hidden" name="employee_id[]" value="{{$employee->id}}" class="employee_id">
                                            <td class="align-middle">{{$key+1}}</td>
                                            <td class="align-middle font-weight-bold">{{$employee->name}}</td>

                                            <td colspan="3" class="align-middle">
                                                <div class="switch-toggle switch-3 switch-candy">
                                                    <input type="radio" class="present" id="present{{$key}}" name="attend_status{{$key}}" value="Present" checked="checked" />
                                                    <label for="present{{$key}}">Present</label>

                                                    <input type="radio" id="leave{{$key}}" name="attend_status{{$key}}" value="Leave" />
                                                    <label for="leave{{$key}}">Leave</label>

                                                    <input type="radio" id="absent{{$key}}" name="attend_status{{$key}}" value="Absent"/>
                                                    <label for="absent{{$key}}">Absent</label>
                                                    <a></a>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4 d-flex justify-content-between align-items-center">
                                <div class="attendance-summary">
                                    <span class="badge badge-success mr-2"><i class="fas fa-check-circle"></i> Present: <span id="presentCount">{{ count($employees) }}</span></span>
                                    <span class="badge badge-warning mr-2"><i class="fas fa-umbrella-beach"></i> Leave: <span id="leaveCount">0</span></span>
                                    <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Absent: <span id="absentCount">0</span></span>
                                </div>
                                <button type="submit" class="btn submit-btn">
                                    <i class="fas fa-save mr-2"></i>Save Attendance
                                </button>
                            </div>
                        </div>
                        @endif
                    </form>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
</div>


@endsection

@push('custom-script')
<script type="text/javascript">
    $(document).ready(function() {
        // Initialize datepicker with better options
        $('#datepicker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            todayHighlight: true,
            orientation: "bottom auto"
        });

        // Update attendance summary counters
        function updateAttendanceCounters() {
            let presentCount = $('input[value="Present"]:checked').length;
            let leaveCount = $('input[value="Leave"]:checked').length;
            let absentCount = $('input[value="Absent"]:checked').length;

            $('#presentCount').text(presentCount);
            $('#leaveCount').text(leaveCount);
            $('#absentCount').text(absentCount);
        }

        // Initialize counters on page load
        updateAttendanceCounters();

        // Attendance radio button click handlers
        $(document).on('click', '.present', function(){
            $(this).parents('tr').find('.datetime').css('pointer-events','none').css('background-color','white').css('color','#495057');
            $(this).parents('tr').addClass('attendance-changed');
            setTimeout(() => {
                $(this).parents('tr').removeClass('attendance-changed');
            }, 500);
            updateAttendanceCounters();
        });

        $(document).on('click', '.leave', function(){
            $(this).parents('tr').find('.datetime').css('pointer-events','').css('background-color','white').css('color','#495057');
            $(this).parents('tr').addClass('attendance-changed');
            setTimeout(() => {
                $(this).parents('tr').removeClass('attendance-changed');
            }, 500);
            updateAttendanceCounters();
        });

        $(document).on('click', '.absent', function(){
            $(this).parents('tr').find('.datetime').css('pointer-events','none').css('background-color','white').css('color','#495057');
            $(this).parents('tr').addClass('attendance-changed');
            setTimeout(() => {
                $(this).parents('tr').removeClass('attendance-changed');
            }, 500);
            updateAttendanceCounters();
        });

        // Bulk attendance setting with visual feedback
        $(document).on('click', '.present_all', function(){
            $("input[value=Present]").prop('checked', true);
            $('.attendance-row').addClass('attendance-changed');
            $('.datetime').css('pointer-events','none').css('background-color','#dee2e6').css('color','#495057');
            setTimeout(() => {
                $('.attendance-row').removeClass('attendance-changed');
            }, 500);
            updateAttendanceCounters();

            // Show notification
            showToast('All employees marked as Present', 'success');
        });

        $(document).on('click', '.leave_all', function(){
            $("input[value=Leave]").prop('checked', true);
            $('.attendance-row').addClass('attendance-changed');
            $('.datetime').css('pointer-events','').css('background-color','white').css('color','#495057');
            setTimeout(() => {
                $('.attendance-row').removeClass('attendance-changed');
            }, 500);
            updateAttendanceCounters();

            // Show notification
            showToast('All employees marked as Leave', 'warning');
        });

        $(document).on('click', '.absent_all', function(){
            $("input[value=Absent]").prop('checked', true);
            $('.attendance-row').addClass('attendance-changed');
            $('.datetime').css('pointer-events','none').css('background-color','#dee2e6').css('color','#495057');
            setTimeout(() => {
                $('.attendance-row').removeClass('attendance-changed');
            }, 500);
            updateAttendanceCounters();

            // Show notification
            showToast('All employees marked as Absent', 'danger');
        });

        // Form validation
        $('#attendanceForm').validate({
            rules: {
                date: {
                    required: true
                }
            },
            messages: {
                date: {
                    required: "Please select an attendance date"
                }
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
                $(element).closest('.form-group').find('.form-control').addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
                $(element).closest('.form-group').find('.form-control').removeClass('is-invalid');
            },
            submitHandler: function(form) {
                // Add loading state to submit button
                const submitBtn = $(form).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Saving...').prop('disabled', true);

                // Simulate save delay for better UX
                setTimeout(() => {
                    form.submit();
                }, 500);
            }
        });

        // Toast notification function
        function showToast(message, type = 'info') {
            const toast = $(`
                <div class="toast-notification toast-${type}">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} mr-2"></i>
                    ${message}
                </div>
            `);

            $('body').append(toast);

            // Add styles for toast
            if (!$('.toast-notification').length) {
                $('<style>')
                    .prop('type', 'text/css')
                    .html(`
                        .toast-notification {
                            position: fixed;
                            top: 20px;
                            right: 20px;
                            padding: 12px 20px;
                            border-radius: 6px;
                            color: white;
                            font-weight: 500;
                            z-index: 9999;
                            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                            transform: translateX(150%);
                            transition: transform 0.3s ease;
                            max-width: 350px;
                        }
                        .toast-success { background: linear-gradient(135deg, var(--success-color), #05c493); }
                        .toast-warning { background: linear-gradient(135deg, var(--warning-color), #ffc043); }
                        .toast-danger { background: linear-gradient(135deg, var(--danger-color), #e63946); }
                        .toast-info { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); }
                    `)
                    .appendTo('head');
            }

            // Show toast with animation
            setTimeout(() => {
                toast.css('transform', 'translateX(0)');
            }, 10);

            // Hide after 3 seconds
            setTimeout(() => {
                toast.css('transform', 'translateX(150%)');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }

        // Add responsive table labels for mobile
        if ($(window).width() <= 768) {
            $('thead th').each(function(i) {
                const label = $(this).text();
                $('tbody td').eq(i).attr('data-label', label);
            });
        }
    });
</script>
@endpush
