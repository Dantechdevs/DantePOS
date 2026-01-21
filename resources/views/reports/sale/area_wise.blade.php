@extends('layouts.layout')
@section('title', '| Areawise Sales Report')
@section('content')
@section('custom_styles')
    <link rel="stylesheet" href="{{ asset('css/css/report_page.css') }}">
@endsection
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Areawise Sales Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Areawise Sales Report</li>
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
                    <form id="purchaseForm">
                        <div class="form-group row">

                            <div class="col-sm-4">
                                <label for="area_id" class="form-label">Areas <font class="required_field">*</font></label>
                                <select id="area_id" class="form-control select2 form-control-sm"
                                    style="width: 100%;">
                                    <option disabled="true" selected>-Select-</option>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area['id'] }}">{{ $area['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>



                        <div class="form-group text-right">
                            <button type="button" id="searchButton" data-url="{{ route('report.areawise.sales.list') }}"
                                class="btn btn-info">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>


            <div class="card card-dark mt-3">

                <div class="card-body table-responsive" id="resultsContainer" {{-- style="overflow-y: auto; max-height: 400px;" --}}>
                    <table class="table table-bordered table-striped table-sm" id="resultsTable">
                        <thead>
                            <tr>
                                <th width="2%">#</th>
                                <th width="20%">customer</th>
                                <th width="20%">mobile</th>
                                <th width="5%">balance</th>
                                <th width="25%">receiving</th>
                            </tr>
                        </thead>
                        <tbody id="resultsBody">
                            <!-- Dynamic Rows Will Be Appended Here -->
                        </tbody>
                    </table>
                    <!-- Loader -->
                    <div id="loader" class="text-center mt-3" style="display: none;">
                        <span class="spinner-border text-primary" role="status"></span>
                        <p>Loading more data...</p>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

@push('custom-script')
    <script src="{{ asset('js/common/global.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script type="module" src="{{ asset('js/reports/area_wise.js') }}"></script>
@endpush

@endsection
