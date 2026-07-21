<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorize('member.manage');
        return view('roles.index');
    }

    public function data(Request $request)
    {
        $this->authorize('member.manage');
        if ($request->ajax()) {
            $roles = Role::query();
            return DataTables::eloquent($roles)
                ->addColumn('action', function ($role) {
                    $btn = '<button class="btn btn-sm btn-primary edit-role" data-id="'.$role->id.'">Edit</button> ';
                    if (!in_array($role->name, ['administrator', 'project_manager', 'member'])) {
                        $btn .= '<button class="btn btn-sm btn-danger delete-role" data-id="'.$role->id.'">Delete</button>';
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $this->authorize('member.manage');
        $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);
        
        $role = Role::create(['name' => $request->name]);
        
        return response()->json(['success' => true, 'message' => 'Role created successfully', 'data' => $role]);
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('member.manage');
        if (in_array($role->name, ['administrator', 'project_manager', 'member'])) {
            return response()->json(['success' => false, 'message' => 'Cannot modify core roles name'], 403);
        }
        
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);
        
        $role->update(['name' => $request->name]);
        
        return response()->json(['success' => true, 'message' => 'Role updated successfully', 'data' => $role]);
    }

    public function show(Role $role)
    {
        $this->authorize('member.manage');
        return response()->json(['success' => true, 'data' => $role]);
    }

    public function destroy(Role $role)
    {
        $this->authorize('member.manage');
        if (in_array($role->name, ['administrator', 'project_manager', 'member'])) {
            return response()->json(['success' => false, 'message' => 'Cannot delete core roles'], 403);
        }
        $role->delete();
        return response()->json(['success' => true, 'message' => 'Role deleted successfully']);
    }
}
