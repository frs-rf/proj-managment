<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserRequest;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('member.manage');
        $roles = Role::all();
        return view('users.index', compact('roles'));
    }

    public function data(Request $request)
    {
        $this->authorize('member.manage');
        if ($request->ajax()) {
            $users = User::with('roles');
            return DataTables::eloquent($users)
                ->addColumn('roles', function ($user) {
                    return $user->roles->pluck('name')->map(function($role) {
                        return '<span class="badge bg-info text-dark">'.$role.'</span>';
                    })->implode(' ');
                })
                ->addColumn('action', function ($user) {
                    return '<button class="btn btn-sm btn-primary edit-user" data-id="'.$user->id.'">Edit</button>';
                })
                ->rawColumns(['roles', 'action'])
                ->make(true);
        }
    }

    public function store(UserRequest $request)
    {
        $this->authorize('member.manage');
        
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        
        $user = User::create($validated);
        
        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }
        
        return response()->json(['success' => true, 'message' => 'User created successfully', 'data' => $user]);
    }

    public function update(UserRequest $request, User $user)
    {
        $this->authorize('member.manage');
        
        $validated = $request->validated();
        
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }
        
        $user->update($validated);
        
        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        } else {
            $user->syncRoles([]);
        }
        
        return response()->json(['success' => true, 'message' => 'User updated successfully', 'data' => $user]);
    }

    public function show(User $user)
    {
        $this->authorize('member.manage');
        $user->load('roles');
        return response()->json(['success' => true, 'data' => $user]);
    }

    public function destroy(User $user)
    {
        $this->authorize('member.manage');
        $user->delete();
        return response()->json(['success' => true, 'message' => 'User deleted successfully']);
    }
}
