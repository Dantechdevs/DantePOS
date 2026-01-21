<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierPaymentRequest;
use App\Http\Requests\SupplierRequest;
use Illuminate\Http\Request;
use App\Services\PurchaseService;
use Exception;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class SupplierController extends Controller
{
    protected $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }
    /*====================================*/
    public function authenticateRole($module_page)
    {
        $permissionCheck =  checkRolePermission($module_page);
        if ($permissionCheck->access == 0) {

            return redirect()->to('/dashboard')->send()->with('error', 'You have no permission!');
        }
    }
    /*===================================================*/
    public function index()
    {
        $this->authenticateRole($module_page = 'supplier');
        Session::put('page', 'suppliers');
        $suppliers = Supplier::get();
        return view('suppliers.view', compact('suppliers'));
    }
    /********************************************************************/
    public function suppliersList(Request $request)
    {
        if ($request->ajax()) {
            $data = Supplier::with(['user:id,name'])->select('id', 'name', 'mobile', 'opening_balance', 'createdBy')
                ->orderBy('id', 'desc'); // Order by 'id' in descending order
            // ->get();

            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('balance', function ($row) {
                    $balance = ($this->purchaseService->calculateTotalAmount($row->id) + $row->opening_balance) - ($this->purchaseService->supplierPayment($row->id));
                    $balance = round($balance);
                    $balance = $balance < 0
                        ? -1 * $balance . ' CR'
                        : ($balance > 0 ? $balance . ' DB' : $balance);
                    return $balance; // Format the date as 'dd-MMM-YYYY'
                })

                ->addColumn('createdBy', function ($row) {
                    return $row->user ? $row->user->name : ''; // Format the date as 'dd-MMM-YYYY'
                })
                ->addColumn('action', function ($row) {
                    return '<a href="javascript:void(0);" class="btn btn-warning btn-sm view" data-url="' . route('supplier.view', $row->id) . '" data-id="' . $row->id . '" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                    <a href="javascript:void(0);" class="btn btn-info btn-sm editSupplier" data-url="' . route('load.supplier.form', $row->id) . '" data-saveSupplierUrl="' . route('create.supplier', $row->id) . '" title="Edit?">
                            <i class="fas fa-pen"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-danger btn-sm delete" data-url="' . route('delete.supplier', $row->id) . '" data-id="' . $row->id . '" title="Delete">
                            <i class="fas fa-trash"></i>
                        </a>';
                })
                ->rawColumns(['action']) // Ensure raw HTML rendering for the 'action' column
                ->make(true);
        }
    }
    /********************************************************************/
    public function supplierView(Request $request, $id = null)
    {
        if ($request->ajax()) {
            $supplier = [];
            if ($id) {
                $supplier = Supplier::find($id);
                $balance = $supplier->opening_balance + ($this->purchaseService->calculateTotalAmount($supplier->id) - $this->purchaseService->supplierPayment($supplier->id));
                $balance = $balance < 0
                    ? -1 * $balance . ' CR'
                    : ($balance > 0 ? $balance . ' DB' : $balance);
                return response()->json([
                    'success' => true,
                    'html' => view('suppliers.includes.supplier_details_modal', compact('supplier', 'balance'))->render()
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
    public function loadSupplierFrom(Request $request, $id = null)
    {
        if ($request->ajax()) {
            $permissionCheck =  checkRolePermission('supplier');
            $available_days =  [];
            if ($permissionCheck->access == 0) {
                return response()->json(['success' => false, 'message' => 'You have no permission!']);
            }
            $supplier = [];
            if ($id) {
                $supplier = Supplier::find($id);
                $available_days = $supplier->available_days ? explode(',', $supplier->available_days) : [];
            }

            // echo "<pre>"; print_r($supplier->available_days); exit();
            return response()->json([
                'success' => true,
                'html' => view('suppliers.includes.create_supplier_modal', compact('supplier', 'available_days'))->render()
            ]);
        }
        return response()->json(['status' => false, 'message' => 'Invalid request']);
    }
    /********************************************************************/
    public function createSupplier(SupplierRequest $request, $id = null)
    {
        if ($request->ajax()) {
            $validatedData = $request->validated();
            // echo "<pre>"; print_r($validatedData); exit();
            try {
                if ($id) {
                    $supplier =  $this->storeSupplier($validatedData, $id);
                } else {
                    $supplier =  $this->storeSupplier($validatedData, null);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Save Changes Successfully ',
                    'supplierName' => $validatedData['name'],
                    "supplierID" => $supplier->id,
                    "supplierBalance" => 0
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
    private function storeSupplier($validatedData, $id)
    {
        $validatedData['createdBy'] = Auth::id();
        $validatedData['available_days'] = isset($validatedData['available_days']) ? implode(',', $validatedData['available_days']) : null;
        if ($id) {
            $supplier = Supplier::findOrFail($id);
            $supplier->update($validatedData);
            return $supplier;
        } else {
            return Supplier::create($validatedData);
        }
    }
    /********************************************************************/

    public function searchSupplier(Request $request)
    {
        if ($request->ajax()) {
            $supplierAuto = [];
            $suppliers = Supplier::where('name', 'LIKE', "%" . $request->term . "%")->orWhere('mobile', 'LIKE', "%" . $request->term . "%")->paginate(5);

            if ($suppliers) {
                foreach ($suppliers as $key => $supplier) {

                    $supplierAuto[] = array(
                        "value" => $supplier->name,
                        "supplierID" => $supplier->id,
                        "supplierName" => $supplier->name,
                        "mobile" => $supplier->mobile,
                        "supplierBalance" => $supplier->opening_balance + ($this->purchaseService->calculateTotalAmount($supplier->id) - $this->purchaseService->supplierPayment($supplier->id))
                    );
                }
                return response()->json($supplierAuto);
                // $dataRetrun = json_encode($supplierAuto);
                // return Response($dataRetrun);
            }
        }
    }

    /********************************************************************/
    public function supplierPayment()
    {
        $this->authenticateRole('suppliers_payment');
        return view('suppliers.payments.debit_amount');
    }
    /********************************************************************/
    public function supplierPaymentsList(Request $request)
    {
        if ($request->ajax()) {
            $data = SupplierPayment::with(['users:id,name', 'supplier:id,name'])
                ->select(
                    'supplier_payments.id', // Explicitly specify the table for 'id'
                    'supplier_payments.purchase_no',
                    'supplier_payments.date',
                    'supplier_payments.supplier_id',
                    'supplier_payments.amount',
                    'supplier_payments.createdBy'
                )
                ->orderBy('supplier_payments.id', 'desc'); // Order by 'id' in descending order
            // ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    return date('d-m-Y | h:i A', strtotime($row->date));
                })
                ->addColumn('supplier', function ($row) {
                    return $row->supplier ? $row->supplier->name : '';
                })

                ->addColumn('createdBy', function ($row) {
                    return $row->users ? $row->users->name : '';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="javascript:void(0);" class="btn btn-warning btn-sm view" data-url="' . route('supplier.payments.view', $row->id) . '" data-id="' . $row->id . '" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                    <a href="javascript:void(0);" class="btn btn-info btn-sm editSupplierPayment" data-url="' . route('load.supplier.payment.form', $row->id) . '" data-saveSupplierPaymentUrl="' . route('store.supplier.payment', $row->id) . '" title="Edit?">
                            <i class="fas fa-pen"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-danger btn-sm delete" data-url="' . route('delete.supplier.payment', $row->id) . '" data-id="' . $row->id . '" title="Delete">
                            <i class="fas fa-trash"></i>
                        </a>';
                })
                ->rawColumns(['action']) // Ensure raw HTML rendering for the 'action' column
                ->make(true);
        }
    }
    /********************************************************************/
    public function loadSupplierPaymentFrom(Request $request, $id = null)
    {
        if (!$request->ajax()) {
            return response()->json(['status' => false, 'message' => 'Invalid request'], 400);
        }

        // Retrieve the latest voucher number in a single query
        $latestVoucher = SupplierPayment::latest('id')->value('purchase_no');
        $voucher_no = $latestVoucher ? $latestVoucher + 1 : 1;

        // Fetch the customer payment if an ID is provided
        $supplierPayment = $id
            ? SupplierPayment::with(['users:id,name', 'supplier:id,name'])
            ->select('id', 'purchase_no', 'date', 'supplier_id', 'amount', 'description', 'createdBy')
            ->find($id)
            : null;
        $balance = 0;
        if ($supplierPayment) {
            $balance = ($this->purchaseService->calculateTotalAmount($supplierPayment->supplier_id)) - ($this->purchaseService->supplierPayment($supplierPayment->supplier_id));
        }
        // Render the modal HTML and return the response
        $html = view('suppliers.includes.payment_modal', compact('voucher_no', 'supplierPayment', 'balance'))->render();

        return response()->json([
            'success' => true,
            'id' => $id,
            'html' => $html,
        ]);
    }

    /********************************************************************/
    public function addSupplierPayment(SupplierPaymentRequest $request, $id = null)
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
            $payment = SupplierPayment::findOrFail($id);
            $payment->update($validatedData);
            return $payment;
        } else {
            return SupplierPayment::create($validatedData);
        }
    }
    /********************************************************************/
    public function supplierPaymentsView(Request $request, $id = null)
    {
        if ($request->ajax()) {
            $supplierPayment = [];
            if ($id) {
                $supplierPayment = SupplierPayment::with(['users:id,name', 'supplier:id,name'])->select('id', 'purchase_no', 'date', 'supplier_id', 'description', 'amount', 'createdBy')->find($id);
                $balance = ($this->purchaseService->calculateTotalAmount($supplierPayment->supplier_id)) - ($this->purchaseService->supplierPayment($supplierPayment->supplier_id));
                $balance = $balance < 0
                    ? -1 * $balance . ' CR'
                    : ($balance > 0 ? $balance . ' DB' : $balance);
                return response()->json([
                    'success' => true,
                    'html' => view('suppliers.includes.payment_details_modal', compact('supplierPayment', 'balance'))->render()
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
    /*==================================================*/
    public function deleteSupplierPayment(Request $request, $id = null)
    {
        if ($request->ajax()) {
            try {
                SupplierPayment::find($id)->delete();
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

    /*=======================================================*/
    public function deleteSupplier(Request $request, $id = null)
    {
        if ($request->ajax()) {
            try {
                Supplier::find($id)->delete();
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

        // $this->authenticateRole($module_page = 'supplier');
        // $getSupplier = Purchase::select('supplier_id')->where('supplier_id', $id)->first();
        // // echo "<pre>"; print_r($getSales); exit();
        // if ($getSupplier) {
        //     return redirect()->back()->with('error', 'Supplier cannot deleted!');
        // } else {
        //     Supplier::find($id)->delete();
        //     return redirect()->back()->with('success', 'Supplier Successfully deleted');
        // }
    }
    /****************************************************************/
    public function getSuppliersByDay($day)
    {
        try {
            // Get all suppliers where available_days contains the selected day
            $suppliers = Supplier::where('available_days', 'LIKE', "%{$day}%")
                ->select('id', 'name', 'mobile', 'available_days', 'opening_balance')
                ->get();

            // Calculate balance for each supplier
            $suppliersWithBalance = $suppliers->map(function ($supplier) {
                $balance = ($this->purchaseService->calculateTotalAmount($supplier->id) + $supplier->opening_balance) - ($this->purchaseService->supplierPayment($supplier->id));
                $balance = round($balance);

                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'mobile' => $supplier->mobile,
                    'balance' => $balance,
                    'available_days' => $supplier->available_days,
                    'opening_balance' => $supplier->opening_balance
                ];
            });

            return response()->json([
                'success' => true,
                'suppliers' => $suppliersWithBalance,
                'count' => $suppliersWithBalance->count(),
                'day' => $day
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching suppliers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
