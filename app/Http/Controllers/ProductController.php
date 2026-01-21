<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Unit;
use Exception;
use App\Http\Helpers\ProductHelper;
use App\Models\Supplier;
use App\SiteSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class ProductController extends Controller
{
    /*====================================*/
    public function authenticateRole($module_page)
    {
        $permissionCheck = checkRolePermission($module_page);
        if ($permissionCheck->access == 0) {

            return redirect()->to('/dashboard')->send()->with('error', 'You have no permission!');
        }
    }
    /*===================================================*/
    public function index()
    {
        $this->authenticateRole($module_page = 'product');
        Session::put('page', 'viewProducts');
        // $products = Product::with(['users', 'units'])->get();
        $units = Unit::get();
        $suppliers = Supplier::select('id', 'name')->get();
        $currency = optional(SiteSetting::where('key', 'currency')->first())->value ?? '';

        //         $totalPiecesSold = 5; // Number of pieces sold
        //         $piecesPerBox = 6; // Number of pieces per box

        //         // Call the helper function to calculate sold boxes and remaining pieces
        //         $result = ProductHelper::calculateSoldAndRemaining($totalPiecesSold, $piecesPerBox);

        //         $boxesSold = $result['boxes_sold'];
        //         $remainingPieces = $result['remaining_pieces'];
        // dd("sold box: " . $boxesSold . '------- Sold Pieces: '. $remainingPieces);
        return view('products.view', compact('units', 'currency', 'suppliers'));
    }
    /********************************************************************/
    public function productsList(Request $request)
    {
        if ($request->ajax()) {
            $data = Product::with(['users:id,name', 'supplier:id,name'])
                ->select(
                    'products.id', // Explicitly specify the table for 'id'
                    'products.product_code',
                    'products.name',
                    'products.quantity',
                    'products.unit_info',
                    'products.supplier_id',
                    'products.createdBy'
                )
                ->orderBy('products.id', 'desc'); // Order by 'id' in descending order
            // ->get();
            // echo "<pre>"; print_r($data->toArray()); exit;
            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('stock', function ($row) {
                    return   $row->getDisplayStock();
                })
                ->addColumn('supplier_data', function ($row) {
                    if (empty($row->supplier_id)) {
                        return 'N/A';
                    }

                    $supplierIds = explode(',', $row->supplier_id);
                    $suppliers = Supplier::select('id', 'name')
                        ->whereIn('id', $supplierIds)
                        ->get()
                        ->pluck('name')
                        ->toArray();

                    return implode(', ', $suppliers) ?: 'N/A';
                })
                ->filterColumn('status', function ($query, $keyword) {
                    if (strtolower($keyword) === 'active') {
                        $query->where('status', 1);
                    } elseif (strtolower($keyword) === 'inactive') {
                        $query->where('status', 0);
                    }
                })
                ->addColumn('createdBy', function ($row) {
                    return $row->users ? $row->users->name : '';
                })
                ->filterColumn('createdBy', function ($query, $keyword) {
                    $query->whereHas('users', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('action', function ($row) {
                    return '<a href="javascript:void(0);" data-url="' . route('edit.product', $row->id) . '" data-update-url="' . route('update.product', $row->id) . '" class="btn btn-info btn-sm editProduct" title="Edit?">
                          <i class="fas fa-pen"></i>
                      </a>
                      <a href="javascript:void(0);" class="btn btn-danger btn-sm delete" data-url="' . route('delete.product', $row->id) . '" data-id="' . $row->id . '" title="Delete">
                          <i class="fas fa-trash"></i>
                      </a>';
                })
                ->rawColumns(['status', 'suppliers', 'action']) // Ensure raw HTML rendering for the 'action' column
                ->make(true);
        }
    }
    /********************************************************************/
    public function lowStockProductsList(Request $request)
    {
        if ($request->ajax()) {
            $data = Product::whereRaw('quantity <= stock_alert')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    $status = $row->quantity <= $row->stock_alert ? 'Low Stock' : 'Normal';
                    $badgeClass = $row->quantity <= $row->stock_alert ? 'badge badge-low-stock' : 'badge badge-success';
                    return '<span class="' . $badgeClass . '">' . $status . '</span>';
                })
                ->addColumn('product', function ($row) {
                    return $row->name . ' (' . $row->product_code . ')';
                })
                ->addColumn('current_stock', function ($row) {
                    return $row->quantity;
                })
                ->addColumn('reorder_level', function ($row) {
                    return $row->stock_alert;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
    }
    /********************************************************************/
    public function expiredProductsList(Request $request)
    {
        if ($request->ajax()) {
            // Get today's date at start of day for accurate comparison
            $today = now()->startOfDay();


            $data = Product::query()
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<', $today)
                ->orderBy('expiry_date', 'asc')
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('product', function ($row) {
                    return $row->name . ' (' . $row->product_code . ')';
                })
                ->addColumn('expiry_date', function ($row) {
                    return '<span class="badge bg-danger">' . date('d-m-Y', strtotime($row->expiry_date)) . '</span>';
                })
                ->addColumn('quantity', function ($row) {
                    return $row->quantity;
                })

                ->rawColumns(['product', 'expiry_date'])
                ->make(true);
        }
    }
    /*==============================================================*/
    public function addProduct(Request $request)
    {
        $this->authenticateRole($module_page = 'product');

        $units = Unit::get();
        $title = "Add Product";
        return view('products.add_prodcut', compact('units', 'title'));
    }
    public function storeProduct(ProductRequest $request)
    {
        $validatedData = $request->validated();
        // echo "<pre>"; print_r($validatedData); "</pre>"; exit;
        DB::beginTransaction();

        try {
            // Find the default unit to get its conversion factor
            $defaultUnitIndex = array_search($validatedData['default_unit'], $validatedData['unit_id']);
            $defaultUnitConversion = $validatedData['conversion'][$defaultUnitIndex];
            // echo "<pre>"; print_r($validatedData['purchase_price'][$defaultUnitIndex]); exit;
            // Prepare units data for pivot table
            $unitsData = [];
            foreach ($validatedData['unit_id'] as $index => $unitId) {
                // echo "<pre>"; print_r($index); exit;
                $unitsData[] = [
                    'conversion' => $validatedData['conversion'][$index],
                    'unit_id' => $unitId,
                    'unit' => Unit::find($unitId)->name,
                    'purchase_price' => $validatedData['purchase_price'][$index],
                    'selling_price' => $validatedData['selling_price'][$index],
                    'wholesale_price' => $validatedData['wholesale_price'][$index],
                    'is_default' => ($index == $validatedData['default_unit'])
                ];
            }
            // Create the product
            Product::create([
                'name' => $validatedData['name'],
                'name_ur' => $validatedData['name_ur'],
                'is_scheme_product' => isset($validatedData['is_scheme_product']) ? true : false,
                'stock_alert' => $validatedData['stock_alert'],
                'expiry_date' => date('Y-m-d', strtotime($validatedData['expiry_date'])),
                'product_code' => $validatedData['product_code'],
                'supplier_id' => is_array($validatedData['supplier_id'])
                    ? implode(',', $validatedData['supplier_id'])
                    : $validatedData['supplier_id'],
                'quantity' => $validatedData['quantity'],
                'unit_id' => $validatedData['default_unit'],
                'qtyPerUnit' => $defaultUnitConversion,
                'unit_info' => $unitsData
            ]);



            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                // 'product' => $product->load('units')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating product',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function storeProductxxxx(ProductRequest $request)
    {
        // $this->authenticateRole($module_page = 'product');
        $data = $request->all();
        // echo "<pre>"; print_r($data); exit;
        try {
            if (is_numeric($data['unit_id']) && ($data['unit_id'] > 0)) {
                $product = new Product;
                $product->name = $data['name'];
                $product->unit_id = $data['unit_id'];
                $product->qtyPerUnit = isset($data['qtyPerUnit']) ? $data['qtyPerUnit'] : 0;
                $product->cost = isset($data['cost']) ? $data['cost'] : 0;
                $product->item_cost = isset($data['item_cost']) ? $data['item_cost'] : 0;
                $product->selling_price = isset($data['selling_price']) ? $data['selling_price'] : 0;
                $product->item_selling_price = isset($data['item_selling_price']) ? $data['item_selling_price'] : 0;
                $product->quantity = isset($data['quantity']) ? $data['quantity'] : 0;
                $product->product_code = $data['product_code'];
                $product->createdBy = Auth::user()->id;
                $product->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Product successfully added!',
                    'product' => $product
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit cannot be nulled!'
                ]);
                // return redirect()->back()->with('error', 'Unit cannot be nulled!');
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while saving product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function  openProductModal(Request $request, $id = null)
    {
        if ($id) {
            $editProduct = Product::find($id);
        } else {
            $editProduct = [];
        }

        return response()->json([
            'success' => true,
            'message' => 'Product successfully added!',
            'product' => $editProduct
        ]);
    }
    /*==============================================================*/
    public function editProductxxx(Request $request, $id = null)
    {
        // $this->authenticateRole($module_page = 'product');
        $editProduct = Product::find($id);

        $units = Unit::get();
        $title = "Update Product";
        return response()->json([
            'success' => true,
            'message' => 'Product successfully added!',
            'product' => $editProduct,
            'units' => $units
        ]);
        // return view('products.add_prodcut', compact('units', 'editProduct', 'title'));
    }
    /*==============================================================*/
    public function editProduct($id)
    {
        $product = Product::findOrFail($id);
        $units = Unit::all();

        // Parse the unit_info if it's stored as JSON
        $unitInfo = is_array($product->unit_info) ? $product->unit_info : json_decode($product->unit_info, true);

        return response()->json([
            'success' => true,
            'product' => $product,
            'units' => $units,
            'unitInfo' => $unitInfo
        ]);
    }
    public function updateProduct(ProductRequest $request, $id)
    {
        $validatedData = $request->validated();

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($id);

            // Find the default unit to get its conversion factor
            $defaultUnitIndex = array_search($validatedData['default_unit'], $validatedData['unit_id']);
            $defaultUnitConversion = $validatedData['conversion'][$defaultUnitIndex];

            // Prepare units data for pivot table
            $unitsData = [];
            foreach ($validatedData['unit_id'] as $index => $unitId) {
                $unitsData[] = [
                    'conversion' => $validatedData['conversion'][$index],
                    'unit_id' => $unitId,
                    'unit' => Unit::find($unitId)->name,
                    'purchase_price' => $validatedData['purchase_price'][$index],
                    'selling_price' => $validatedData['selling_price'][$index],
                    'wholesale_price' => $validatedData['wholesale_price'][$index],
                    'is_default' => ($index == $validatedData['default_unit'])
                ];
            }

            // Update the product
            $product->update([
                'name' => $validatedData['name'],
                'name_ur' => $validatedData['name_ur'],
                'is_scheme_product' => isset($validatedData['is_scheme_product']) ? true : false,
                'stock_alert' => $validatedData['stock_alert'],
                'expiry_date' => date('Y-m-d', strtotime($validatedData['expiry_date'])),
                'product_code' => $validatedData['product_code'],
                'supplier_id' => is_array($validatedData['supplier_id'])
                    ? implode(',', $validatedData['supplier_id'])
                    : $validatedData['supplier_id'],
                'quantity' => $validatedData['quantity'],
                'unit_id' => $validatedData['default_unit'],
                'qtyPerUnit' => $defaultUnitConversion,
                'cost' => 0,
                'selling_price' => 0,
                'item_cost' => 0 * $defaultUnitConversion,
                'item_selling_price' => 0 * $defaultUnitConversion,
                'unit_info' => $unitsData,
                'updatedBy' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating product',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateProductxxx(ProductRequest $request, $id = null)
    {
        try {
            $this->authenticateRole($module_page = 'product');
            $editProduct = Product::find($id);
            $data = $request->all();
            $editProduct->name = $data['name'];
            $editProduct->unit_id = $data['unit_id'];
            $editProduct->qtyPerUnit = isset($data['qtyPerUnit']) ? $data['qtyPerUnit'] : 0;
            $editProduct->cost = isset($data['cost']) ? $data['cost'] : 0;
            $editProduct->item_cost = isset($data['item_cost']) ? $data['item_cost'] : 0;
            $editProduct->selling_price = isset($data['selling_price']) ? $data['selling_price'] : 0;
            $editProduct->item_selling_price = isset($data['item_selling_price']) ? $data['item_selling_price'] : 0;
            $editProduct->quantity = $data['quantity'];
            $editProduct->createdBy = Auth::user()->id;
            $editProduct->product_code = $data['product_code'];
            $editProduct->save();
            return redirect('/products')->with('success', 'Product successfully Updated!');
        } catch (Exception $e) {
            Session::flash('flash_message_error', "Oops, Something went wrong. Try again");
            return redirect()->back()->with($e->getMessage());
        }
    }

    /*==============================================================*/
    public function deleteProduct(Request $request, $id = null)
    {
        if ($request->ajax()) {
            try {
                $product = Product::findOrFail($id);

                // Call the delete method to trigger the `deleting` event
                $product->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Product Successfully Deleted!',
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the product.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }
    }
    /*==============================================================*/
    public function searchProducts(Request $request)
    {
        // echo "<pre>"; print_r($request->all()); exit;
        if (!$request->ajax()) {
            return response()->json(['message' => 'Invalid request'], 400);
        }

        $searchTerm = $request->term;
        $customerType = $request->customerType;

        $products = Product::query()
            ->select([
                'id',
                'name',
                'product_code',
                'quantity',
                'cost',
                'unit_info'
            ])
            ->where(function ($query) use ($searchTerm,$customerType) {
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('product_code', 'LIKE', "%{$searchTerm}%");
            })
            ->orderBy('name')
            ->limit(15)
            ->get()
            ->map(function ($product) use($customerType) {
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
                // echo "<pre>"; print_r($defaultUnit); "</pre>";
                $stock = $product->getDisplayStock();
                return [
                    "value"          => $product->name,
                    "productID"      => $product->id,
                    "productName"   => $product->product_code . ' - ' . $product->name,
                    "productQty"     => $product->quantity,
                    "stock"     => $stock,
                    "inputQty"     => 1,
                    "code"           => $product->product_code,
                    "cost"  => $defaultUnit['purchase_price'] ?? 0,
                    "productUnit"    => $defaultUnit['unit'] ?? null, // Use the unit title/name
                    "unit_id"       => $defaultUnit['unit_id'] ?? null,
                    "sellingPrice" => $customerType == 'retail' ? $defaultUnit['selling_price'] : $defaultUnit['wholesale_price'],
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
    public function searchProducts_xxx(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['message' => 'Invalid request method'], 400);
        }

        $searchTerm = $request->term;

        if (empty($searchTerm) || strlen($searchTerm) < 2) {
            return response()->json([
                'message' => 'Please enter at least 2 characters to search',
                'results' => []
            ], 200);
        }

        $products = Product::query()
            ->with(['units:id,name'])
            ->select([
                'id',
                'name',
                'product_code',
                'quantity',
                'qtyPerUnit',
                'item_cost',
                'cost',
                'item_selling_price',
                'selling_price',
                'unit_id'
            ])
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('product_code', 'LIKE', "%{$searchTerm}%");
            })
            ->orderBy('name')
            ->limit(15)
            ->get()
            ->map(function ($product) {
                $isBulk = $product->qtyPerUnit > 1;

                return [
                    "value"         => $product->name,
                    "productID"     => $product->id,
                    "productName"   => $product->product_code . ' - ' . $product->name,
                    "productQty"    => $product->quantity,
                    "code"          => $product->product_code,
                    "sellingPrice"  => $isBulk ? $product->item_selling_price : $product->selling_price,
                    "cost"          => $isBulk ? $product->item_cost : $product->cost,
                    "productUnit"   => $product->units->name ?? 'unit',
                    "qtyPerUnit"    => $product->qtyPerUnit
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
            'message' => 'Found ' . $products->count() . ' results',
            'results' => $products,
            'currency' => optional($settings)['currency']
        ]);
    }

    // public function searchProducts(Request $request)
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
    //                 $cost = $product->qtyPerUnit > 1 ? $product->item_cost : $product->cost;
    //                 $sellingPrice = $product->qtyPerUnit > 1 ? $product->item_selling_price : $product->selling_price;

    //                 return [
    //                     "value" => $product->name,
    //                     "productID" => $product->id,
    //                     "productName" => $product->name,
    //                     "productQty" => $product->quantity,
    //                     "code" => $product->product_code,
    //                     "sellingPrice" => $sellingPrice,
    //                     "cost" => $cost,
    //                     "productUnit" => optional($product->units)->name, // Use optional to avoid errors
    //                 ];
    //             });

    //             return response()->json($nakasiProduct);
    //         }

    //         return response()->json(['message' => 'No products found matching your search.'], 404);
    //     }
    // }

    /********************************************************************/
    public function searchProductUnit(Request $request)
    {
        if ($request->ajax()) {
            $nakasiProduct = [];
            $product = Product::find(1);

            if ($product) {
                // Access the main unit associated with this product
                $unit = $product->unit;

                // Access the sub unit associated with this product
                $subUnit = $product->subUnit;

                if ($unit) {
                    // Now you can access properties of both units
                    $unitName = $unit->name; // Example: accessing the 'name' attribute of the main unit
                    // $

                    // You can return or use these $unitName and $subUnitName as needed
                } else if ($subUnit) {
                    $subUnitName = $subUnit->name; // Example: accessing the 'name' attribute of the sub unit
                    // Handle if either unit or sub unit is not associated with the product
                }
            } else {
                // Handle if product with $productId is not found
            }
            if ($product) {
                foreach ($product as $key => $value) {
                    $nakasiProduct[] = array(
                        "value" => $value->name,
                        "productID" => $value->id,
                        "productName" => $value->name,
                        "productQty" => $value->quantity,
                        "sellingPrice" => $value->selling_price,
                        "cost" => $value->cost,
                        "productUnit" => $value['units']->name,
                    );
                }
                $dataRetrun = json_encode($nakasiProduct);
                return Response($dataRetrun);
            }
        }
    }
}
