<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Cash Report - {{ $formatted_date }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 15px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #2c3e50;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 4px;
            color: #2c3e50;
        }

        .report-title {
            font-size: 15px;
            margin-bottom: 4px;
            color: #7f8c8d;
        }

        .report-date {
            font-size: 13px;
            color: #95a5a6;
        }

        /* Metrics row as table (reliable for PDF) */
        .metrics-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            table-layout: fixed;
        }

        .metrics-table td {
            width: 25%;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 12px;
            text-align: center;
            background-color: #f9f9f9;
            font-size: 11px;
            vertical-align: middle;
        }

        .metric-title {
            margin: 0 0 6px 0;
            font-size: 11px;
            color: #7f8c8d;
            font-weight: bold;
            text-transform: uppercase;
        }

        .metric-value {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
        }

        .cash-in { color: #27ae60; }
        .cash-out { color: #e74c3c; }

        /* Transactions table */
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        /* Header cells only */
        .transaction-table thead th {
            background-color: #2c3e50;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }

        .transaction-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }

        .transaction-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right { text-align: right !important; }

        /* Footer styling (use td so .text-right applies) */
        .transaction-table tfoot td {
            background-color: #2c3e50;
            color: #ffffff;
            font-weight: 600;
            padding: 8px;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }
        .badge-success { background-color: #27ae60; }
        .badge-danger { background-color: #e74c3c; }

        /* Summary */
        .summary {
            margin-top: 15px;
            padding: 12px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #3498db;
            font-size: 11px;
        }
        .summary strong { color: #2c3e50; }

        /* Footer */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #95a5a6;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            color: #7f8c8d;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-name">{{ $company_name }}</div>
        <div class="report-title">Daily Cash Movement Report</div>
        <div class="report-date">Date: {{ $formatted_date }}</div>
    </div>

    <!-- Metrics row in one table row -->
    <table class="metrics-table">
        <tr>
            <td>
                <div class="metric-title">Opening Balance</div>
                <div class="metric-value">{{ number_format($opening_balance, 2) }}</div>
            </td>
            <td>
                <div class="metric-title">Closing Balance</div>
                <div class="metric-value">{{ number_format($closing_balance, 2) }}</div>
            </td>
            <td>
                <div class="metric-title">Net Change</div>
                <div class="metric-value {{ $net_change >= 0 ? 'cash-in' : 'cash-out' }}">
                    {{ number_format($net_change, 2) }}
                </div>
            </td>
            <td>
                <div class="metric-title">Total Transactions</div>
                <div class="metric-value">{{ count($transactions) }}</div>
            </td>
        </tr>
    </table>

    @if (count($transactions) > 0)
        <table class="transaction-table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="15%">Time</th>
                    <th width="40%">Description</th>
                    <th width="15%">Type</th>
                    <th width="25%" class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $index => $transaction)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('h:i A') }}</td>
                        <td>{{ $transaction->description }}</td>
                        <td>
                            @if ($transaction->type === 'in')
                                <span class="badge badge-success">Cash In</span>
                            @else
                                <span class="badge badge-danger">Cash Out</span>
                            @endif
                        </td>
                        <td class="text-right {{ $transaction->type === 'in' ? 'cash-in' : 'cash-out' }}">
                            {{ number_format($transaction->amount, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right">Total Cash In:</td>
                    <td class="text-right cash-in">{{ number_format($cash_in_total, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-right">Total Cash Out:</td>
                    <td class="text-right cash-out">{{ number_format($cash_out_total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            No transactions found for {{ $formatted_date }}
        </div>
    @endif

    <div class="summary">
        <strong>Summary:</strong><br>
        Opening Balance: {{ number_format($opening_balance, 2) }}<br>
        + Total Cash In: {{ number_format($cash_in_total, 2) }}<br>
        - Total Cash Out: {{ number_format($cash_out_total, 2) }}<br>
        = Closing Balance: {{ number_format($closing_balance, 2) }}
    </div>

    <div class="footer">
        Generated on: {{ $generated_at }}<br>
        {{ $company_name }} - Daily Cash Report
    </div>
</body>

</html>
