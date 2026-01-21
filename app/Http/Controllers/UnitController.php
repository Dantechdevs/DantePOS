<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Unit;
use App\Models\Product;
use Exception;
use Yajra\DataTables\DataTables;

class UnitController extends Controller
{
    /*====================================*/
    public function authenticateRole($module_page)
    {
        $permissionCheck =  checkRolePermission($module_page);
        if ($permissionCheck->access == 0) {

            return redirect()->to('/dashboard')->send()->with('error', 'You have no permission!');
        }
    }
    public function units()
    {

        return view('units.view');
    }

    public function unitsList(Request $request)
    {
        if ($request->ajax()) {
            $data = Unit::with(['user:id,name'])
                ->select(
                    'units.id',
                    'units.name',
                    'units.createdBy'
                )
                ->orderBy('units.id', 'desc'); // Order by 'id' in descending order
            // ->get();
            // echo "<pre>"; print_r($data->toArray()); exit;
            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('createdBy', function ($row) {
                    return $row->user ? $row->user->name : '';
                })
                ->filterColumn('createdBy', function ($query, $keyword) {
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('action', function ($row) {
                    return '<a href="javascript:void(0);" data-url="' . route('single.unit', $row->id) . '" data-update-url="' . route('unit.store', $row->id) . '" class="btn btn-info btn-sm edit-unit" title="Edit?">
                          <i class="fas fa-pen"></i>
                      </a>
                      <a href="javascript:void(0);" class="btn btn-danger btn-sm delete" data-url="' . route('unit.delete', $row->id) . '" data-id="' . $row->id . '" title="Delete">
                          <i class="fas fa-trash"></i>
                      </a>';
                })
                ->rawColumns(['status', 'action']) // Ensure raw HTML rendering for the 'action' column
                ->make(true);
        }
    }
    public function singleUnit($id)
    {
        $unit = Unit::find($id);
        if (!$unit) {
            return response()->json(['success' => false, 'message' => 'Unit not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $unit,
            'updateUrl' => route('unit.store', $unit->id),
        ]);
    }

    /*************************************************************/
    /* Store and Update unit */
    /*************************************************************/
    public function unitStore(Request $request, $id = null)
    {
        try {
            if ($id) {
                $unit = Unit::findOrNew($id);
            } else {
                $unit = new Unit;
            }
            $unit->name = $request->name;
            $unit->createdBy = auth()->user()->id;
            $unit->save();

            return response()->json(['success' => true, 'message' => 'Unit saved successfully!']);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Error saving unit: ' . $th->getMessage()], 500);
        }
    }

    public function unitDelete(Request $request)
    {
        try {
            $unit = Unit::find($request->id);
            if ($unit) {
                $unit->delete();
                return response()->json(['success' => true, 'message' => 'Unit deleted successfully!']);
            } else {
                return response()->json(['success' => false, 'message' => 'Unit not found.'], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the sale.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
