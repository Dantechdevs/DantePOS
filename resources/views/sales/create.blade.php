@extends('layouts.layout')
@section('title', '| Add Sale')
@section('content')
@section('custom_styles')
    <link rel="stylesheet" href="{{ asset('css/sale/sales.css') }}">
    <link rel="stylesheet" href="{{ asset('css/css/search_product.css') }}">
    <style>
        /* Compact styles */
        .compact-form .form-group {
            margin-bottom: 0.5rem;
        }

        .compact-form .form-control,
        .compact-form .input-group-text,
        .compact-form .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            height: 30px;
        }

        .compact-form .table th,
        .compact-form .table td {
            padding: 0.3rem;
            font-size: 0.8rem;
        }

        .compact-form .card-body {
            padding: 0.75rem;
        }

        .compact-form .select2-container .select2-selection--single {
            height: 30px !important;
        }

        /* Header bar styles */
        .header-info-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            border-radius: 4px;
        }

        .invoice-search-container {
            flex: 1;
            margin-right: 10px;
        }

        .user-info-container {
            display: flex;
            align-items: center;
        }

        .user-name {
            margin-right: 15px;
            font-weight: bold;
            color: #333;
        }

        .live-clock {
            font-family: monospace;
            background: #e9ecef;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            min-width: 100px;
            text-align: center;
        }

        .customer-balance-container {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .customerBalance {
            font-size: 0.8rem;
            padding: 0.25em 0.4em;
            font-weight: 600;
        }

        /* Marquee effect for stock */
        .marquee-input {
            overflow: hidden;
            white-space: nowrap;
            width: 60px;
            animation: marquee 8s linear infinite;
        }

        @keyframes marquee {
            0% {
                text-indent: 100%
            }

            100% {
                text-indent: -100%
            }
        }
    </style>
@endsection

<div class="content-wrapper compact-form">
    <section class="content">


        <div class="container-fluid renderSalesForm" data-loadFormUrl="{{ route('create.sale') }}">


            @include('sales.load_sale_form')
        </div>
    </section>
</div>
@endsection

@push('custom-script')

<script src="{{ asset('js/common/global.js') }}"></script>

<script type="module" src="{{ asset('js/customer/customer.js') }}"></script>
<script type="module" src="{{ asset('js/sales/new_sale.js') }}"></script>
<script type="module" src="{{ asset('js/sales/sale_calculations.js') }}"></script>

@endpush
