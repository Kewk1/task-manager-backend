<?php
/*
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task; // [DAGDAG] In-import ang Task Model
use App\Models\Project; // [DAGDAG] In-import ang Project Model para sa validation
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // [DAGDAG] In-import si Auth

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /*
    public function index(Request $request)
    {
        // [DAGDAG] Kukunin ang tasks na pagmamay-ari ng naka-login na user
        // Pwedeng mag-filter by project_id halimbawa: /api/tasks?project_id=1
        $query = Task::whereHas('project', function ($q) {
            $q->where('user_id', Auth::id());
        });

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $tasks = $query->with('project')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Tasks retrieved successfully',
            'data' => $tasks
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    /*
    public function store(Request $request)
    {
        // [DAGDAG] Validation para sa paggawa ng bagong task
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,in_progress,completed',
            'due_date' => 'nullable|date',
        ]);

        // [DAGDAG] Sisiguraduhing ang project na paglalagyan ng task ay pagmamay-ari ng naka-login na user
        $project = Project::where('id', $validated['project_id'])
            ->where('user_id', Auth::id())
            ->first();

        if (!$project) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized or project not found'
            ], 403);
        }

        // [DAGDAG] Pag-save ng task
        $task = Task::create([
            'project_id' => $validated['project_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'pending',
            'due_date' => $validated['due_date'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Task created successfully',
            'data' => $task
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    /*
    public function show($id)
    {
        // [DAGDAG] Kukunin lang ang task kung sa user ang project nito
        $task = Task::whereHas('project', function ($q) {
            $q->where('user_id', Auth::id());
        })->with('project')->find($id);

        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task not found or unauthorized'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $task
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    /*
    public function update(Request $request, $id)
    {
        // [DAGDAG] Sisiguraduhing pagmamay-ari ng user ang task bago i-update
        $task = Task::whereHas('project', function ($q) {
            $q->where('user_id', Auth::id());
        })->find($id);

        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task not found or unauthorized'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:pending,in_progress,completed',
            'due_date' => 'nullable|date',
        ]);

        $task->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Task updated successfully',
            'data' => $task
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    /*
    public function destroy($id)
    {
        // [DAGDAG] Sisiguraduhing sa user ang task bago burahin
        $task = Task::whereHas('project', function ($q) {
            $q->where('user_id', Auth::id());
        })->find($id);

        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task not found or unauthorized'
            ], 404);
        }

        $task->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Task deleted successfully'
        ], 200);
    }
}
    */


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the tasks.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin' || $user->role === 'project_manager') {
            // [BINAGO] Ginawang 'developer' sa halip na 'assignedUser' para tumugma sa Task.php model
            $tasks = Task::with('project', 'developer')->get();
        } else {
            $tasks = Task::where('assigned_to', $user->id)
                ->with('project')
                ->get();
        }

        return response([
            'status' => 'success',
            'data' => $tasks
        ], 200);
    }

    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            // [BINAGO] Ginawang 'in-progress' (dash) para tumugma sa enum sa create_tasks_table migration
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        $task = Task::create($validated);

        return response([
            'status' => 'success',
            'message' => 'Task assigned successfully!',
            'data' => $task
        ], 201);
    }

    /**
     * Update Task Status (Para sa Developer / Team Member progress update).
     */
    public function updateStatus(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response([
                'status' => 'error',
                'message' => 'Task not found.'
            ], 404);
        }

        // Tsek kung ang naka-login na developer ang totoong may-hawak ng task
        $user = Auth::user();
        if ($user->role === 'developer' && $task->assigned_to !== $user->id) {
            return response([
                'status' => 'error',
                'message' => 'Forbidden. You can only update tasks assigned to you.'
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        $task->update(['status' => $validated['status']]);

        return response([
            'status' => 'success',
            'message' => 'Task status updated successfully!',
            'data' => $task
        ], 200);
    }
}