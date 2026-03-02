<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * List all admin users.
     */
    public function index(Request $request): JsonResponse
    {
        $adminRole = Role::where('name', 'admin')->first();

        if (! $adminRole) {
            return response()->json([
                'message' => __('Admin role not configured.'),
            ], 500);
        }

        $admins = User::where('id_role', $adminRole->id)
            ->with('role')
            ->paginate($request->integer('per_page', 15));

        return response()->json($admins);
    }

    /**
     * Create a new admin user.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', 'unique:users,cpf'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('The given data was invalid.'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $role = Role::where('name', 'admin')->first();

        if (! $role) {
            return response()->json([
                'message' => __('Role not found.'),
            ], 500);
        }

        $user = User::create([
            'id_role' => $role->id,
            'name' => $request->name,
            'cpf' => $request->cpf,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->load('role');

        return response()->json([
            'message' => __('Admin created successfully.'),
            'user' => [
                'id' => $user->id,
                'id_role' => $user->id_role,
                'role' => $user->role->name,
                'name' => $user->name,
                'cpf' => $user->cpf,
                'email' => $user->email,
            ],
        ], 201);
    }

    /**
     * Promote a user to admin.
     */
    public function promote(User $user): JsonResponse
    {
        $role = Role::where('name', 'admin')->first();

        if (! $role) {
            return response()->json([
                'message' => __('Role not found.'),
            ], 500);
        }

        $user->id_role = $role->id;
        $user->save();
        $user->load('role');

        return response()->json([
            'message' => __('User promoted to admin successfully.'),
            'user' => [
                'id' => $user->id,
                'id_role' => $user->id_role,
                'role' => $user->role->name,
                'name' => $user->name,
                'cpf' => $user->cpf,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Remove admin role from a user (demote to normal user).
     */
    public function demote(User $user): JsonResponse
    {
        $role = Role::where('name', 'user')->first();

        if (! $role) {
            return response()->json([
                'message' => __('Role not found.'),
            ], 500);
        }

        $user->id_role = $role->id;
        $user->save();
        $user->load('role');

        return response()->json([
            'message' => __('User removed from admin successfully.'),
            'user' => [
                'id' => $user->id,
                'id_role' => $user->id_role,
                'role' => $user->role->name,
                'name' => $user->name,
                'cpf' => $user->cpf,
                'email' => $user->email,
            ],
        ]);
    }
}
