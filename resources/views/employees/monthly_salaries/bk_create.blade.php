@extends('layouts.layout')
@section('title', '| Employees Monthly Salaries')
@section('content')

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Employees Monthly Salaries</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Employees Monthly Salaries</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-12">

                    <form action="{{ url('/pay-employee-mothly-salary') }}" method="post">@csrf
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">View Employees</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label class="control-label">Date</label>
                                        <input type="date" name="date" id="date"
                                            class="form-control form-control-sm">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <a class="btn btn-sm btn-success" id="search"
                                            style="margin-top: 29px; color: white">Search</a>
                                    </div>
                                </div>

                            </div>
                            <div class="card-body">
                                <div id="DocumentResults"></div>
                                <script id="document-template" type="text/x-handlebars-template">
             <table class="table-sm table-bordered table-striped" style="width: 100%">
              <thead>
                <tr>
                  @{{{thsource}}}
                </tr>
              </thead>
              <tbody>
                @{{#each this}}
                <tr>
                  @{{{tdsource}}}
                </tr>
                @{{/each}}
              </tbody>
            </table>
            </script>
                            </div>
                            <div class="card-footer">
                                <div>
                                    <button type="submit" class="btn btn-success btn-sm" style="float: right;">Pay
                                        Salary</button>
                                </div>
                                <div style="margin-right: 150px;">
                                    <a href="{{ '/dashboard' }}" class="btn btn-warning btn-sm"
                                        style="float: right;">Cancel</a>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </form>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>

@endsection

@push('custom-script')
    <script type="text/javascript" src="{{ asset('js/handlebars-v4.0.12.js') }}"></script>
    <script type="text/javascript">
        $(document).on('click', '#search', function() {
            var date = $("#date").val();
            $('.notifyjs-corner').html('');
            if (date == '') {
                $.notify("Date required", {
                    globalPsoition: 'top right',
                    className: 'error'
                });
                return false;
            }
            $.ajax({
                url: '/employee-monthly-salary-datewise-get',
                type: 'GET',
                data: {
                    date: date
                },
                beforSend: function() {

                },
                success: function(data) {
                    var source = $("#document-template").html();
                    var template = Handlebars.compile(source);
                    var html = template(data);
                    $('#DocumentResults').html(html);
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });
        });
    </script>
@endpush
