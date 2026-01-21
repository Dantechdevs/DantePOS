@extends('layouts.layout')
@section('title', '| Sale Invoice')
@section('content')
    <style>
        /* General Styling */
        body {
            font-family: "Segoe UI", Tahoma, sans-serif;
            background-color: #f8f9fa;
        }

        .content-wrapper {
            padding: 20px;
        }

        .invoice-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        /* Header Section */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }

        .invoice-logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-wrapper {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logo-wrapper.selected .logo-circle {
            border-color: #007bff;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
            transform: scale(1.1);
        }

        .logo-wrapper.selected .checkmark {
            display: block;
        }

        .logo-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #ddd;
            position: relative;
        }

        .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .checkmark {
            position: absolute;
            top: -10px;
            right: -10px;
            display: none;
            color: #007bff;
            font-size: 18px;
        }

        .invoice-date {
            color: #666;
            font-size: 0.9rem;
            text-align: right;
        }

        /* Invoice Info */
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .invoice-col {
            flex: 1;
            margin-right: 20px;
        }

        .invoice-col:last-child {
            margin-right: 0;
        }

        .invoice-col address {
            font-style: normal;
            font-size: 0.9rem;
            line-height: 1.6;
            color: #555;
        }

        /* Table Section */
        .invoice-table {
            margin-bottom: 20px;
        }

        .invoice-table thead {
            background: #007bff;
            color: #fff;
        }

        .invoice-table th,
        .invoice-table td {
            text-align: center;
            padding: 10px;
        }

        .invoice-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .invoice-totals {
            font-weight: bold;
            background-color: #f2f2f2;
        }

        /* Print Button */
        .no-print {
            margin-top: 20px;
            text-align: right;
        }


        .btn-print {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .btn-print:hover {
            background-color: black;
            color: white;
        }
    </style>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Sale Invoice</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Sale Invoice</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="invoice-container">
                <div class="invoice-header">
                    <div class="invoice-logo">
                        <div id="logoContainer" style="display: flex; gap: 20px;">
                            <!-- Logo 1 -->
                            <div class="logo-wrapper" data-logo="invoice_logo">
                                <div class="logo-circle">
                                    <img src="{{ optional($settings)['invoice_logo'] && file_exists(public_path(optional($settings)['invoice_logo']))
                                        ? asset(optional($settings)['invoice_logo'])
                                        : (optional($settings)['default_image'] && file_exists(public_path(optional($settings)['default_image']))
                                            ? asset(optional($settings)['default_image'])
                                            : asset('images/default-image.png')) }}"
                                        alt="Logo">
                                    <div class="checkmark"><i class="fas fa-check-circle"></i></div>
                                </div>
                            </div>
                            <!-- Logo 2 -->
                            <div class="logo-wrapper" data-logo="invoice_logo2">
                                <div class="logo-circle">
                                    <img src="{{ optional($settings)['invoice_logo2'] && file_exists(public_path(optional($settings)['invoice_logo2']))
                                        ? asset(optional($settings)['invoice_logo2'])
                                        : (optional($settings)['default_image'] && file_exists(public_path(optional($settings)['default_image']))
                                            ? asset(optional($settings)['default_image'])
                                            : asset('images/default-image.png')) }}"
                                        alt="Invoice Logo2">

                                    <div class="checkmark"><i class="fas fa-check-circle"></i></div>
                                </div>
                            </div>
                        </div>
                        {{-- <span style="font-size: 1.5rem; font-weight: bold; color: #333;">{{ optional($settings)['site_name'] }}</span> --}}
                    </div>
                    <div class="invoice-date">
                        <span>Invoice Date:</span> {{ date('d/m/Y | h:i A', strtotime($saleInvoice['date'])) }}
                    </div>
                </div>

                <div class="invoice-info">
                    <div class="invoice-col">
                        <strong>From:</strong>
                        <address>
                            {{ optional($settings)['site_name'] }}<br>
                            Address: {{ optional($settings)['site_address'] }}<br>
                            @foreach (explode(',', optional($settings)['mobile_numbers']) as $mobile)
                                Phone: {{ trim($mobile) }}<br>
                            @endforeach
                        </address>
                    </div>

                    <div class="invoice-col">
                        <strong>Customer:</strong>
                        <address>
                            {{ optional($saleInvoice['customer'])['name'] }}<br>
                            Phone: {{ optional($saleInvoice['customer'])['mobile'] }} <br>

                            Address: {{ optional($saleInvoice['customer'])['address'] }}
                        </address>
                    </div>
                    <div class="invoice-col">
                        <strong>Invoice #:</strong> {{ $saleInvoice['invoice_no'] }}<br>
                        <p><strong>Status:</strong>
                            {{ $saleInvoice['status'] == 0
                                ? 'Canceled'
                                : ($saleInvoice['status'] == 1
                                    ? 'Billed'
                                    : ($saleInvoice['status'] == 2
                                        ? 'Pending'
                                        : ($saleInvoice['status'] == 3
                                            ? 'Return'
                                            : 'Unknown'))) }}
                        </p>



                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-sm invoice-table">
                        <thead>
                            <tr>
                                <th>#</th>

                                <th class="text-left">Product</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-right">Item Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $counter = 0;
                            ?>
                            @foreach ($saleitmesAddons as $addons)
                                <?php
                                $productName = App\Models\Product::find($addons['product_id'])->name ?? 'Unknown Product';
                                $counter = $counter + 1;
                                ?>
                                <tr>
                                    <td width="6%">{{ $counter }}</td>
                                    <td width="8%" class="text-left">
                                        {{ $productName }}
                                    </td>
                                    <td width="7%" class="text-center">{{ $addons['selling_price'] }}
                                    </td>
                                    <td width="7%" class="text-center">{{ $addons['quantity'] }} {{ $addons['unit'] }}
                                    </td>
                                    <td width="7%" class="text-right">
                                        {{ number_format($addons['amount'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                            @if ($saleInvoice['sub_total'] > 0 && ($saleInvoice['discount'] > 0 || $saleInvoice['other_charges'] > 0))
                                <tr>
                                    <td colspan="4"
                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                        Sub Total:
                                    </td>
                                    <td style="text-align: right; background: white; font-weight: bold; color:black;">
                                        {{ number_format($saleInvoice['sub_total'], 2) }}
                                    </td>
                                </tr>
                            @endif
                            @if ($saleInvoice['other_charges'] > 0)
                                <tr>
                                    <td colspan="4"
                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                        Other Charges:
                                    </td>
                                    <td style="text-align: right; background: white; font-weight: bold; color:black;">
                                        {{ number_format($saleInvoice['other_charges'], 2) }}
                                    </td>
                                </tr>
                            @endif
                            @if ($saleInvoice['discount'] > 0)
                                <tr>
                                    <td colspan="4"
                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                        Discount @if (isset($saleInvoice['discount_type']))
                                            ({{ ucfirst($saleInvoice['discount_type']) }}) :
                                        @endif
                                    </td>
                                    <td style="text-align: right; background: white; font-weight: bold; color:black;">
                                        @if (isset($saleInvoice['discount_type']) && $saleInvoice['discount_type'] === 'percentage')
                                            ({{ number_format($saleInvoice['discount']) }}%)
                                            {{ number_format($saleInvoice['discount_amount']) }}
                                        @else
                                            {{ number_format($saleInvoice['discount']) }}
                                        @endif
                                    </td>
                                </tr>
                            @endif

                            <tr>
                                <td colspan="4"
                                    style="text-align: right; background: white; font-weight: bold; color:black;">
                                    Total:
                                </td>
                                <td style="text-align: right; background: white; font-weight: bold; color:black;">
                                    {{ number_format($saleInvoice['grand_total'], 2) }}
                                </td>
                            </tr>
                            @if ($saleInvoice['paid_amount'] > 0)
                                <tr>
                                    <td colspan="4"
                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                        Paid:
                                    </td>
                                    <td style="text-align: right; background: white; font-weight: bold; color:black;">
                                        {{ number_format($saleInvoice['paid_amount'], 2) }}
                                    </td>
                                </tr>
                            @endif

                            @if ($saleInvoice['balance_amount'] > 0)
                                <tr>
                                    <td colspan="4"
                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                        Due:
                                    </td>
                                    <td style="text-align: right; background: white; font-weight: bold; color:black;">
                                        {{ number_format($saleInvoice['balance_amount'], 2) }}
                                    </td>
                                </tr>
                            @endif

                            @if ($saleInvoice['change_amount'] > 0)
                                <tr>
                                    <td colspan="4"
                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                        Refund:
                                    </td>
                                    <td style="text-align: right; background: white; font-weight: bold; color:black;">
                                        {{ number_format($saleInvoice['change_amount'], 2) }}
                                    </td>
                                </tr>
                            @endif

                            @if ($customerBalance > 0)
                                @php

                                    $preblnc = $customerBalance;
                                @endphp
                                <tr>
                                    <td colspan="4"
                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                        Previous Balance:
                                    </td>
                                    <td style="text-align: right; background: white; font-weight: bold; color:black;">
                                        {{ number_format($preblnc, 2) }}
                                    </td>
                                </tr>
                            @endif
                            @if ($customerBalance > 0)
                                @php
                                    $customerBalance =
                                        $customerBalance +
                                        ($saleInvoice['balance_amount'] > 0 ? $saleInvoice['balance_amount'] : 0);
                                @endphp
                                <tr style="text-align: right; background: gray; font-weight: bold; color:white;">
                                    <td colspan="4" style="text-align: right; font-weight: bold;">
                                        Total Balance:
                                    </td>
                                    <td style="text-align: right; font-weight: bold;">
                                        {{ number_format($customerBalance, 2) }}
                                    </td>
                                </tr>
                            @endif

                            @if ($saleInvoice['description'])
                                <tr>
                                    <td>
                                        <span><b>Note:</b></span>
                                        <p>
                                            {{ $saleInvoice['description'] }}
                                        </p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="no-print">
                    <a id="printInvoiceButton" href="{{ route('print.invoice', [$saleInvoice['id'], 'logo' => '']) }}"
                        target="_blank" class="btn-print">
                        <i class="fas fa-print"></i> Print
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('custom-script')
    <script>
        // Get the print button
        const printButton = document.getElementById('printInvoiceButton');

        // Add click event listener to update the href dynamically
        document.querySelectorAll('.logo-wrapper').forEach(wrapper => {
            wrapper.addEventListener('click', function() {
                // Remove "selected" class from all wrappers
                document.querySelectorAll('.logo-wrapper').forEach(el => el.classList.remove('selected'));

                // Add "selected" class to the clicked wrapper
                this.classList.add('selected');

                // Get the selected logo
                const selectedLogo = this.getAttribute('data-logo');

                // Update the href with the selected logo as a parameter
                const currentHref =
                    "{{ route('print.invoice', [$saleInvoice['id'], 'logo' => '__LOGO__']) }}";
                printButton.href = currentHref.replace('__LOGO__', selectedLogo);
            });
        });

        // Preselect the logo and set the print button URL on page load
        const preselectedLogo = "{{ optional($settings)['selected_logo'] ?? 'invoice_logo' }}";
        const preselectedElement = document.querySelector(`.logo-wrapper[data-logo="${preselectedLogo}"]`);
        if (preselectedElement) {
            preselectedElement.classList.add('selected');
            const currentHref = "{{ route('print.invoice', [$saleInvoice['id'], 'logo' => '__LOGO__']) }}";
            printButton.href = currentHref.replace('__LOGO__', preselectedLogo);
        }
    </script>
@endpush
