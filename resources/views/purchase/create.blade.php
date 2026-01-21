@extends('layouts.layout')
@section('title', '| Add Purchase')
@section('content')
@section('custom_styles')
    <link rel="stylesheet" href="{{ asset('css/sale/sales.css') }}">
@endsection

<div class="content-wrapper">
    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            @include('purchase.load_purchase_form')
        </div>
    </section>
</div>

@endsection

@push('custom-script')
<script src="{{ asset('js/common/global.js') }}"></script>
<script type="module" src="{{ asset('js/suppliers/supplier.js') }}"></script>
<script type="module" src="{{ asset('js/purchase/new_purchase.js') }}"></script>
<script type="module" src="{{ asset('js/purchase/purchase_calculations.js') }}"></script>
@endpush
