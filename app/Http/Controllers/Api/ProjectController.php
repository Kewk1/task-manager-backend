<?php
/*
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project; // [DAGDAG] In-import ang Project Model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // [DAGDAG] In-import si Auth para makuha ang ID ng naka-login na user

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /*
    public function index()
    {
        // [DAGDAG] Kukunin lang ang mga projects na pagmamay-ari ng naka-login na user
        $projects = Project::where('user_id', Auth::id())->with('tasks')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Projects retrieved successfully',
            'data' => $projects
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    /*
    public function store(Request $request)
    {
        // [DAGDAG] Validation ng input data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // [DAGDAG] Awtomatikong isasama ang user_id ng kung sino ang kasalukuyang naka-login
        $project = Project::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Project created successfully',
            'data' => $project
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    /*
    public function show($id)
    {
        // [DAGDAG] Hahanapin ang project kung sa kanya nakapangalan, mag-404 kapag hindi sa kanya
        $project = Project::where('user_id', Auth::id())->with('tasks')->find($id);

        if (!$project) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project not found or unauthorized'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $project
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    /*
    public function update(Request $request, $id)
    {
        // [DAGDAG] Sisiguraduhing ang sariling project lang ang pwedeng i-update
        $project = Project::where('user_id', Auth::id())->find($id);

        if (!$project) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project not found or unauthorized'
            ], 404);
        }
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $project->update($validated);
        return response()->json([
            'status' => 'success',
            'message' => 'Project updated successfully',
            'data' => $project
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    /*
    public function destroy($id)
    {
        // [DAGDAG] Sisiguraduhing ang sariling project lang ang pwedeng burahin
        $project = Project::where('user_id', Auth::id())->find($id);

        if (!$project) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project not found or unauthorized'
            ], 404);
        }
        $project->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Project deleted successfully'
        ], 200);
    }
} 
*/

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // 🟢 Pag Admin o Manager: Makita ang LAHAT ng projects
        if ($user->role === 'admin' || $user->role === 'project_manager') {
            $projects = Project::with('tasks', 'creator')->get();
        } else {
            // 🟢 Pag Team Member / Developer: Makita ang projects kung saan may assigned task sila
            $projects = Project::whereHas('tasks', function ($query) use ($user) {
                $query->where('assigned_to', $user->id);
            })->with('tasks')->get();
        }

        return response([
            'status' => 'success',
            'message' => 'Projects retrieved successfully',
            'data' => $projects
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,in_progress,completed'
        ]);
        // Tiyaking may authenticated user
    $user = $request->user();
    if (!$user) {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }

        $project = Project::create([
            'user_id' => Auth::id(), // Ang gumawa / owner ng project
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'pending',
            'created_by' => auth()->id(),
        ]);

        return response([
            'status' => 'success',
            'message' => 'Project created successfully',
            'data' => $project
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $project = Project::with('tasks')->find($id);

        if (!$project) {
            return response([
                'status' => 'error',
                'message' => 'Project not found'
            ], 404);
        }

        return response([
            'status' => 'success',
            'data' => $project
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response([
                'status' => 'error',
                'message' => 'Project not found'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,in_progress,completed'
        ]);

        $project->update($validated);

        return response([
            'status' => 'success',
            'message' => 'Project updated successfully',
            'data' => $project
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response([
                'status' => 'error',
                'message' => 'Project not found'
            ], 404);
        }

        $project->delete();

        return response([
            'status' => 'success',
            'message' => 'Project deleted successfully'
        ], 200);
    }
}