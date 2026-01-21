<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>All Customers Credit/Debit Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .table-bordered {
            border: 1px solid #000;
            width: 100%;
            border-collapse: collapse;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .customer-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .customer-section h5 {
            margin-bottom: 10px;
        }

        .total-row {
            font-weight: bold;
            background-color: #FBCEB1;
        }
    </style>
</head>

<body>
    <div class="header">
        <h4>All Customers Credit/Debit Report</h4>
        <p>Date Range: {{ date('M d, Y', strtotime($startDate)) }} to {{ date('M d, Y', strtotime($endDate)) }}</p>
    </div>

    @foreach ($reportData as $report)
        <div class="customer-section">
            <h5>Customer: {{ $report['customer']['name'] }}</h5>
            <p>Total Balance:
                {{ $report['totalBalance'] < 0 ? -1 * $report['totalBalance'] . ' DB' : $report['totalBalance'] . ' CR' }}
            </p>

            <table class="table table-bordered">
                <thead>
                    <tr>

                        <th width="5%">#</th>
                        <th width="10%">VCH#</th>
                        <th width="30%">Date</th>
                        <th width="15%">Debit</th>
                        <th width="15%">Credit</th>
                        <th width="25%">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5">Opening Balance</td>
                        <td>
                            @if ($report['openingBalance'] == 0)
                                0
                            @else
                                {{ $report['openingBalance'] < 0 ? -1 * $report['openingBalance'] . ' DB' : $report['openingBalance'] . ' CR' }}
                            @endif
                        </td>
                    </tr>
                    <?php $counter = 0; ?>
                    @foreach ($report['transactions'] as $transaction)
                        <?php $counter = $counter + 1; ?>
                        <tr>
                            <td>{{ $counter }}</td>
                            <td>{{ $transaction['invoice_no'] }}</td>
                            <td>{{ date('d-m-Y | H:i:s a', strtotime($transaction['date'])) }}</td>

                            <td>{{ $transaction['debit'] !== '' && $transaction['debit'] !== null ? $transaction['debit'] : '-' }}
                            </td>
                            <td>{{ $transaction['credit'] !== '' && $transaction['credit'] !== null ? $transaction['credit'] : '-' }}
                            </td>
                            <td>
                                @if ($transaction['balance'] == 0)
                                    0
                                @else
                                    {{ $transaction['balance'] }}
                                @endif
                            </td>
                        </tr>
                        @if (isset($transaction['description']))
                            <tr>
                                <td colspan="12" class="text-left p-text"> {{ $transaction['description'] }}</td>
                            </tr>
                        @endif
                    @endforeach
                    <tr class="total-row">
                        <td colspan="5">Closing Balance</td>
                        <td>
                            @if ($report['totalBalance'] == 0)
                                0
                            @else
                                {{ $report['totalBalance'] < 0 ? -1 * $report['totalBalance'] . ' DB' : $report['totalBalance'] . ' CR' }}
                            @endif
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>
    @endforeach
</body>

</html>
