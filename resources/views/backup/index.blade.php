@extends('layouts.layout')
@section('title', '| Backup')
@section('content')
    <style>
        .table-header,
        .table-body>.row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ccc;
            padding: 10px 0;
        }

        .table-header {
            font-weight: bold;
            background-color: #f4f4f4;
            padding: 15px 0;
        }

        .table-body>.row:hover {
            background-color: #f9f9f9;
        }

        .col {
            flex: 1;
            text-align: left;
            padding: 0 10px;
        }

        .text-danger {
            color: red;
        }

        .text-success {
            color: green;
        }

        #resultsContainer {
            overflow-y: auto;
            /* Enable scrolling */
            max-height: 400px;
            /* Fixed height for scroll area */
            border: 1px solid #ddd;
            padding: 10px;
        }

        #resultsContainer::-webkit-scrollbar {
            display: none;
            /* Hide scrollbar for modern browsers */
        }
        .select2-container .select2-selection--single {
        height: 30px !important;
        /* Match the input field size */
        font-size: 14px;
        /* Ensure the font size matches other inputs */
        line-height: 30px !important;
        /* Align the text vertically */
        padding: 2px 8px;
        /* Add some padding */
        border-radius: 6px;
        /* Match the rounded corners */
        border: 1px solid #ced4da;
        /* Default border */
        background-color: #fff;
        /* Background color */
    }

    .select2-container .select2-selection--single .select2-selection__rendered {
        padding-left: 0 !important;
        /* Align text with the input */
        font-size: 14px;
        /* Font size consistency */
        color: #212529;
        /* Text color */
    }

    .select2-container .select2-selection--single .select2-selection__arrow {
        height: 30px !important;
        /* Match the select height */
        width: 30px;
        /* Maintain consistent size */
    }

    .select2-container--default .select2-selection--single {
        border-color: #ced4da !important;
        /* Default border for Select2 */
    }

    .select2-container--default .select2-selection--single:focus {
        border-color: #007bff !important;
        /* Blue border on focus */
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
        /* Add focus effect */
    }
    </style>
    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Backup</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Backup</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Search Form -->
                <div class="card card-dark">

                    <div class="card-body">
                        <form id="backupForm">
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">From Date</label>
                                <div class="col-sm-4">
                                    <input type="text" placeholder="DD-MM-YYYY" class="form-control form-control-sm" id="startDate"
                                        readonly>
                                </div>
                                <label class="col-sm-2 col-form-label">To Date</label>
                                <div class="col-sm-4">
                                    <input type="text" placeholder="DD-MM-YYYY" class="form-control form-control-sm" id="endDate"
                                        readonly>
                                </div>
                            </div>

                            <div class="form-group text-right">
                                <button type="button" id="backupButton" class="btn btn-info">
                                    <i class="fas fa-download"></i> Backup
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </section>
    </div>

    @push('custom-script')
        <script src="{{ asset('js/backup/backup.js') }}"></script>
    @endpush

@endsection
