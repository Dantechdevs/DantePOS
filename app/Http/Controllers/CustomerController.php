<?php

namespace App\Http\Controllers;

use App\CustomerDiscount;
use App\CustomerPayment;
use App\Http\Requests\CustomerPaymentRequest;
use App\Http\Requests\CustomerRequest;
use Illuminate\Http\Request;
use Exception;
use App\Models\Customer;
use App\Models\Area;
use App\Models\Sale;
use App\Models\CustomerOpeningBalance;
use App\Models\CustomerPaymentReceipt;
use App\Services\SaleService;
use Carbon\Carbon;
use PDF;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;
use Illuminate\Support\HtmlString;

class CustomerController extends Controller
{
    protected $saleService;

    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }
    /********************************************************************/
    public function authenticateRole($module_page)
    {
        $permissionCheck =  checkRolePermission($module_page);
        if ($permissionCheck->access == 0) {

            return redirect()->to('/dashboard')->send()->with('error', 'You have no permission!');
        }
    }
    /********************************************************************/
    public function index()
    {
        $this->authenticateRole($module_page = 'customers');
        Session::put('page', 'customers');
        $customers = Customer::with('user')->orderBy('id', 'DESC')->get()->toArray();
        return view('customers.view', compact('customers'));
    }
    /********************************************************************/
    public function customersList(Request $request)
    {
        if ($request->ajax()) {
            $data = Customer::with(['user:id,name'])->select('id', 'name', 'mobile', 'opening_balance', 'createdBy')
                ->orderBy('id', 'desc'); // Order by 'id' in descending order
            // ->get();

            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('balance', function ($row) {

                    // $balance = ($this->saleService->calculateTotalAmount($row->id) + $this->saleService->calculateCustomerBalance($row->id, 'debit')) - $this->saleService->calculateCustomerBalance($row->id, 'credit');
                    $balance = $this->saleService->customerTotalBalance($row->id);
                    $balance = $balance < 0
                        ? -1 * $balance . ' CR'
                        : ($balance > 0 ? $balance . ' DB' : $balance);
                    return $balance; // Format the date as 'dd-MMM-YYYY'
                })

                ->addColumn('attachments', function ($row) {
                    // Button to view attachments
                    return '
                    <button class="btn btn-info btn-sm view-attachments"
                            data-url="' . route('customer.attachments', $row->id) . '"
                            data-customer-name="' . $row->name . '"
                            title="View Attachments">
                        <i class="fas fa-paperclip"></i> View Attachments
                    </button>';
                })

                ->addColumn('createdBy', function ($row) {
                    return $row->user ? $row->user->name : ''; // Format the date as 'dd-MMM-YYYY'
                })
                 ->addColumn('action', function ($row) {
                // Dropdown action menu
                $actions = '
            <div class="dropdown">
                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="actionMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Action
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="actionMenu">
                <a class="dropdown-item view" href="javascript:void(0);" data-url="' . route('customer.view', $row->id) . '" data-id="' . $row->id . '" title="View Customer">
                        <i class="fas fa-eye text-green"></i> View
                    </a>
                    <a class="dropdown-item editCustomer" href="javascript:void(0);" data-url="' . route('load.customer.form', $row->id) . '" data-saveCustomerUrl="' . route('create.customer', $row->id) . '">
                        <i class="fas fa-pen text-blue"></i> Edit
                    </a>
                    <a class="dropdown-item delete" href="javascript:void(0);" data-url="' . route('delete.customer', $row->id) . '" data-id="' . $row->id . '" title="Delete Customer">
                        <i class="fas fa-trash text-red"></i> Delete
                    </a>';

                // Only show "Pay Due Payments" for superadmin
                if (Auth::user()->user_type === 'superadmin') {
                    $actions .= '
                    <a class="dropdown-item payDuePayment" href="javascript:void(0);" data-url="' . route('customer.payment.modal', $row->id) . '">
                        <i class="fas fa-money-bill text-yellow"></i> Pay Due Payments
                    </a>';
                }

                $actions .= '</div></div>';
                return $actions;
            })

                ->rawColumns(['action', 'attachments']) // Ensure raw HTML rendering for the 'action' column
                ->make(true);
        }
    }
    /********************************************************************/
    public function getAttachments($id)
    {
        try {
            $customer = Customer::findOrFail($id);

            // Assuming you have an attachments relationship in your Customer model
            $attachments = $customer->attachments()->get()->map(function ($attachment) {
                // echo "<pre>"; print_r($attachment->receitpt_path); "</pre>"; exit;
                // echo "<pre>"; print_r(asset($attachment->receitpt_path)); "</pre>"; exit;
                return [
                    'id' => $attachment->id,
                    'file_name' => $attachment->receitpt_path,
                    'file_url' => asset($attachment->receitpt_path), // Adjust path as needed
                    'created_at' => $attachment->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'attachments' => $attachments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching attachments'
            ], 500);
        }
    }
    /********************************************************************/
    public function showPaymentModal($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $partialOP_Balance = $this->saleService->partialPaidCustomerOP_Balance($customerId);
        $dueOP_Balance = $customer->opening_balance - $partialOP_Balance;
        $balance = $this->saleService->customerTotalBalance($customerId);
        $totalSales = $this->saleService->calculateTotalAmount($customerId);
        $paidAmount = $this->saleService->invoicePaidAmount($customerId);
        $partialPaid = $this->saleService->partialPaidAmount($customerId);
        $dueAmount = ($totalSales + $customer->opening_balance) - ($paidAmount + $partialPaid  + $partialOP_Balance);
        $dueSalesAmount = $totalSales - ($paidAmount + $partialPaid);

        return view('customers.includes.pay_due_payment_modal', [
            'customer' => $customer,
            'openingBalance' => $customer->opening_balance,
            'dueOP_Balance' => $dueOP_Balance,
            'totalSales' => $totalSales,
            'paidAmount' => ($paidAmount + $partialPaid),
            'dueAmount' => $dueAmount,
            'dueSalesAmount' => $dueSalesAmount,
            'balance' => $balance
        ]);
    }
    /********************************************************************/
    public function submitPaymentxxx(Request $request)
    {
        if ($request->ajax()) {
            // echo "<pre>"; print_r($request->all()); "</pre>"; exit;
            try {
                $validated = $request->validate([
                    'customer_id' => 'required|exists:customers,id',
                    'amount' => 'required|numeric|min:0.01',
                    'payment_date' => 'required|date',
                    'notes' => 'nullable|string'
                ]);

                $customerId = $validated['customer_id'];
                $paymentAmount = $validated['amount'];
                $remainingAmount = $paymentAmount;
                $customer = Customer::findOrFail($customerId);

                $totalSales = $this->saleService->calculateTotalAmount($customerId);
                $paidAmount = $this->saleService->invoicePaidAmount($customerId);
                $partialPaid = $this->saleService->partialPaidAmount($customerId);
                $dueAmount = ($totalSales + $customer->opening_balance) - ($paidAmount + $partialPaid);
                if ($remainingAmount > $dueAmount) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Payment amount exceeds the due amount of PKR ' . number_format($dueAmount, 2),
                    ], 400);
                }

                DB::beginTransaction();

                $paymentDetails = [];
                $customer = Customer::find($customerId);
                $openingBalance = $customer->opening_balance ?? 0;

                // 1. First handle opening balance
                if ($openingBalance > 0) {
                    $amountToOpeningBalance = min($openingBalance, $remainingAmount);
                    // dd($amountToOpeningBalance);

                    if ($amountToOpeningBalance > 0) {
                        // dd(date('Y-m-d H:i:s'));
                        // Save to customer_opening_balances table
                        CustomerOpeningBalance::create([
                            'invoice_no' => 'OB-' . time(),
                            'customer_id' => $customerId,
                            'amount' => $amountToOpeningBalance,
                            'date' => date('Y-m-d H:i:s'),
                            'description' => $validated['notes'] ?? "Opening balance payment",
                            'createdBy' => Auth::id()
                        ]);

                        // Update customer's opening balance
                        // $customer->opening_balance -= $amountToOpeningBalance;
                        $customer->save();

                        $paymentDetails[] = [
                            'type' => 'opening_balance',
                            'amount' => $amountToOpeningBalance,
                            'remaining_balance' => $customer->opening_balance
                        ];

                        $remainingAmount -= $amountToOpeningBalance;
                    }
                }

                // 2. Then apply to invoices (oldest first)
                if ($remainingAmount > 0) {
                    $unpaidInvoices = Sale::where('customer_id', $customerId)
                        ->where('status', 1)
                        ->orderBy('created_at', 'asc')
                        ->get();

                    foreach ($unpaidInvoices as $invoice) {
                        if ($remainingAmount <= 0) break;

                        $invoiceDue = $invoice->grand_total - ($this->getPaidAmountForInvoice($invoice->id) + $invoice->paid_amount);

                        if ($invoiceDue > 0) {
                            $amountToInvoice = min($invoiceDue, $remainingAmount);

                            // Save to customer_payments table
                            CustomerPayment::create([
                                'sale_id' => $invoice->id,
                                'amount' => $amountToInvoice,
                                'payment_date' => date('Y-m-d H:i:s'),
                                'notes' => $validated['notes'] ?? "Payment for invoice #{$invoice->id}",
                                'created_by' => Auth::id()
                            ]);

                            // Update invoice payment status
                            $totalPaidNow = $this->getPaidAmountForInvoice($invoice->id) + $amountToInvoice;
                            $paymentStatus = $this->determinePaymentStatus($invoice->grand_total, $totalPaidNow);

                            $invoice->update([
                                'payment_status' => $paymentStatus
                            ]);

                            $paymentDetails[] = [
                                'type' => 'invoice',
                                'invoice_id' => $invoice->id,
                                'amount' => $amountToInvoice,
                                'remaining_due' => $invoiceDue - $amountToInvoice,
                                'payment_status' => $paymentStatus
                            ];

                            $remainingAmount -= $amountToInvoice;
                        }
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'payment_details' => $paymentDetails,
                    'remaining_amount' => $remainingAmount > 0 ? $remainingAmount : 0,
                    'new_opening_balance' => $customer->opening_balance
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Oops, something went wrong. Try again.',
                    'error' => $th->getMessage(),
                ], 500);
            }
        }
    }

    public function submitPayment(Request $request)
    {
        if ($request->ajax()) {
            // echo "<pre>"; print_r($request->all()); "</pre>"; exit;
            try {
                $validated = $request->validate([
                    'customer_id' => 'required|exists:customers,id',
                    'amount' => 'required|numeric|min:0.01',
                    'payment_date' => 'required|date',
                    'notes' => 'nullable|string'
                ]);

                $customerId = $validated['customer_id'];
                $paymentAmount = $validated['amount'];
                $remainingAmount = $paymentAmount;
                $customer = Customer::findOrFail($customerId);

                // Calculate total paid towards opening balance
                $totalOpeningBalancePaid = CustomerOpeningBalance::where('customer_id', $customerId)->sum('amount');

                // Calculate payable opening balance (original opening balance - already paid)
                $payableOpeningBalance = $customer->opening_balance - $totalOpeningBalancePaid;

                $totalSales = $this->saleService->calculateTotalAmount($customerId);
                $paidAmount = $this->saleService->invoicePaidAmount($customerId);
                $partialPaid = $this->saleService->partialPaidAmount($customerId);
                $dueAmount = ($totalSales + $payableOpeningBalance) - ($paidAmount + $partialPaid);

                if ($remainingAmount > $dueAmount) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Payment amount exceeds the due amount of PKR ' . number_format($dueAmount, 2),
                    ], 400);
                }

                DB::beginTransaction();

                $paymentDetails = [];
                $receiptItems = [];
                $totalApplied = 0;
                $receiptNote = $validated['notes'] ?? ''; // Initialize receipt note

                // Generate receipt number
                $receiptNumber = 'RCP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

                // 1. First handle opening balance
                if ($payableOpeningBalance > 0 && $remainingAmount > 0) {
                    $amountToOpeningBalance = min($payableOpeningBalance, $remainingAmount);

                    if ($amountToOpeningBalance > 0) {
                        $receiptNote = $validated['notes'] ?? "Opening balance payment";
                        CustomerOpeningBalance::create([
                            'invoice_no' => 'OB-' . time(),
                            'customer_id' => $customerId,
                            'amount' => $amountToOpeningBalance,
                            'discount_amount' => @$request->discount_amount ?? 0,
                            'date' => date('Y-m-d H:i:s'),
                            'description' => $receiptNote,
                            'createdBy' => Auth::id()
                        ]);


                        $receiptItems[] = [
                            'description' => 'Opening Balance Payment',
                            'amount' => $amountToOpeningBalance
                        ];

                        $paymentDetails[] = [
                            'type' => 'opening_balance',
                            'amount' => $amountToOpeningBalance,
                            'remaining_balance' => $payableOpeningBalance - $amountToOpeningBalance
                        ];

                        $totalApplied += $amountToOpeningBalance;
                        $remainingAmount -= $amountToOpeningBalance;
                    }
                }

                if ($request->discount_amount > 0) {
                    CustomerDiscount::create([
                        'customer_id' => $customerId,
                        'discount_amount' => $request->discount_amount,
                        'createdBy' => Auth::id()
                    ]);
                }

                // 2. Then apply to invoices (oldest first)
                if ($remainingAmount > 0) {
                    $unpaidInvoices = Sale::where('customer_id', $customerId)
                        ->where('status', 1)
                        ->orderBy('created_at', 'asc')
                        ->get();

                    foreach ($unpaidInvoices as $invoice) {
                        if ($remainingAmount <= 0) break;

                        $invoiceDue = $invoice->grand_total - ($this->getPaidAmountForInvoice($invoice->id) + $invoice->paid_amount);

                        if ($invoiceDue > 0) {
                            $receiptNote = $validated['notes'] ?? "Payment for invoice #{$invoice->id}";
                            $amountToInvoice = min($invoiceDue, $remainingAmount);

                            // Save to customer_payments table
                            CustomerPayment::create([
                                'sale_id' => $invoice->id,
                                'amount' => $amountToInvoice,
                                'payment_date' => date('Y-m-d H:i:s'),
                                'notes' => $receiptNote,
                                'created_by' => Auth::id()
                            ]);

                            // Update invoice payment status
                            $totalPaidNow = $this->getPaidAmountForInvoice($invoice->id) + $amountToInvoice;
                            $paymentStatus = $this->determinePaymentStatus($invoice->grand_total, $totalPaidNow);

                            $invoice->update([
                                'payment_status' => $paymentStatus
                            ]);

                            $receiptItems[] = [
                                'description' => "Invoice #{$invoice->id}",
                                'amount' => $amountToInvoice
                            ];

                            $paymentDetails[] = [
                                'type' => 'invoice',
                                'invoice_id' => $invoice->id,
                                'amount' => $amountToInvoice,
                                'remaining_due' => $invoiceDue - $amountToInvoice,
                                'payment_status' => $paymentStatus
                            ];

                            $totalApplied += $amountToInvoice;
                            $remainingAmount -= $amountToInvoice;
                        }
                    }
                }

                DB::commit();

                // Calculate remaining payable opening balance after this payment
                $newTotalOpeningBalancePaid = $totalOpeningBalancePaid + ($paymentAmount - $remainingAmount - ($totalApplied - ($paymentAmount - $remainingAmount)));
                $newPayableOpeningBalance = $customer->opening_balance - $newTotalOpeningBalancePaid;

                // Prepare receipt data for response
                $receiptData = [
                    'receipt_number' => $receiptNumber,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'customer_address' => $customer->address,
                    'payment_date' => date('d M, Y h:i A'),
                    'total_amount' => $paymentAmount,
                    'amount_applied' => $totalApplied,
                    'remaining_amount' => $remainingAmount > 0 ? $remainingAmount : 0,
                    'payment_method' => 'Cash',
                    'receipt_items' => $receiptItems,
                    'processed_by' => Auth::user()->name,
                    'payment_notes' => $receiptNote ?? ($validated['notes'] ?? ''), // Include payment notes
                    'original_opening_balance' => $customer->opening_balance,
                    'remaining_opening_balance' => max(0, $newPayableOpeningBalance)
                ];

                // Generate and save PDF receipt
                $pdfPath = $this->generateAndSavePDFReceipt($receiptData);
                CustomerPaymentReceipt::create([
                    'customer_id' => $customerId,
                    'receitpt_path' => $pdfPath
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'payment_details' => $paymentDetails,
                    'remaining_amount' => $remainingAmount > 0 ? $remainingAmount : 0,
                    'receipt_data' => $receiptData,
                    'pdf_path' => $pdfPath
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Oops, something went wrong. Try again.',
                    'error' => $th->getMessage(),
                ], 500);
            }
        }
    }



    private function getPaidAmountForInvoice($invoiceId)
    {
        return CustomerPayment::where('sale_id', $invoiceId)
            ->sum('amount') ?? 0;
    }

    private function determinePaymentStatus($grandTotal, $paidAmount)
    {
        if ($paidAmount <= 0) {
            return 'unpaid';
        } elseif ($paidAmount >= $grandTotal) {
            return 'paid';
        } else {
            return 'partial';
        }
    }

    private function generateAndSavePDFReceipt($receiptData)
    {
        try {
            // Create the directory if it doesn't exist
            $directory = public_path('customer_payment_receipts');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Generate PDF with thermal printer settings
            $pdf = PDF::loadHTML($this->generateReceiptHTML($receiptData));

            // Set paper size for thermal printer (76mm width)
            $pdf->setPaper([0, 0, 215, 900], 'portrait'); // 76mm = 215 points

            // Optimize for thermal printing
            $pdf->setOptions([
                'dpi' => 203, // Common thermal printer DPI
                'defaultFont' => 'Courier New',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'isPhpEnabled' => false,
                'isFontSubsettingEnabled' => true,
            ]);

            // Generate filename
            $filename = $receiptData['receipt_number'] . '.pdf';
            $filePath = $directory . '/' . $filename;

            // Save PDF to file
            $pdf->save($filePath);

            // Return relative path for database storage if needed
            return 'customer_payment_receipts/' . $filename;
        } catch (\Exception $e) {
            Log::error('PDF Generation Error: ' . $e->getMessage());
            return null;
        }
    }

    private function generateReceiptHTML($receiptData)
    {
        $itemsHTML = '';
        foreach ($receiptData['receipt_items'] as $item) {
            $itemsHTML .= '
        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
            <span style="flex: 2; font-size: 10px;">' . htmlspecialchars($item['description']) . '</span>
            <span style="flex: 1; text-align: right; font-size: 10px; font-weight: bold;">' . number_format($item['amount'], 2) . '</span>
        </div>';
        }

        $remainingHTML = '';
        if ($receiptData['remaining_amount'] > 0) {
            $remainingHTML = '
        <div style="display: flex; justify-content: space-between; margin: 8px 0; padding: 4px; background: #f5f5f5; border: 1px dashed #ccc;">
            <span style="font-size: 10px; font-weight: bold;">Remaining:</span>
            <span style="font-size: 10px; font-weight: bold;">' . number_format($receiptData['remaining_amount'], 2) . '</span>
        </div>';
        }

        $notesHTML = '';
        if (!empty($receiptData['payment_notes'])) {
            $notesHTML = '
        <div style="margin: 8px 0; padding: 6px; border-top: 1px dashed #000; border-bottom: 1px dashed #000;">
            <div style="text-align: center; font-size: 10px; font-weight: bold; margin-bottom: 4px;">NOTES</div>
            <div style="font-size: 9px; text-align: center; font-style: italic;">' . htmlspecialchars($receiptData['payment_notes']) . '</div>
        </div>';
        }

        return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Payment Receipt - ' . $receiptData['receipt_number'] . '</title>
        <style>
            @page {
                margin: 0;
                padding: 0;
                size: 76mm auto;
            }
            body {
                font-family: "Courier New", monospace;
                font-size: 11px;
                line-height: 1.2;
                margin: 0;
                padding: 5mm;
                color: #000;
                background: #ffffff;
                width: 76mm;
                max-width: 76mm;
            }
            * {
                box-sizing: border-box;
            }
            .receipt-container {
                width: 100%;
                margin: 0 auto;
            }
            .header {
                text-align: center;
                border-bottom: 1px solid #000;
                padding-bottom: 8px;
                margin-bottom: 8px;
            }
            .header h1 {
                font-size: 14px;
                font-weight: bold;
                margin: 0 0 4px 0;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .company-name {
                font-size: 11px;
                font-weight: bold;
                margin: 2px 0;
            }
            .company-info {
                font-size: 9px;
                margin: 1px 0;
                line-height: 1.1;
            }
            .details-section {
                margin-bottom: 8px;
                padding: 6px 0;
            }
            .detail-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 3px;
                padding: 1px 0;
            }
            .detail-label {
                font-weight: bold;
                font-size: 10px;
            }
            .detail-value {
                text-align: right;
                font-size: 10px;
            }
            .items-section {
                margin: 8px 0;
                border-top: 1px solid #000;
                border-bottom: 1px solid #000;
                padding: 6px 0;
            }
            .items-header {
                text-align: center;
                font-weight: bold;
                margin-bottom: 6px;
                font-size: 10px;
                text-transform: uppercase;
            }
            .items-content {
                padding: 0 2px;
            }
            .totals-section {
                margin: 8px 0;
                padding: 8px;
                border: 1px solid #000;
                background: #f8f8f8;
            }
            .total-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 3px;
                padding: 2px 0;
                font-size: 10px;
            }
            .grand-total {
                font-weight: bold;
                border-top: 1px solid #000;
                border-bottom: 1px solid #000;
                margin-top: 6px;
                padding: 4px 0;
                font-size: 11px;
            }
            .signature-section {
                margin-top: 12px;
                padding-top: 8px;
                border-top: 1px dashed #666;
            }
            .signature-line {
                border-top: 1px solid #000;
                margin-top: 20px;
                padding-top: 3px;
                text-align: center;
                font-size: 8px;
                color: #666;
            }
            .footer {
                text-align: center;
                margin-top: 12px;
                padding-top: 8px;
                border-top: 1px dashed #666;
                color: #666;
                font-size: 8px;
                line-height: 1.1;
            }
            .footer p {
                margin: 2px 0;
            }
            .divider {
                border-top: 1px dashed #000;
                margin: 6px 0;
            }
            .center {
                text-align: center;
            }
            .bold {
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="receipt-container">
            <div class="header">
                <h1>PAYMENT RECEIPT</h1>
                <div class="company-name">TRADESPHERE SOLUTIONS</div>
                <div class="company-info">123 Business Street, City</div>
                <div class="company-info">Phone: (555) 123-4567</div>
            </div>

            <div class="details-section">
                <div class="detail-row">
                    <span class="detail-label">Receipt#:</span>
                    <span class="detail-value">' . $receiptData['receipt_number'] . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">' . $receiptData['payment_date'] . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Customer:</span>
                    <span class="detail-value">' . htmlspecialchars($receiptData['customer_name']) . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Method:</span>
                    <span class="detail-value">' . $receiptData['payment_method'] . '</span>
                </div>
            </div>

            <div class="divider"></div>

            <div class="items-section">
                <div class="items-header">PAYMENT DETAILS</div>
                <div class="items-content">
                    ' . $itemsHTML . '
                </div>
            </div>

            ' . $notesHTML . '

            <div class="totals-section">
                <div class="total-row">
                    <span>Total Paid:</span>
                    <span>' . number_format($receiptData['total_amount'], 2) . '</span>
                </div>
                <div class="total-row">
                    <span>Amount Applied:</span>
                    <span>' . number_format($receiptData['amount_applied'], 2) . '</span>
                </div>
                ' . $remainingHTML . '
                <div class="total-row grand-total">
                    <span>NET RECEIVED:</span>
                    <span>' . number_format($receiptData['amount_applied'], 2) . '</span>
                </div>
            </div>

            <div class="signature-section">
                <div class="detail-row">
                    <span class="detail-label">Processed By:</span>
                    <span class="detail-value">' . htmlspecialchars($receiptData['processed_by']) . '</span>
                </div>
                <div class="signature-line">
                    Authorized Signature
                </div>
            </div>

            <div class="footer">
                <p class="bold">** COMPUTER GENERATED RECEIPT **</p>
                <p>Thank you for your business!</p>
                <p>For queries: support@tradesphere.com</p>
                <div class="divider"></div>
                <p style="font-size: 7px;">ID: ' . $receiptData['receipt_number'] . '</p>
            </div>
        </div>
    </body>
    </html>';
    }
    /********************************************************************/
    public function customerView(Request $request, $id = null)
    {
        if ($request->ajax()) {
            $customer = [];
            if ($id) {
                $customer = Customer::with('area:id,name')->find($id);
                $balance = $this->saleService->customerTotalBalance($customer->id);
                $balance = $balance < 0
                    ? -1 * $balance . ' CR'
                    : ($balance > 0 ? $balance . ' DB' : $balance);
                return response()->json([
                    'success' => true,
                    'html' => view('customers.includes.customer_details_modal', compact('customer', 'balance'))->render()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'data not found'
                ]);
            }
        }

        return response()->json(['status' => false, 'message' => 'Invalid request']);
    }
    /********************************************************************/
    public function loadCustomerFrom(Request $request, $id = null)
    {
        if ($request->ajax()) {
            $permissionCheck =  checkRolePermission('customers');
            if ($permissionCheck->access == 0) {
                return response()->json(['success' => false, 'message' => 'You have no permission!']);
            }
            $customer = [];
            $areas = Area::all(); // Fetch all areas (update with your actual model)

            if ($id) {
                $customer = Customer::with('area:id,name')->find($id);
            }
            // echo "<pre>";
            // print_r($customer->toArray());
            // exit();
            return response()->json([
                'success' => true,
                'html' => view('customers.includes.create_customer', compact('areas', 'customer'))->render()
            ]);
        }


        return response()->json(['status' => false, 'message' => 'Invalid request']);
    }

    /********************************************************************/
    public function addCustomer(CustomerRequest $request, $id = null)
    {
        if ($request->ajax()) {
            $validatedData = $request->validated();
            // echo "<pre>";
            // print_r($request->all());
            // exit();
            try {
                if ($id) {
                    $customer =  $this->storeCustomer($validatedData, $id);
                } else {
                    $customer =  $this->storeCustomer($validatedData, null);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Save Changes Successfully ',
                    'customerName' => $validatedData['name'],
                    "customerID" => $customer->id,
                    "areaID" => $customer->area_id,
                    "customerBalance" => $this->saleService->customerTotalBalance($customer->id),
                    // "customerBalance" => $customer->opening_balance + ($this->saleService->calculateTotalAmount($customer->id) + $this->saleService->calculateCustomerBalance($customer->id, 'debit') - $this->saleService->calculateCustomerBalance($customer->id, 'credit'))
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Oops, something went wrong. Try again.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }
    }
    private function storeCustomer($validatedData, $id)
    {
        $validatedData['name_ur'] = $validatedData['name_ur'] ?? $validatedData['name'];
        $validatedData['createdBy'] = Auth::id();
        if ($id) {
            $customer = Customer::findOrFail($id);
            $customer->update($validatedData);
            return $customer;
        } else {
            return Customer::create($validatedData);
        }
    }
    /********************************************************************/
    public function searchCustomer(Request $request)
    {
        if ($request->ajax()) {
            $cuttomerAuto = [];
            $customers = Customer::where('name', 'LIKE', "%" . $request->term . "%")
                ->orWhere('mobile', 'LIKE', "%" . $request->term . "%")->limit(5)->get();

            if ($customers) {
                foreach ($customers as $key => $customer) {

                    $cuttomerAuto[] = array(
                        "value" => $customer->name,
                        "customerID" => $customer->id,
                        "customerName" => $customer->name,
                        "areaID" => $customer->area_id,
                        "customerBalance" => round($this->saleService->customerTotalBalance($customer->id)),
                        "mobile" => $customer->mobile,
                    );
                }
                return response()->json($cuttomerAuto);
                // $dataRetrun = json_encode($cuttomerAuto);
                // return Response($dataRetrun);
            }
        }
    }

    /********************************************************************/
    public function loadCustomerPaymentFrom(Request $request, $id = null)
    {
        if (!$request->ajax()) {
            return response()->json(['status' => false, 'message' => 'Invalid request'], 400);
        }

        // Retrieve the latest voucher number in a single query
        $latestVoucher = CustomerOpeningBalance::latest('id')->value('invoice_no');
        $voucher_no = $latestVoucher ? $latestVoucher + 1 : 1;

        // Fetch the customer payment if an ID is provided
        $customerPayments = $id
            ? CustomerOpeningBalance::with(['users:id,name', 'customers:id,name'])
            ->select('id', 'invoice_no', 'date', 'customer_id', 'type', 'amount', 'description', 'createdBy')
            ->find($id)
            : null;
        $balance = 0;
        if ($customerPayments) {
            $balance = $this->saleService->customerTotalBalance($customerPayments->customer_id);
        }
        // Render the modal HTML and return the response
        $html = view('customers.includes.payments_modal', compact('voucher_no', 'customerPayments', 'balance'))->render();

        return response()->json([
            'success' => true,
            'id' => $id,
            'html' => $html,
            'page' => 'customer_payments'
        ]);
    }

    // public function loadCustomerPaymentFrom(Request $request, $id = null)
    // {
    //     if ($request->ajax()) {
    //         $customerPayments = [];
    //         $voucher_data = CustomerOpeningBalance::select('id')->orderBy('id', 'DESC')->first();
    //         if ($id) {
    //             $customerPayments = CustomerOpeningBalance::with(['users:id,name', 'customers:id,name'])->select('id', 'invoice_no', 'date', 'customer_id', 'type', 'amount', 'createdBy')->find($id);
    //         }
    //         if ($voucher_data == null) {

    //             $firstReg = '0';
    //             $voucher_no = $firstReg + 1;
    //         } else {
    //             $voucher_data = CustomerOpeningBalance::orderBy('id', 'DESC')->first()->invoice_no;
    //             // echo "<pre>"; print_r($voucher_data); exit();
    //             $voucher_no = $voucher_data + 1;
    //         }
    //         return response()->json([
    //             'success' => true,
    //             'html' => view('customers.includes.payments_modal', compact('voucher_no', 'customerPayments'))->render()
    //         ]);
    //     }

    //     return response()->json(['status' => false, 'message' => 'Invalid request']);
    // }

    /********************************************************************/
    public function customerPayments()
    {
        $this->authenticateRole($module_page = 'customers_payment');
        Session::put('page', 'customerPayment');
        return view('customers.payments.create');
    }
    /********************************************************************/
    public function customerPaymentsList(Request $request)
    {
        if ($request->ajax()) {
            $data = CustomerOpeningBalance::with(['users:id,name', 'customers:id,name'])
                ->select(
                    'customer_opening_balances.id', // Explicitly specify the table for 'id'
                    'customer_opening_balances.invoice_no',
                    'customer_opening_balances.date',
                    'customer_opening_balances.customer_id',
                    'customer_opening_balances.type',
                    'customer_opening_balances.amount',
                    'customer_opening_balances.createdBy'
                )
                ->orderBy('customer_opening_balances.id', 'desc'); // Explicitly specify the table for 'id'

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    return date('d-m-Y | h:i A', strtotime($row->date));
                })
                ->addColumn('customer', function ($row) {
                    return $row->customers ? $row->customers->name : '';
                })
                ->addColumn('payment_type', function ($row) {
                    return strtoupper($row->type);
                })



                ->addColumn('createdBy', function ($row) {
                    return $row->users ? $row->users->name : '';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="javascript:void(0);" class="btn btn-warning btn-sm view" data-url="' . route('customer.payments.view', $row->id) . '" data-id="' . $row->id . '" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                    <a href="javascript:void(0);" class="btn btn-info btn-sm editCustomerPayment" data-url="' . route('load.customer.payment.form', $row->id) . '" data-saveCustomerPaymentUrl="' . route('store.customer.payment', $row->id) . '" title="Edit?">
                            <i class="fas fa-pen"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-danger btn-sm delete" data-url="' . route('delete.customer.payment', $row->id) . '" data-id="' . $row->id . '" title="Delete">
                            <i class="fas fa-trash"></i>
                        </a>';
                })
                ->rawColumns(['action']) // Ensure raw HTML rendering for the 'action' column
                ->make(true);
        }
    }
    /********************************************************************/
    public function storeCustomerPayment(CustomerPaymentRequest $request, $id = null)
    {
        if ($request->ajax()) {
            $validatedData = $request->validated();

            try {
                if ($id) {
                    $this->storePayments($validatedData, $id);
                    $message = 'Updated';
                } else {
                    $this->storePayments($validatedData, null);
                    $message = 'Addedd';
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Customer Payment Successfully ' . $message
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Oops, something went wrong. Try again.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid request.',
        ], 400);
    }

    /********************************************************************/

    private function storePayments($validatedData, $id = null)
    {
        if ($id) {
            $payment = CustomerOpeningBalance::findOrFail($id);
            $payment->update($validatedData);
            return $payment;
        } else {
            return CustomerOpeningBalance::create($validatedData);
        }
    }
    /********************************************************************/
    public function customerPaymentsView(Request $request, $id = null)
    {
        if ($request->ajax()) {
            $customerPayments = [];
            if ($id) {
                $customerPayments = CustomerOpeningBalance::with(['users:id,name', 'customers:id,name'])->select('id', 'invoice_no', 'date', 'customer_id', 'type', 'description', 'amount', 'createdBy')->find($id);
                $balance = $this->saleService->customerTotalBalance($customerPayments->customer_id);
                $balance = $balance < 0
                    ? -1 * $balance . ' CR'
                    : ($balance > 0 ? $balance . ' DB' : $balance);
                return response()->json([
                    'success' => true,
                    'html' => view('customers.includes.payment_details_modal', compact('customerPayments', 'balance'))->render()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'data not found'
                ]);
            }
        }

        return response()->json(['status' => false, 'message' => 'Invalid request']);
    }


    /********************************************************************/
    public function deleteCustomerPayment(Request $request, $id = null)
    {
        if ($request->ajax()) {
            try {
                CustomerOpeningBalance::find($id)->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Data Deleted Successfully'
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Oops, something went wrong. Try again.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }
    }

    /********************************************************************/
    public function deleteCustomer(Request $request, $id = null)
    {
        if ($request->ajax()) {
            try {
                Customer::find($id)->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Data Deleted Successfully'
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Oops, something went wrong. Try again.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }
    }
    /********************************************************************/
    public function getInactiveCustomers()
    {
        try {
            // Calculate date 3 days ago
            $threeDaysAgo = Carbon::now()->subDays(3)->format('Y-m-d H:i:s');

            // Get customers who haven't purchased in the last 3 days
            $inactiveCustomers = Customer::whereDoesntHave('sales', function ($query) use ($threeDaysAgo) {
                $query->where('date', '>=', $threeDaysAgo);
            })
                ->orWhereHas('sales', function ($query) use ($threeDaysAgo) {
                    $query->where('date', '<', $threeDaysAgo);
                }, '=', 0)
                ->with(['sales' => function ($query) {
                    $query->orderBy('date', 'desc')->take(1);
                }])
                ->select('id', 'name', 'mobile', 'email')
                ->get()
                ->map(function ($customer) {
                    $lastSale = $customer->sales->first();
                    $lastPurchaseDate = $lastSale ? $lastSale->date : null;
                    $daysSinceLastPurchase = $lastPurchaseDate ?
                        Carbon::parse($lastPurchaseDate)->diffInDays(Carbon::now()) : 'Never';

                    return [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'mobile' => $customer->mobile,
                        'email' => $customer->email,
                        'last_purchase_date' => $lastPurchaseDate,
                        'days_since_last_purchase' => $daysSinceLastPurchase
                    ];
                })
                ->sortByDesc('days_since_last_purchase')
                ->values();

            return response()->json([
                'success' => true,
                'customers' => $inactiveCustomers,
                'count' => $inactiveCustomers->count(),
                'last_checked' => now()->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching inactive customers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
