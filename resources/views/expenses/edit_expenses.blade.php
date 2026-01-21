@extends('layouts.layout')
@section('title', '| Edit Expense')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Expense</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Edit Expense</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid col-md-10">
            @include('flash_messages')

            <form id="expenseForm" action="{{ route('update.expense', $editExpense['id']) }}" method="POST">
                @csrf

                <div class="card card-dark">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Edit Expense</h3>
                        <a href="{{ route('expenses') }}" class="btn btn-warning btn-sm">
                            <i class="fa fa-list"></i> Expense List
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="datepicker">Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="date" id="datepicker"
                                        value="{{ date('d-m-Y', strtotime($editExpense['date'])) }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="exp_category_id">Category <span class="text-danger">*</span></label>
                                    <select name="exp_category_id" id="exp_category_id" class="form-control select2">
                                        <option selected disabled>- Select -</option>
                                        @foreach ($expCategories as $category)
                                            <option value="{{ $category['id'] }}"
                                                {{ $editExpense['exp_category_id'] == $category['id'] ? 'selected' : '' }}>
                                                {{ $category['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="expense_for">Expense For <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="expense_for" id="expense_for"
                                        placeholder="Enter purpose of expense" value="{{ old('expense_for', $editExpense['expense_for']) }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="amount">Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" name="amount" id="amount"
                                        placeholder="Enter amount" value="{{ old('amount', $editExpense['amount']) }}" required>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="note">Note</label>
                                    <textarea class="form-control" id="note" name="note" rows="4"
                                        placeholder="Enter any additional notes...">{{ old('note', $editExpense['note']) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-info">Update Expense</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-warning">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
