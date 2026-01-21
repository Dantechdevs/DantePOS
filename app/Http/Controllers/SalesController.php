<?php

namespace App\Http\Controllers;

use App\CustomerPayment;
use App\Godown;
use App\Helpers\AssetHelper;
use App\Http\Requests\SaleRequest;
use App\Imports\BulkSaleImport;
use App\Imports\BulkSalesImport;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Unit;
use App\Services\PurchaseService;
use App\Services\SaleService;
use App\Services\SchemeService;
use App\SiteSetting;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;
use Maatwebsite\Excel\Facades\Excel;

class SalesController extends Controller
{
    protected $saleService;
    protected $purchaseService;
    protected $schemeService;

    public function __construct(
        SaleService $saleService,
        PurchaseService $purchaseService,
        SchemeService $schemeService
    ) {
        $this->saleService = $saleService;
        $this->purchaseService = $purchaseService;
        $this->schemeService = $schemeService;
    }


    /*====================================*/
    public function authenticateRole($module_page)
    {
        $permissionCheck = checkRolePermission($module_page);
        // dd($permissionCheck);
        if ($permissionCheck->access == 0) {

            return redirect()->to('/dashboard')->send()->with('error', 'You have no permission!');
        }
    }
    /********************************************************************/
    public function index()
    {

        $this->authenticateRole($module_page = 'sales');
        try {
            Session::put('page', 'viewInvoice');
            $sales = Sale::with(['customer'])->orderBy('id', 'DESC')->get();
            $settings = SiteSetting::pluck('value', 'key')->toArray();
            // echo "<pre>"; print_r($sales->toArray()); exit();
            return view('sales.view', compact('sales', 'settings'));
        } catch (Exception $e) {
            Session::flash('flash_message_error', "Oops, Something went wrong. Try again");
            return redirect('dashboard')->with($e->getMessage());
        }
    }
    /********************************************************************/
    public function dueSales(Request $request)
    {
        if ($request->ajax()) {
            $data = Sale::with(['customers'])
                ->select(
                    'sales.id',
                    'sales.invoice_no',
                    'sales.customer_id',
                    'sales.grand_total',
                    'sales.paid_amount',
                    'sales.payment_status',
                    'sales.status'
                )
                ->where('sales.payment_status', '!=', 'paid')
                ->where('sales.status', '!=', 0) // Exclude cancelled sales
                ->whereDate('sales.due_date', '<', now()) // Today > due_date
                ->orderBy('sales.id', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('invoice', function ($row) {
                    // echo '<pre>'; print_r($row->toArray()); exit; exit;
                    return $row->invoice_no;
                })
                ->addColumn('customer', function ($row) {
                    return optional($row->customers)->name;
                })
                ->addColumn('due_amount', function ($row) {
                    $paidAmount = $this->saleService->invoicePaidBySaleId($row->id) + $this->saleService->partialPaidBySaleId($row->id);
                    $totalPaid = $paidAmount > $row->grand_total ? $row->grand_total : $paidAmount;


                    $dueAmount = $row->grand_total - $totalPaid;
                    return number_format($dueAmount);
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    /********************************************************************/
    public function salesList(Request $request)
    {
        if ($request->ajax()) {
            $data = Sale::with(['customer:id,name', 'users:id,name'])
                ->select(
                    'sales.id', // Explicitly specify the table for 'id'
                    'sales.invoice_no',
                    'sales.sale_type',
                    'sales.date',
                    'sales.customer_id',
                    'sales.grand_total',
                    'sales.paid_amount',
                    'sales.payment_status',
                    'sales.status',
                    'sales.createdBy'
                )
                ->orderBy('sales.id', 'desc'); // Order by 'id' in descending order


            return DataTables::of($data)
                ->addIndexColumn() // Adds the row index
                ->filterColumn('sale_type', function ($query, $keyword) {
                    $query->where('sale_type', 'like', "%{$keyword}%");
                })

                ->editColumn('date', function ($row) {
                    return $row->date ? date('d-m-Y | h:i A', strtotime($row->date)) : 'N/A'; // Safer date formatting
                })
                ->editColumn('status', function ($row) {
                    if ($row->status === 0) {
                        return '<span class="badge badge-danger" style="width: 100px;">CANCELLED</span>';
                    } elseif ($row->status === 1) {
                        return '<span class="badge badge-success" style="width: 100px;">Billed</span>';
                    } elseif ($row->status === 2) {
                        return '<span class="badge badge-warning" style="width: 100px;">PENDING</span>';
                    } else {
                        return '<span class="badge badge-primary" style="width: 100px;">Return</span>';
                    }
                })
                ->filterColumn('status', function ($query, $keyword) {
                    if (strtolower($keyword) === 'cancel') {
                        $query->where('status', 0);
                    } elseif (strtolower($keyword) === 'completed') {
                        $query->where('status', 1);
                    } elseif (strtolower($keyword) === 'pending') {
                        $query->where('status', 2);
                    } elseif (strtolower($keyword) === 'return') {
                        $query->where('status', 3);
                    }
                })
                ->addColumn('customer', function ($row) {
                    return optional($row->customer)->name; // Use `optional` for cleaner null handling
                })

                ->filterColumn('customer', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })

                ->addColumn('grand_total', function ($row) {
                    return number_format($row->grand_total); // Use `optional` for cleaner null handling
                })

                ->addColumn('paid_amount', function ($row) {
                    // dd($this->saleService->invoicePaidBySaleId($row->id));
                    $paidAmount = $this->saleService->invoicePaidBySaleId($row->id) + $this->saleService->partialPaidBySaleId($row->id);
                    $totalPaid = $paidAmount > $row->grand_total ? $row->grand_total : $paidAmount;
                    return number_format($totalPaid); // Cleaner user relationship handling
                })
                ->addColumn('due', function ($row) {
                    $paidAmount = $this->saleService->invoicePaidBySaleId($row->id) + $this->saleService->partialPaidBySaleId($row->id);
                    $totalPaid = $paidAmount > $row->grand_total ? $row->grand_total : $paidAmount;
                    $dueAmount = $row->grand_total - $totalPaid;
                    return number_format($dueAmount); // Cleaner user relationship handling
                })

                ->editColumn('payment_status', function ($row) {
                    if ($row->payment_status === 'unpaid') {
                        return '<span class="badge badge-danger" style="width: 100px;">' . $row->payment_status . '</span>';
                    } elseif ($row->payment_status === 'paid') {
                        return '<span class="badge badge-success" style="width: 100px;">' . $row->payment_status . '</span>';
                    } elseif ($row->payment_status === 'partial') {
                        return '<span class="badge badge-warning" style="width: 100px;">' . $row->payment_status . '</span>';
                    }
                })

                ->addColumn('createdBy', function ($row) {
                    return optional($row->users)->name; // Cleaner user relationship handling
                })
                ->filterColumn('createdBy', function ($query, $keyword) {
                    $query->whereHas('users', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('action', function ($row) {
                    // Dropdown action menu
                    $actions = '
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="actionMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Action
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="actionMenu">
                        <a class="dropdown-item" href="javascript:void(0);" onclick="window.open(\'' . route('pos.invoice', $row->id) . '\', \'POS Invoice\', \'width=500,height=500,scrollbars=yes\')">
                            <i class="fas fa-cash-register text-blue"></i> POS Invoice
                        </a>
                        <a class="dropdown-item" href="' . route('edit.sale', $row->id) . '">
                            <i class="fas fa-edit text-blue"></i> Edit
                        </a>
                        <a class="dropdown-item" href="' . route('sale.invoice', $row->id) . '">
                            <i class="fas fa-file-invoice text-gray"></i> Sale Invoice
                        </a>';
                        // Only show "Pay Due Payments" for superadmin
                if (Auth::user()->user_type === 'superadmin') {
                    $actions .= '
                        <a class="dropdown-item paySaleDuePayment" href="javascript:void(0);" data-url="' . route('invoice.payment.modal', $row->id) . '">
                            <i class="fas fa-money-bill text-yellow"></i> Pay Now
                        </a>';
                }
                    if ($row->status == '0') { // Conditional delete option for pending sales
                        $actions .= '
                        <a class="dropdown-item delete" href="javascript:void(0);" data-url="' . route('delete.sale', $row->id) . '">
                            <i class="fas fa-trash text-red"></i> Delete
                        </a>';
                    }
                    $actions .= '</div></div>';
                    return $actions;
                })
                ->rawColumns(['status', 'payment_status', 'action']) // Ensure HTML rendering for these columns
                ->make(true);
        }

        return response()->json(['status' => false, 'message' => 'Invalid request'], 400);
    }
    /********************************************************************/
    public function showInvoicePaymentModal(Request $request, $saleId)
    {
        $sale = Sale::with(['customers'])->findOrFail($saleId);
        $paidAmount = $this->saleService->invoicePaidBySaleId($sale->id) + $this->saleService->partialPaidBySaleId($sale->id);
        $totalPaid = $paidAmount > $sale->grand_total ? $sale->grand_total : $paidAmount;


        $dueAmount = $sale->grand_total - $totalPaid;
        $invoicePaymentJistory = $this->saleService->invoicePaymentHistory($sale);
        $settings = SiteSetting::pluck('value', 'key');
        // echo "<pre>"; print_r($sale->toArray()); "</pre>"; exit;
        return view('sales.includes.pay_due', compact('sale', 'totalPaid', 'dueAmount', 'invoicePaymentJistory', 'settings'));
    }
    /********************************************************************/
    public function submitInvoicePayment(Request $request)
    {
        try {
            $sale = Sale::findOrFail($request->sale_id);
            $totalSales = $sale->grand_total;
            $paidAmount = $this->saleService->invoicePaidBySaleId($sale->id);
            $partialPaid = $this->saleService->partialPaidBySaleId($sale->id);
            $totalPaid = $paidAmount + $partialPaid;
            $dueAmount = $totalSales - $totalPaid;

            // Validate payment amount
            if ($request->amount > $dueAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount exceeds the due amount of PKR ' . number_format($dueAmount, 2),
                ], 400);
            }

            if ($request->amount <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment amount must be greater than zero',
                ], 400);
            }

            DB::beginTransaction();

            // Create the payment record
            CustomerPayment::create([
                'sale_id' => $sale->id,
                'amount' => $request->amount,
                'payment_date' => date('Y-m-d H:i:s'),
                'notes' => $request->notes ?? "Payment for invoice #{$sale->invoice_no}",
                'created_by' => Auth::id()
            ]);

            // Calculate new total paid amount including this payment
            $newTotalPaid = $totalPaid + $request->amount;

            // Determine and update payment status
            if ($newTotalPaid >= $totalSales) {
                $paymentStatus = 'paid';
                $due_date = null;
            } elseif ($newTotalPaid > 0) {
                $paymentStatus = 'partial';
                $due_date = $sale->due_date;
            } else {
                $paymentStatus = 'unpaid';
                $due_date = $sale->due_date;
            }

            // Update sale payment status and paid amount
            $sale->update([
                'payment_status' => $paymentStatus,
                'due_date' => $due_date,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'payment_status' => $paymentStatus,
                'paid_amount' => $newTotalPaid,
                'due_amount' => $totalSales - $newTotalPaid
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
    /********************************************************************/

    public function deleteInvoicePayment($payment_id = null)
    {
        try {
            DB::beginTransaction();
            if (!$payment_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Initial payment record cannot deleted.',
                ], 400);
            }
            $invoicePyament = CustomerPayment::where('id', $payment_id)->first();
            // Get related sale before deletion
            $sale = Sale::findOrFail($invoicePyament->sale_id);

            // Delete the payment
            $invoicePyament->forceDelete();

            // Recalculate payment totals
            $totalSales = $sale->grand_total;
            $paidAmount = $this->saleService->invoicePaidBySaleId($sale->id);
            $partialPaid = $this->saleService->partialPaidBySaleId($sale->id);
            $totalPaid = $paidAmount + $partialPaid;
            $dueAmount = $totalSales - $totalPaid;

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully',
                'updatedData' => [
                    'totalPaid' => $totalPaid,
                    'totalPaidFormatted' => number_format($totalPaid),
                    'dueAmount' => $dueAmount,
                    'dueAmountFormatted' => number_format($dueAmount)
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /********************************************************************/
    public function openPos(Request $request)
    {
        $areas = Area::get();

        // $invoice_data = Sale::orderBy('id', 'DESC')->first();
        // $invoice_no = $invoice_data ? $invoice_data->invoice_no + 1 : 1;
        $godowns = Godown::where('status', 'active')->get();
        if ($request->ajax()) {
            $assets = AssetHelper::getAssets('sales');
            return response()->json([
                'html' => view('sales.load_sale_form', compact('invoice_no', 'godowns'))->render(),
                'scripts' => $assets['scripts'],
                'styles' => $assets['styles']
            ]);
        }
        return view('sales.pos', compact('areas', 'godowns'));
    }
    /********************************************************************/
    public function checkProductStock(Request $request)
    {
        $data = $request->all();

        $product = Product::where('id', $data['productID'])->first()->toArray();
        if ($product['quantity'] < $data['quantity']) {
            return response()->json(
                [
                    'error' => true,
                    'availableQuantity' => $product['quantity'],
                    'message' => $product['name'] . ' Product is ' . $product['quantity'] . ' remaining!'
                ]
            );
        }
    }
    /********************************************************************/
    public function downloadSample(Request $request)
    {
        if ($request->ajax()) {
            $filePath = public_path('uploads/Bulk_sales.xlsx'); // Adjust file path as needed

            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found!'
                ]);
            }

            return response()->json([
                'success' => true,
                'file_url' => asset('uploads/Bulk_sales.xlsx'), // Publicly accessible file URL
                'file_name' => 'sample.xlsx' // Suggested download filename
            ]);
        }
    }
    /********************************************************************/
    public function salesImport(Request $request)
    {
        try {
            $import = new BulkSaleImport();
            Excel::import($import, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Sales imported successfully.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors occurred while processing the sales.',
                'errors' => $e->validator->errors()->toArray(), // Ensure this is formatted correctly
            ], 422);
        } catch (\Exception $e) {
            // $errors = explode(',',$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the sales.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /********************************************************************/
    public function salesImportxxx(Request $request)
    {
        // $request->validate([
        //     'file' => 'required|file|mimes:xlsx,csv|max:2048',
        // ]);

        try {
            $import = new BulkSalesImport();
            Excel::import($import, $request->file('file'));

            if (!empty($import->errors)) {
                $fileName = 'sale_import_errors_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
                $errorFilePath = 'errors/sale_import_errors_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

                // Generate an error Excel file
                Excel::store(new class($import->errors) implements \Maatwebsite\Excel\Concerns\FromCollection {
                    private $errors;

                    public function __construct($errors)
                    {
                        $this->errors = $errors;
                    }

                    public function collection()
                    {
                        return collect($this->errors);
                    }
                }, $errorFilePath, 'public');

                return response()->json([
                    'success' => false,
                    'message' => 'Some rows failed validation. Download the error file for details.',
                    'error_file_url' => asset('storage/' . $errorFilePath),
                    'fileName' => $fileName
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sales imported successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the sales.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /********************************************************************/
    public function addSale(Request $request)
    {
        $this->authenticateRole($module_page = 'sales');
        $page = 'sales';
        $areas = Area::get();
        $godowns = Godown::where('status', 'active')->get();
        $walkinCustomer = Customer::select('id', 'name')->first();
        // $invoice_data = Sale::orderBy('id', 'DESC')->first();
        // $dateTime = date('dmY');
        // if ($invoice_data && preg_match('/\d+-(\d+)$/', $invoice_data->invoice_no, $matches)) {
        //     $lastNumber = (int)$matches[1] + 1;
        // } else {
        //     $lastNumber = 1;
        // }

        // $invoice_no = $dateTime . '-' . $lastNumber;

        $username = '@' . Auth::user()->username;

        $lastInvoice = Sale::orderBy('id', 'DESC')->first();
        $nextInvoiceNo = $lastInvoice ? ($lastInvoice->invoice_no + 1) : 1;
        $invoice_no =  $nextInvoiceNo;
        $invoicePayments = [];
        if ($request->ajax()) {
            $assets = AssetHelper::getAssets('sales');
            return response()->json([
                'html' => view('sales.load_sale_form', compact('invoice_no', 'godowns', 'invoicePayments', 'walkinCustomer', 'username'))->render(),
                'scripts' => $assets['scripts'],
                'styles' => $assets['styles']
            ]);
        }

        return view('sales.create', compact('invoice_no', 'areas', 'page', 'godowns', 'invoicePayments', 'walkinCustomer', 'username'));
    }
    /********************************************************************/
    public function postSale(SaleRequest $request)
    {
        if ($request->ajax()) {
            $validatedData = $request->validated();

            try {
                DB::beginTransaction();

                // Check stock availability
                // $this->saleService->checkStock($validatedData);

                // Save sale and update stock if confirmed
                $sale = $this->saleService->saveSale($validatedData, null);

                if ($validatedData['status'] == 1) {
                    // Update stock for confirmed sale
                    $this->purchaseService->updateStock($validatedData['product_id'], $validatedData['quantity'], $validatedData['unit'], 'decrease');

                    // Process scheme for confirmed sale
                    $schemeResult = $this->schemeService->processOrder($sale);
                    // dd($schemeResult);
                    // Log scheme result for debugging
                    Log::info('Scheme processing result:', $schemeResult);
                }

                $lastInvoice = Sale::orderBy('id', 'DESC')->first();
                $nextInvoiceNo = $lastInvoice ? ($lastInvoice->invoice_no + 1) : 1;
                $invoice_no = $nextInvoiceNo;

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Invoice Successfully Created!',
                    'sale_id' => $sale->id,
                    'sale_type' => $request->sale_type,
                    'invoice_url' => route('pos.invoice', $sale->id),
                    'invoice_no' => $invoice_no,
                    'scheme_result' => $schemeResult ?? null
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Sale creation error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing the sale.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid request type.',
        ], 400);
    }
    /********************************************************************/
    public function searchInvoice(Request $request)
    {
        $invoice = Sale::with(['customer'])
            ->where('invoice_no', $request->invoice_no)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ]);
        }
        $data = $this->saleService->findInvoiceWithDetails($invoice->id);
        // Retrieve the logo parameter from the request

        // $customerBalance = ($data['totalAmount'] + $data['debitAmount']) - $data['creditAmount'];
        $customerBalance = round($this->saleService->customerTotalBalance($invoice->customer_id));

        // Unserialize items and enhance with product data
        $items = unserialize($invoice->items_addon) ?: [];
        // echo "<pre>"; print_r($items); exit;
        $enhancedItems = [];

        foreach ($items as $item) {
            $product = Product::with('units')->find($item['product_id']);

            $enhancedItems[] = [
                'value' => $product->name,
                'productID' => $product->id,
                'productName' => $product->name,
                'productQty' => $item['quantity'],
                'inputQty' => $item['quantity'],
                'stock' => $product->getDisplayStock(), // Custom method to format stock
                'code' => $product->product_code,
                'cost' => $item['cost'],
                'calculatedCost' => $item['calculatedCost'],
                'productUnit' => Unit::find($item['unit_id'])->name,
                'unit_id' => $item['unit_id'],
                'sellingPrice' => $item['selling_price'],
                'unitInfo' => $product->unit_info
            ];
        }
        // invoice payments history

        $invoicePaymentJistory = $this->saleService->invoicePaymentHistory($invoice);

        // echo "<pre>";  print_r($invoicePaymentJistory); "</pre>"; exit;
        $balanceAmount = round($invoice->grand_total - ($invoice->paid_amount + intval($this->saleService->partialPaidBySaleId($invoice->id))));
        // dd($balanceAmount);
        $balanceAmount = $balanceAmount > 0 ? $balanceAmount : 0;
        return response()->json([
            'success' => true,
            'update_url' => route('update.sale', $invoice->id),
            'customerBalance' => $customerBalance,
            'invoicePayments' => $invoicePaymentJistory,
            'invoice' => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'godown_id' => $invoice->godown_id,
                'date' => $invoice->date,
                'customer' => $invoice->customers,
                'status' => $invoice->status,
                'items' => $enhancedItems,
                'sub_total' => $invoice->sub_total,
                'discount' => $invoice->discount,
                'discount_type' => $invoice->discount_type,
                'other_charges' => $invoice->other_charges,
                'grand_total' => $invoice->grand_total,
                'paid_amount' => $invoice->paid_amount + $this->saleService->partialPaidBySaleId($invoice->id),
                'balance_amount' => $balanceAmount,
                'change_amount' => $invoice->change_amount,
                'payment_type' => $invoice->payment_type,
                'notes' => $invoice->description
            ]
        ]);
    }

    /********************************************************************/
    public function editSale(Request $request, $id = null)
    {
        $page = 'sales';
        $this->authenticateRole($module_page = 'sales');
        $data = $this->saleService->findInvoiceWithDetails($id);
        $due_date = $data['saleInvoice']['due_date'] !== null ? date('d-m-Y', strtotime($data['saleInvoice']['due_date'])) : date('d-m-Y');
        $updateSale = $data['saleInvoice'];
        $salesItems = $data['saleitmesAddons'];
        // $customerBalance = ($data['totalAmount'] + $data['debitAmount']) - $data['creditAmount'];
        $customerBalance = round($this->saleService->customerTotalBalance($data['saleInvoice']['customer_id']));
        // echo "<pre>"; print_r($salesItems); exit;
        $godowns = Godown::where('status', 'active')->get();
        $invoicePayments = $this->saleService->invoicePaymentHistory($data['saleInvoice']);
        $paidAmount = $data['saleInvoice']['paid_amount'] + $this->saleService->partialPaidBySaleId($data['saleInvoice']['id']);
        // dd($paidAmount);
        $enhancedItems = [];

        foreach ($salesItems as $item) {
            $product = Product::with('units')->find($item['product_id']);

            $enhancedItems[] = [
                'value' => $product->name,
                'productID' => $product->id,
                'productName' => $product->name,
                'productQty' => $item['quantity'],
                'stock' => $product->getDisplayStock(), // Custom method to format stock
                'code' => $product->product_code,
                'cost' => $item['cost'],
                'calculatedCost' => $item['calculatedCost'],
                'amount' => $item['amount'],
                'productUnit' => Unit::find($item['unit_id'])->name,
                'unit_id' => $item['unit_id'],
                'sellingPrice' => $item['selling_price'],
                'unitInfo' => $product->unit_info
            ];
        }
        // echo "<pre>"; print_r($enhancedItems); "</pre>"; exit;

        Session::put('page', 'viewInvoice');

        return view('sales.edit', compact('updateSale', 'salesItems', 'customerBalance', 'enhancedItems', 'page', 'godowns', 'due_date', 'invoicePayments', 'paidAmount'));
    }
    /********************************************************************/
    public function updateSale(SaleRequest $request, $id)
    {
        if ($request->ajax()) {
            $sale = Sale::with('customer')->findOrFail($id);
            $currentStatus = $sale->status;
            $validatedData = $request->validated();

            try {
                DB::beginTransaction();

                // Handle status change logic
                if (in_array($validatedData['status'], [0, 2, 3])) { // Cancel or Pending or Return
                    $this->handleCancelOrPendingSale($sale, $validatedData, $currentStatus);
                } elseif ($validatedData['status'] == 1) { // Confirmed Sale
                    $this->handleConfirmedSale($sale, $validatedData, $currentStatus);
                }

                $invoice_data = Sale::select('id', 'invoice_no')->orderBy('id', 'DESC')->first();
                $dateTime = date('dmY');
                if ($invoice_data && preg_match('/\d+-(\d+)$/', $invoice_data->invoice_no, $matches)) {
                    $lastNumber = (int)$matches[1] + 1;
                } else {
                    $lastNumber = 1;
                }

                $invoice_no = $dateTime . '-' . $lastNumber;

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice Successfully Updated!',
                    'sale_id' => $sale->id,
                    'sale_type' => $validatedData['sale_type'],
                    'invoice_url' => route('pos.invoice', $sale->id),
                    'url' => route('sales'),
                    'invoice_no' => $invoice_no
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Sale update error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing the sale.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }
        return response()->json([
            'success' => false,
            'message' => 'Invalid request type.',
        ], 400);
    }
    /********************************************************************/
    private function handleCancelOrPendingSale($sale, $validatedData, $currentStatus)
    {
        CustomerPayment::where('sale_id', $sale->id)->forceDelete();
        if ($currentStatus == 1) { // Previously confirmed
            $this->saleService->restoreStock($sale);
        }
        $this->saleService->saveSale($validatedData, $sale->id);
        $sale->update(['status' => $validatedData['status']]);
    }
    /********************************************************************/
    private function handleConfirmedSale($sale, $validatedData, $currentStatus)
    {
        CustomerPayment::where('sale_id', $sale->id)->onlyTrashed()->restore();
        if ($currentStatus == 1) { // Already confirmed
            // echo "<pre>"; print_r("already confirmed"); exit;
            $this->saleService->restoreStock($sale);
        }
        // echo "<pre>"; print_r($currentStatus); exit;
        $this->saleService->saveSale($validatedData, $sale->id);
        $this->purchaseService->updateStock($validatedData['product_id'], $validatedData['quantity'], $validatedData['unit'], 'decrease');
        $sale->update(['status' => 1]);
    }

    /********************************************************************/
    // New method to handle redemption during sale
    public function redeemSchemeAmount(SaleRequest $request)
    {
        if ($request->ajax()) {
            $validatedData = $request->validated();

            try {
                DB::beginTransaction();

                // Create sale first
                $sale = $this->saleService->saveSale($validatedData, null);

                if ($validatedData['status'] == 1) {
                    // Update stock
                    $this->purchaseService->updateStock($validatedData['product_id'], $validatedData['quantity'], $validatedData['unit'], 'decrease');

                    // Process redemption if requested
                    if (isset($validatedData['redeem_amount']) && $validatedData['redeem_amount'] > 0) {
                        $redeemResult = $this->schemeService->redeemForOrder($sale, $validatedData['redeem_amount']);

                        if ($redeemResult) {
                            // Apply discount to sale total
                            $sale->total_amount -= $validatedData['redeem_amount'];
                            $sale->discount_amount = ($sale->discount_amount ?? 0) + $validatedData['redeem_amount'];
                            $sale->save();
                        }
                    }

                    // Process scheme accumulation
                    $schemeResult = $this->schemeService->processOrder($sale);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Sale processed successfully with scheme redemption!',
                    'sale_id' => $sale->id,
                    'redeemed' => $redeemResult ?? false,
                    'scheme_result' => $schemeResult ?? null
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing the sale with redemption.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }
    }

    /********************************************************************/

    public function saleInvoice($id)
    {
        $this->authenticateRole('sales');

        try {
            Session::put('page', 'viewInvoice');

            $data = $this->saleService->findInvoiceWithDetails($id);
            // $customerBalance = ($this->saleService->customerTotalBalance($data['saleInvoice']->customer_id) + $data['saleInvoice']['paid_amount']) - $data['saleInvoice']['grand_total'];
            $customerBalance = ($this->saleService->customerTotalBalanceExcludedInvoice($data['saleInvoice']->customer_id, $data['saleInvoice']['id']));

            $settings = SiteSetting::pluck('value', 'key');
            // echo "<pre>"; print_r($data['saleInvoice']); exit;
            return view('invoices.sale-invoice', [
                'saleInvoice' => $data['saleInvoice'],
                'saleitmesAddons' => $data['saleitmesAddons'],
                'customerBalance' => $customerBalance,
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /********************************************************************/
    public function printSaleInvoice(Request $request, $id = null)
    {
        try {
            $print_footer = "print_footer";
            $data = $this->saleService->findInvoiceWithDetails($id);
            // Retrieve the logo parameter from the request
            $selectedLogo = $request->query('logo', 'invoice_logo'); // Default to 'invoice_logo' if not provided
            $customerBalance = ($this->saleService->customerTotalBalanceExcludedInvoice($data['saleInvoice']->customer_id, $data['saleInvoice']['id']));


            $settings = SiteSetting::pluck('value', 'key');
            // Dynamically update the logo in the settings array based on the selected logo
            if ($selectedLogo === 'invoice_logo2') {
                $settings['invoice_logo'] = $settings['invoice_logo2'] ?? 'images/invoice_logo2.png';
            } elseif ($selectedLogo === 'invoice_logo') {
                $settings['invoice_logo'] = $settings['invoice_logo'] ?? 'images/invoice_logo.png';
            }
            $page =  ($settings['billing_language'] == 'urdu') ? 'print_invoice.single_sale.print_urdu' : 'print_invoice.single_sale.print_eng';
            return view($page, [
                'print_footer' => $print_footer,
                'saleInvoice' => $data['saleInvoice'],
                'saleitmesAddons' => $data['saleitmesAddons'],
                'customerBalance' => $customerBalance,
                'settings' => $settings
            ]);
        } catch (Exception $e) {
            Session::flash('flash_message_error', "Oops, Something went wrong. Try again");
            return redirect()->back()->with($e->getMessage());
        }
    }
    /********************************************************************/
    public function bulkPrintSales(Request $request)
    {
        try {
            // Retrieve the sale IDs from the request.
            // They can be passed as a comma-separated string or an array.
            $ids = $request->ids;
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }

            if (empty($ids)) {
                throw new Exception('No sale IDs provided for bulk print.');
            }

            $print_footer = "print_footer";
            // Retrieve the logo parameter from the request
            $selectedLogo = $request->query('logo', 'invoice_logo'); // Default to 'invoice_logo' if not provided

            // Initialize an array to store each invoice's details.
            $invoices = [];

            foreach ($ids as $id) {
                // Get data for each sale invoice.
                $data = $this->saleService->findInvoiceWithDetails($id);

                // Calculate the customer balance for this invoice.
                $customerBalance = ($this->saleService->customerTotalBalanceExcludedInvoice($data['saleInvoice']->customer_id, $data['saleInvoice']['id']));
                $data['customerBalance'] = $customerBalance;

                // Add the invoice data to the invoices array.
                $invoices[] = $data;
            }
            // echo "<pre>"; print_r($invoices); exit;

            // Retrieve site settings and update the logo based on the selected option.
            $settings = SiteSetting::pluck('value', 'key')->toArray();
            if ($selectedLogo === 'invoice_logo2') {
                $settings['invoice_logo'] = $settings['invoice_logo2'] ?? 'images/invoice_logo2.png';
            } elseif ($selectedLogo === 'invoice_logo') {
                $settings['invoice_logo'] = $settings['invoice_logo'] ?? 'images/invoice_logo.png';
            }

            $page =  ($settings['billing_language'] == 'urdu') ? 'print_invoice.bulk_sale.bulk_print_urdu' : 'print_invoice.bulk_sale.bulk_print_eng';

            // Pass all the collected invoices to a bulk print view.
            return view($page, [
                'print_footer' => $print_footer,
                'invoices'     => $invoices,
                'settings'     => $settings
            ]);
        } catch (Exception $e) {
            Session::flash('flash_message_error', "Oops, Something went wrong. Try again");
            return redirect()->back()->with($e->getMessage());
        }
    }

    /********************************************************************/
    public function posSaleInvoice($id = null)
    {
        try {
            $print_footer = "print_footer";
            $data = $this->saleService->findInvoiceWithDetails($id);
            $settings = SiteSetting::pluck('value', 'key');
            $page =  ($settings['billing_language'] == 'urdu') ? 'print_invoice.pos.pos_urdu' : 'print_invoice.pos.pos_eng';
            // dd($this->saleService->customerTotalBalanceExcludedInvoice($data['saleInvoice']->customer_id, $data['saleInvoice']['id']));
            return view($page, [
                'print_footer' => $print_footer,
                'saleInvoice' => $data['saleInvoice'],
                'saleitmesAddons' => $data['saleitmesAddons'],
                'customerBalance' => ($this->saleService->customerTotalBalanceExcludedInvoice($data['saleInvoice']->customer_id, $data['saleInvoice']['id'])),
                'settings' => $settings
            ]);
        } catch (Exception $e) {
            Session::flash('flash_message_error', "Oops, Something went wrong. Try again");
            return redirect()->back()->with($e->getMessage());
        }
    }

    /********************************************************************/
    public function deleteSale(Request $request, $id = null)
    {
        if ($request->ajax()) {
            try {
                $sale = Sale::findOrFail($id);

                // Call the delete method to trigger the `deleting` event
                $sale->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice Successfully Deleted!',
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing the sale.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }
    }
    /********************************************************************/
}
