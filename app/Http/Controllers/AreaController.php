<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Session;
use App\Models\Area;
use App\Models\Customer;
use Exception;
use Yajra\DataTables\DataTables;

class AreaController extends Controller
{
    /*************************************************************/
    public function authenticateRole($module_page)
    {
        $permissionCheck =  checkRolePermission($module_page);
        if ($permissionCheck->access == 0) {
            return redirect()->to('/dashboard')->send()->with('error', 'You have no permission!');
        }
    }
    /*************************************************************/
    public function areas()
    {
        $this->authenticateRole($module_page = 'area');
        return view('area.view');
    }

    public function areasList(Request $request)
    {
        if ($request->ajax()) {
            $data = Area::with(['user:id,name'])
                ->select(
                    'areas.id',
                    'areas.name',
                    'areas.createdBy'
                )
                ->orderBy('areas.id', 'desc'); // Order by 'id' in descending order
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
                    return '<a href="javascript:void(0);" data-url="' . route('single.area', $row->id) . '" data-update-url="' . route('area.store', $row->id) . '" class="btn btn-info btn-sm edit-area" title="Edit?">
                          <i class="fas fa-pen"></i>
                      </a>
                      <a href="javascript:void(0);" class="btn btn-danger btn-sm delete" data-url="' . route('area.delete', $row->id) . '" data-id="' . $row->id . '" title="Delete">
                          <i class="fas fa-trash"></i>
                      </a>';
                })
                ->rawColumns(['action']) // Ensure raw HTML rendering for the 'action' column
                ->make(true);
        }
    }
    public function singleArea($id)
    {
        $area = Area::find($id);
        if (!$area) {
            return response()->json(['success' => false, 'message' => 'Area not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $area,
            'updateUrl' => route('area.store', $area->id),
        ]);
    }

    /*************************************************************/
    /* Store and Update Area */
    /*************************************************************/
    public function areasStore(Request $request, $id = null)
    {
        try {
            if ($id) {
                $area = Area::findOrNew($id);
            } else {
                $area = new Area;
            }
            $area->name = $request->name;
            $area->createdBy = auth()->user()->id;
            $area->save();

            return response()->json(['success' => true, 'message' => 'Area saved successfully!']);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Error saving area: ' . $th->getMessage()], 500);
        }
    }

    public function areaDelete(Request $request)
    {
        try {
            $area = Area::find($request->id);
            if ($area) {
                $area->delete();
                return response()->json(['success' => true, 'message' => 'Area deleted successfully!']);
            } else {
                return response()->json(['success' => false, 'message' => 'Area not found.'], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'error' => 'Error deleting area: ' . $th->getMessage()], 500);
        }
    }
}
