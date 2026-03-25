<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        // Retorna todos os usuários onde o nome da role não seja o de usuário comum
        $staffs = User::whereHas('role', function ($query) {
            $query->where('name', '!=', 'user');
        })->with('role')->get();

        return response()->json($staffs, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'cpf' => 'required|string|unique:users,cpf',
            'password' => 'required|string|min:6',
            'id_role' => 'required|exists:roles,id',
        ]);

        $staff = User::create($validated);

        return response()->json($staff, 201);
    }

    public function show(User $staff)
    {
        return response()->json($staff->load('role'), 200);
    }

    public function update(Request $request, User $staff)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $staff->id,
            'cpf' => 'sometimes|required|string|unique:users,cpf,' . $staff->id,
            'id_role' => 'sometimes|required|exists:roles,id',
        ]);

        if ($request->has('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $staff->update($validated);

        return response()->json($staff, 200);
    }

    public function destroy(User $staff)
    {
        $staff->deleteAccount(); 

        return response()->json(['message' => 'Staff removido com sucesso.'], 200);
    }
}