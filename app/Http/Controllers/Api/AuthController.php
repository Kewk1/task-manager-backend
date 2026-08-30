<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; // 🟢 [INAYOS]: In-import na ang Auth Facade para 'di na mag-500 Error!

class AuthController extends Controller
{
    // Solid Register Function
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:admin,project_manager,developer'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
            'role' => $fields['role'],
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    // Solid Login Function
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'status' => 'error',
                'message' => 'Maling kredensyal. Pakisuri ang email o password.'
            ], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        return response([
            'status' => 'success',
            'message' => 'Login successful!',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    // Logout Function
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response([
            'message' => 'Successfully logged out'
        ], 200);
    }

    // 🟢 [FUNCTION]: Para sa pagkuha ng lahat ng Developers
    public function index()
    {
        $user = Auth::user();

        if ($user->role !== 'admin' && $user->role !== 'project_manager') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Forbidden. Admins and Managers only.'
            ], 403);
        }

        $developers = User::where('role', 'developer')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $developers
        ], 200);
    }

    // 🟢 [FUNCTION]: Para sa pag-update ng Developer
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if ($user->role !== 'admin' && $user->role !== 'project_manager') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Forbidden. Admins and Managers only.'
            ], 403);
        }

        $developer = User::where('role', 'developer')->find($id);

        if (!$developer) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Developer not found.'
            ], 404);
        }

        $validated = $request->validate([
            'name'  => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'role'  => 'sometimes|required|in:admin,project_manager,developer'
        ]);

        $developer->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Developer updated successfully!',
            'data'    => $developer
        ], 200);
    }

    // 🟢 [FUNCTION]: Para sa pagbura ng Developer
    public function destroy($id)
    {
        $user = Auth::user();

        if ($user->role !== 'admin' && $user->role !== 'project_manager') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Forbidden. Admins and Managers only.'
            ], 403);
        }

        $developer = User::where('role', 'developer')->find($id);

        if (!$developer) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Developer not found.'
            ], 404);
        }

        $developer->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Developer deleted successfully!'
        ], 200);
    }
}