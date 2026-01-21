<?php

namespace App\Http\Controllers;

use App\Godown;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class GodownController extends Controller
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
    public function godowns()
    {
        $this->authenticateRole($module_page = 'godowns');
        return view('godowns.index');
    }

    public function godownsList(Request $request)
    {
        if ($request->ajax()) {
            $data = Godown::with(['user:id,name'])
                ->select(
                    'godowns.id',
                    'godowns.name',
                    'godowns.status',
                    'godowns.createdBy'
                )
                ->orderBy('godowns.id', 'desc'); // Order by 'id' in descending order
            // ->get();
            // echo "<pre>"; print_r($data->toArray()); exit;
            return DataTables::of($data)
                ->addIndexColumn()


                ->filterColumn('status', function ($query, $keyword) {
                    if (strtolower($keyword) === 'active') {
                        $query->where('status', 1);
                    } elseif (strtolower($keyword) === 'inactive') {
                        $query->where('status', 0);
                    }
                })
                ->addColumn('status', function ($row) {
                    return strtoupper($row->status) === 'ACTIVE' ?
                        '<span class="badge badge-success">Active</span>' :
                        '<span class="badge badge-danger">Inactive</span>';
                })
                ->addColumn('createdBy', function ($row) {
                    return $row->user ? $row->user->name : '';
                })
                ->filterColumn('createdBy', function ($query, $keyword) {
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('action', function ($row) {
                    return '<a href="javascript:void(0);" data-url="' . route('single.godown', $row->id) . '" data-update-url="' . route('godown.store', $row->id) . '" class="btn btn-info btn-sm edit-godown" title="Edit?">
                          <i class="fas fa-pen"></i>
                      </a>
                      <a href="javascript:void(0);" class="btn btn-danger btn-sm delete" data-url="' . route('godown.delete', $row->id) . '" data-id="' . $row->id . '" title="Delete">
                          <i class="fas fa-trash"></i>
                      </a>';
                })
                ->rawColumns(['status', 'action']) // Ensure raw HTML rendering for the 'action' column
                ->make(true);
        }
    }
    public function singleGodown($id)
    {
        $godown = Godown::find($id);
        if (!$godown) {
            return response()->json(['success' => false, 'message' => 'Godown not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $godown,
            'updateUrl' => route('godown.store', $godown->id),
        ]);
    }

    /*************************************************************/
    /* Store and Update Godown */
    /*************************************************************/
    public function godownsStore(Request $request, $id = null)
    {
        try {
            if ($id) {
                $godown = Godown::findOrNew($id);
            } else {
                $godown = new Godown;
            }
            $godown->name = $request->name;
            $godown->status = $request->status;
            $godown->createdBy = auth()->user()->id;
            $godown->save();

            return response()->json(['success' => true, 'message' => 'Godown saved successfully!']);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Error saving godown: ' . $th->getMessage()], 500);
        }
    }

    public function godownDelete(Request $request)
    {
        try {
            $godown = Godown::find($request->id);
            if ($godown) {
                $godown->delete();
                return response()->json(['success' => true, 'message' => 'Godown deleted successfully!']);
            } else {
                return response()->json(['success' => false, 'message' => 'Godown not found.'], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function searchgodowns(Request $request)
    {
        $query = $request->input('query');
        $godowns = Godown::where('name', 'LIKE', "%{$query}%")
            ->orWhere('id', $query)
            ->get();

        return response()->json($godowns);
    }
}
