@extends('layouts.layout')
@section('title', '| View Expense Categories')
@section('content')
@section('custom_styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="{{ asset('css/datatables_styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/trash.css') }}">
@endsection
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>View Expense Categories</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Expense Categories</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Manage Expense Categories</h3>
                <a href="{{ route('store.expense.category') }}" class="btn btn-success btn-sm ml-auto">
                    <i class="fas fa-plus"></i> Add Category
                </a>
            </div>


            <div class="card-body">
                <!-- Flash Messages -->
                @include('flash_messages')

                <!-- Products Table -->
                <div class="table-responsive">
                    <table id="expenseCategoryTable" data-url="{{ route('expense.categories.list') }}"
                        class="table table-bordered table-striped table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Created By</th>
                                <th>Action</th>

                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection

@push('custom-script')
<script src="{{ asset('js/common/global.js') }}"></script>
<script type="module" src="{{ asset('js/expenses/category.js') }}"></script>
<!-- DataTables Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
@endpush
