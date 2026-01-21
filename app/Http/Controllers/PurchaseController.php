<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use Illuminate\Http\Request;
use App\Services\PurchaseService;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Unit;
use App\SiteSetting;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class PurchaseController extends Controller
{
    protected $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
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
        $this->authenticateRole($module_page = 'purchase');
        return view('purchase.view');
    }
    /********************************************************************/
    public function purchaseList(Request $request)
    {
        if ($request->ajax()) {
            $data = Purchase::with(['supplier:id,name', 'users:id,name'])
                ->select('id', 'purchase_no', 'date', 'supplier_id', 'grand_total', 'status', 'createdBy')
                ->orderByDesc('id'); // Use `orderByDesc` for clarity

            return DataTables::of($data)
                ->addIndexColumn() // Adds the row index
                ->editColumn('date', function ($row) {
                    return $row->date ? date('d-m-Y | h:i A', strtotime($row->date)) : 'N/A'; // Safer date formatting
                })
                ->addColumn('supplier', function ($row) {
                    return optional($row->supplier)->name; // Use `optional` for cleaner null handling
                })
                ->editColumn('status', function ($row) {
                    if ($row->status === 'cancel') {
                        return '<span class="badge badge-danger" style="width: 100px;">CANCELLED</span>';
                    } elseif ($row->status === 'received') {
                        return '<span class="badge badge-success" style="width: 100px;">RECEIVED</span>';
                    } else {
                        return '<span class="badge badge-warning" style="width: 100px;">PENDING</span>';
                    }
                })
                ->addColumn('createdBy', function ($row) {
                    return optional($row->users)->name; // Cleaner user relationship handling
                })
                ->addColumn('action', function ($row) {
                    // Dropdown action menu
                    $actions = '
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="actionMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Action
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="actionMenu">
                        <a class="dropdown-item" href="' . route('edit.purchase', $row->id) . '">
                            <i class="fas fa-edit text-blue"></i> Edit
                        </a>
                        <a class="dropdown-item" href="' . route('purchase.invoice', $row->id) . '">
                            <i class="fas fa-file-invoice text-gray"></i> Purchase Invoice
                        </a>';
                    if ($row->status == 'cancel') { // Conditional delete option for pending sales
                        $actions .= '
                        <a class="dropdown-item delete" href="javascript:void(0);" data-url="' . route('delete.purchase', $row->id) . '">
                            <i class="fas fa-trash text-red"></i> Delete
                        </a>';
                    }
                    $actions .= '</div></div>';
                    return $actions;
                })
                ->rawColumns(['status', 'action']) // Ensure HTML rendering for these columns
                ->make(true);
        }

        return response()->json(['status' => false, 'message' => 'Invalid request'], 400);
    }
    /********************************************************************/
    public function purchaseInvoice($id)
    {
        $this->authenticateRole('purchase');
        try {
            Session::put('page', 'viewInvoice');

            $data = $this->purchaseService->findPurchaseInvoiceWithDetails($id);
            $supplierBalance = $data['totalAmount'] - $data['supplierPayment'];
            $settings = SiteSetting::pluck('value', 'key');
            return view('invoices.purchase-invoice', [
                'purchaseInvoice' => $data['purchaseInvoice'],
                'purchaseitmesAddons' => $data['purchaseitmesAddons'],
                'supplierBalance' => $supplierBalance,
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /********************************************************************/
    public function printPurchaseInvoice(Request $request, $id)
    {
        $this->authenticateRole('purchase');
        try {
            Session::put('page', 'viewInvoice');
            $selectedLogo = $request->query('logo', 'invoice_logo'); // Default to 'invoice_logo' if not provided
            $data = $this->purchaseService->findPurchaseInvoiceWithDetails($id);
            $supplierBalance = $data['totalAmount'] - $data['supplierPayment'];
            $settings = SiteSetting::pluck('value', 'key');
            // Dynamically update the logo in the settings array based on the selected logo
            if ($selectedLogo === 'invoice_logo2') {
                $settings['invoice_logo'] = $settings['invoice_logo2'] ?? 'images/invoice_logo2.png';
            } elseif ($selectedLogo === 'invoice_logo') {
                $settings['invoice_logo'] = $settings['invoice_logo'] ?? 'images/invoice_logo.png';
            }
            return view('print_invoice.purchase-invoice', [
                'purchaseInvoice' => $data['purchaseInvoice'],
                'purchaseitmesAddons' => $data['purchaseitmesAddons'],
                'supplierBalance' => $supplierBalance,
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /********************************************************************/
    public function addPurchase(Request $request)
    {
        // Authenticate the user's role for the purchase module
        $this->authenticateRole('purchase');

        $invoice_data = Purchase::orderBy('id', 'DESC')->first();
        $dateTime = date('dmY');
        if ($invoice_data && preg_match('/\d+-(\d+)$/', $invoice_data->purchase_no, $matches)) {
            $lastNumber = (int)$matches[1] + 1;
        } else {
            $lastNumber = 1;
        }

        $purchase_no = $dateTime . '-' . $lastNumber;

        return view('purchase.create', [
            'purchase_no' => $purchase_no,
        ]);
    }

    /*********************************************************************/
    public function postPurchase(PurchaseRequest $request)
    {
        if ($request->ajax()) {
            $validatedData = $request->validated();
            try {
                DB::beginTransaction();

                // Save purchase and update stock if confirmed
                $purchase = $this->purchaseService->savePurchase($validatedData, null);
                if ($validatedData['status'] == 'received') {
                    $this->purchaseService->updateStock($validatedData['product_id'], $validatedData['quantity'], $validatedData['unit'], 'increase');
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Invoice Successfully Created!',
                    'url' => route('purchase.invoice', $purchase->id)
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
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
    public function editPurchase(Request $request, $id = null)
    {
        $this->authenticateRole($module_page = 'sales');
        $data = $this->purchaseService->findPurchaseInvoiceWithDetails($id);
        $updatePurchase = $data['purchaseInvoice'];
        $purchaseItems = $data['purchaseitmesAddons'];
        $customerBalance = $data['totalAmount'] - $data['supplierPayment'];
        $enhancedItems = [];

        foreach ($purchaseItems as $item) {
            $product = Product::with('units')->find($item['product_id']);

            $enhancedItems[] = [
                'value' => $product->name,
                'product_id' => $product->id,
                'productName' => $product->name,
                'code' => $product->product_code,
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'amount' => $item['amount'],
                'productUnit' => Unit::find($item['unit_id'])->name,
                'unit_id' => $item['unit_id'],
                'unitInfo' => $product->unit_info
            ];
        }

        return view('purchase.edit', compact('updatePurchase', 'purchaseItems', 'customerBalance','enhancedItems'));
    }
    /********************************************************************/
    public function updatePurchase(PurchaseRequest $request, $id)
    {
        if ($request->ajax()) {

            $purchase = Purchase::with('supplier')->findOrFail($id);
            $currentStatus = $purchase->status;
            $validatedData = $request->validated();
// echo "<pre>"; print_r($validatedData); "</pre>"; exit;
            try {
                DB::beginTransaction();

                // Handle status change logic
                if (in_array($validatedData['status'], ['cancel', 'pending'])) { // Cancel or Pending
                    $this->handleCancelOrPendingPurchase($purchase, $validatedData, $currentStatus);
                } elseif ($validatedData['status'] == 'received') { // Confirmed Sale

                    $this->handleConfirmedPurchase($purchase, $validatedData, $currentStatus);
                }

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice Successfully Updated!',
                    'url' => route('purchases')
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing the purchase.',
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
    private function handleCancelOrPendingPurchase($purchase, $validatedData, $currentStatus)
    {
        if ($currentStatus == 'received') { // Previously confirmed
            $this->purchaseService->stockOut($purchase);
        }
        $this->purchaseService->savePurchase($validatedData, $purchase->id);
        $purchase->update(['status' => $validatedData['status']]);
    }
    /********************************************************************/
    private function handleConfirmedPurchase($purchase, $validatedData, $currentStatus)
    {

        if ($currentStatus == 'received') { // Already confirmed
            $this->purchaseService->stockOut($purchase);
        }
        $this->purchaseService->savePurchase($validatedData, $purchase->id);
        $this->purchaseService->updateStock($validatedData['product_id'], $validatedData['quantity'], $validatedData['unit'], 'increase');
        $purchase->update(['status' => 'received']);
    }

    /*==================================================================================*/
    // public function searchRawProducts(Request $request)
    // {
    //     if ($request->ajax()) {
    //         // Fetch only the required fields to reduce data load
    //         $products = Product::with(['units:id,name']) // Only load necessary fields
    //             ->select(['id', 'name', 'product_code', 'quantity', 'qtyPerUnit', 'item_cost', 'cost', 'item_selling_price', 'selling_price', 'unit_id']) // Only select the required columns
    //             ->where('name', 'LIKE', "%" . $request->term . "%")
    //             ->orWhere('product_code', 'LIKE', "%" . $request->term . "%")
    //             ->get();

    //         if ($products->isNotEmpty()) {
    //             $nakasiProduct = $products->map(function ($product) {
    //                 return [
    //                     "value" => $product->name,
    //                     "productID" => $product->id,
    //                     "productName" => $product->name,
    //                     "productQty" => $product->quantity,
    //                     "code" => $product->product_code,
    //                     "purchasePrice" => $product->cost,
    //                     "productUnit" => optional($product->units)->name, // Use optional to avoid errors
    //                 ];
    //             });

    //             return response()->json($nakasiProduct);
    //         }

    //         return response()->json(['message' => 'No products found matching your search.'], 404);
    //     }
    // }
    /********************************************************************/
    public function searchRawProducts(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['message' => 'Invalid request'], 400);
        }

        $searchTerm = $request->term;

        $products = Product::query()
            ->select([
                'id',
                'name',
                'product_code',
                'quantity',
                'cost',
                'unit_info'
            ])
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('product_code', 'LIKE', "%{$searchTerm}%");
            })
            ->orderBy('name')
            ->limit(15)
            ->get()
            ->map(function ($product) {
                // Decode the unit_info JSON and find the default unit
                $unitInfo = $product->unit_info;
                $defaultUnit = null;

                foreach ($unitInfo as $unit) {
                    if (isset($unit['is_default']) && $unit['is_default'] === true) {
                        $defaultUnit = $unit;
                        break;
                    }
                }

                // If no default found, try to get the first unit
                if (!$defaultUnit && !empty($unitInfo)) {
                    $defaultUnit = reset($unitInfo);
                }

                $stock = $product->getDisplayStock();
                //  echo "<pre>"; print_r($stock); "</pre>";
                return [
                    "value"          => $product->name,
                    "productID"      => $product->id,
                    "productName"   => $product->product_code . ' - ' . $product->name,
                    "productQty"     => $stock,
                    "code"           => $product->product_code,
                    "purchasePrice"  => $defaultUnit['purchase_price'] ?? 0,
                    "productUnit"    => $defaultUnit['unit'] ?? null, // Use the unit title/name
                    "unit_id"       => $defaultUnit['unit_id'] ?? null,
                    "selling_price" => $defaultUnit['selling_price'] ?? null,
                    'unitInfo' => $unitInfo
                ];
            });

        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'No products found matching "' . $searchTerm . '"',
                'results' => []
            ], 200);
        }

        $settings = SiteSetting::pluck('value', 'key');
        return response()->json([
            'results' => $products,
            'currency' => optional($settings)['currency']
        ]);
    }

    public function searchRawProducts_xxx(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['message' => 'Invalid request'], 400);
        }

        $searchTerm = $request->term;

        $products = Product::query()
            ->with(['units:id,name']) // Eager load only necessary relationship fields
            ->select([
                'id',
                'name',
                'product_code',
                'quantity',
                'cost',
                'unit_id'
            ])
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('product_code', 'LIKE', "%{$searchTerm}%");
            })
            ->orderBy('name')
            ->limit(15) // Prevent excessive results
            ->get()
            ->map(function ($product) {
                return [
                    "value"          => $product->name,
                    "productID"      => $product->id,
                    "productName"   => $product->product_code . ' - ' . $product->name,
                    "productQty"     => $product->quantity,
                    "code"           => $product->product_code,
                    "purchasePrice"  => $product->cost,
                    "productUnit"    => $product->units->name ?? null,
                ];
            });

        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'No products found matching "' . $searchTerm . '"',
                'results' => []
            ], 200);
        }
        $settings = SiteSetting::pluck('value', 'key');
        return response()->json([
            'results' => $products,
            'currency' => optional($settings)['currency']
        ]);
    }
    /********************************************************************/
    public function deletePurchase(Request $request, $id = null)
    {
        if ($request->ajax()) {
            try {

                $purchase = Purchase::findOrFail($id);
                // Call the delete method to trigger the `deleting` event
                $purchase->delete();
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
}
