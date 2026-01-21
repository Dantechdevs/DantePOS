<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>
        @section('title') Dashboard @show
    </title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bbootstrap 4 -->
    <link rel="stylesheet"
        href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <!-- pace-progress -->
    <!-- <link rel="stylesheet" href="{{ asset('plugins/pace-progress/themes/yellow/pace-theme-flash.css') }}"> -->

    <link rel="stylesheet" href="{{ asset('plugins/pace-progress/themes/black/pace-theme-minimal.css') }}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- JQVMap -->
    <!-- <link rel="stylesheet" href="{{ asset('plugins/jqvmap/jqvmap.min.css') }}"> -->
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attend.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
    <!-- summernote -->
    <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.css"
        rel="stylesheet" />
    <link href="https://unpkg.com/gijgo@1.9.13/css/gijgo.min.css" rel="stylesheet" type="text/css" />
    <!-- <link rel="stylesheet" href="{{ asset('css/yearpicker.css') }}"> -->
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css" />
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <!-- jQuery -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="{{ asset('css/toaster.css') }}">
    <style type="text/css">
        .notifyjs-corner {
            z-index: 10000 !important;
        }

        .product_listing {
            display: none;
        }

        .customLoading {
            margin: 0;
            position: absolute;
            top: 50%;
            left: 50%;
            margin-right: -50%;
            transform: translate(-50%, -50%);
        }
    </style>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js"></script>

    @yield('custom_styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    @if (isset($page_dashboard))
        <style type="text/css">
            .wrapper {
                display: none;
            }

            .loader {
                margin: 0;
                position: absolute;
                top: 50%;
                left: 50%;
                margin-right: -50%;
                transform: translate(-50%, -50%);
            }
        </style>
        <div class="loader">
            <img src="{{ asset('images/spinner.gif') }}">
        </div>
    @endif

    <div class="wrapper">
        @include('layouts.header')
        @include('layouts.sidebar')
        @yield('content')
        @if (session()->has('success'))
            <script type="text/javascript">
                $(function() {
                    $.notify("{{ session()->get('success') }}", {
                        globalPosition: 'top right',
                        className: 'success'
                    });
                });
            </script>
        @endif
        @if (session()->has('error'))
            <script type="text/javascript">
                $(function() {
                    $.notify("{{ session()->get('error') }}", {
                        globalPosition: 'top right',
                        className: 'error'
                    });
                });
            </script>
        @endif
        @include('layouts.footer')

        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->


    <!-- jQuery UI 1.11.4 -->
    <script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <!-- <script src="{{ asset('js/jquery.validate.js') }}"></script> -->

    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- ChartJS -->
    <script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
    <!-- Sparkline -->
    <script src="{{ asset('plugins/sparklines/sparkline.js') }}"></script>
    <!-- JQVMap -->
    <!-- <script src="{{ asset('plugins/jqvmap/jquery.vmap.min.js') }}"></script> -->
    <!-- <script src="{{ asset('plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script> -->
    <!-- jQuery Knob Chart -->
    <script src="{{ asset('plugins/jquery-knob/jquery.knob.min.js') }}"></script>
    <!-- daterangepicker -->
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <!-- Summernote -->
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <!-- overlayScrollbars -->
    <script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <!-- jquery-validation -->
    <script src="{{ asset('plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('plugins/jquery-validation/additional-methods.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('js/adminlte.min.js') }}"></script>
    {{-- <script src="{{ asset('js/admin_scripts.js') }}"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.js"></script>
    <!-- DatePicker------------------------------->

    <script src="https://unpkg.com/gijgo@1.9.13/js/gijgo.min.js" type="text/javascript"></script>
    <!-- <script src="{{ asset('js/yearpicker.js') }}" async></script> -->
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <script src="{{ asset('js/pages/dashboard.js') }}"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="{{ asset('js/demo.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- Datatables----------->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <!-- pace-progress -->
    <script src="{{ asset('plugins/pace-progress/pace.min.js') }}"></script>
    <!-- ChartJS -->
    <script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/handlebars-v4.0.12.js') }}"></script>
    <!-- Datatables----------->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>
    <!-- BootBox for Delete Data ---------->
    <script type="text/javascript"
        src="{{ URL::to('https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js') }}"></script>
    <script>
        $(document).on("click", "#accountdelete", function(e) {
            e.preventDefault();
            var link = $(this).attr("href");
            bootbox.confirm("Are you want to delete!!", function(confirmed) {
                if (confirmed) {
                    window.location.href = link;
                };
            });
        });
    </script>
    <script type="text/javascript">
        $(function() {
            $(".loader").fadeOut(1000, function() {
                $(".wrapper").fadeIn(500);
            });
            $(".customLoading").fadeOut(5000, function() {
                $(".product_listing").fadeIn(1000);
            });
        });
    </script>
    <script>
        @if (Session::has('message'))
            var type = "{{ Session::get('alert-type', 'info') }}"

            switch (type) {
                case 'info':
                    toastr.info("{{ Session::get('message') }}");
                    break;
                case 'success':
                    toastr.success("{{ Session::get('message') }}");
                    break;
                case 'warning':
                    toastr.warning("{{ Session::get('message') }}");
                    break;
                case 'error':
                    toastr.error("{{ Session::get('message') }}");
                    break;
            }
        @endif
    </script>
    <script>
        $(".yearpicker").datepicker({
            format: "yyyy",
            viewMode: "years",
            minViewMode: "years"
        });
    </script>
    <script type="text/javascript">
        $('#datepicker').datepicker({
            uiLibrary: 'bootstrap4',
            format: 'dd-mm-yyyy'
        });
        // $('#startDate').datepicker({
        //     uiLibrary: 'bootstrap4',
        //     format: 'dd-mm-yyyy'
        // });
        // $('#endDate').datepicker({
        //     uiLibrary: 'bootstrap4',
        //     format: 'dd-mm-yyyy'
        // });
    </script>
    <script type="text/javascript">
        window.setTimeout(function() {
            $(".alert").alert('close');
        }, 10000);
    </script>
    <script type="text/javascript">
        $(document).on('click', '.deleteRecord', function(e) {
            var id = $(this).attr('rel');
            // console.log(id);
            var deleteFunction = $(this).attr('rel1');

            swal({
                    title: "Are you sure?",
                    text: "You'll not be able to recover this record again!",
                    type: "warning",
                    showCloseButton: true,
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonColor: 'red',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "Yes, Delete it!"
                },

                function() {
                    window.location.href = "/" + deleteFunction + "/" + id;
                });
        });
    </script>
    <script type="text/javascript">
        $(document).on('click', '.approvePurchase', function(e) {
            var id = $(this).attr('rel');
            // console.log(id);
            var deleteFunction = $(this).attr('rel1');

            swal({
                    title: "Are you sure?",
                    text: "Approve this record!",
                    type: "warning",
                    showCloseButton: true,
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonColor: 'green',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "Yes, Appove it!"
                },

                function() {
                    window.location.href = "/admin/" + deleteFunction + "/" + id;
                });
        });
    </script>
    <script>
        $(function() {
            //Initialize Select2 Elements
            $('.select2').select2();

            //Initialize Select2 Elements
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });



        });
    </script>
    <script>
        $(function() {
            $("#example1").DataTable({
                "responsive": true,
                "autoWidth": false,
            });
            $('#example2').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
            });
        });
    </script>
    @stack('custom-script')
</body>

</html>
