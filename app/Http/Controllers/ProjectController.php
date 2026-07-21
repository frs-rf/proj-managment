<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProjectController extends Controller
{
    public function index()
    {
        $this->authorize('project.viewAny', Project::class);
        $projectManagers = User::role('project_manager')->get();
        return view('projects.index', compact('projectManagers'));
    }

    public function data(Request $request)
    {
        $this->authorize('project.viewAny', Project::class);
        if ($request->ajax()) {
            $projects = Project::with('pm')->select('projects.*');
            return DataTables::eloquent($projects)
                ->addColumn('pm_name', function (Project $project) {
                    return $project->pm->name ?? '-';
                })
                ->addColumn('action', function ($project) {
                    return '<button class="btn btn-sm btn-primary edit-project" data-id="'.$project->id.'">Edit</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function store(StoreProjectRequest $request)
    {
        $this->authorize('project.create', Project::class);
        $project = Project::create($request->validated());
        return response()->json(['success' => true, 'message' => 'Project created successfully', 'data' => $project]);
    }

    public function show(Project $project)
    {
        $this->authorize('project.viewAny', Project::class);
        return response()->json(['success' => true, 'data' => $project]);
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('project.update', Project::class);
        $project->update($request->validated());
        return response()->json(['success' => true, 'message' => 'Project updated successfully', 'data' => $project]);
    }

    public function destroy(Project $project)
    {
        $this->authorize('project.update', Project::class); // usually PM or Admin can delete
        $project->delete();
        return response()->json(['success' => true, 'message' => 'Project deleted successfully']);
    }
}
