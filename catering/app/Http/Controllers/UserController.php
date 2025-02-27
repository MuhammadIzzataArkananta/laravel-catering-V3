<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // Menampilkan semua pengguna
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    // Menyimpan data pengguna baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string',
            'role'     => 'in:customer,admin'
        ]);

        // Hash password sebelum disimpan
        $validatedData['password'] = bcrypt($validatedData['password']);

        $user = User::create($validatedData);
        return response()->json($user, 201);
    }

    // Menampilkan detail pengguna berdasarkan ID
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    // Memperbarui data pengguna
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validatedData = $request->validate([
            'name'     => 'sometimes|required|string|max:100',
            'email'    => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:6',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string',
            'role'     => 'in:customer,admin'
        ]);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        }

        $user->update($validatedData);
        return response()->json($user);
    }

    // Menghapus pengguna
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(null, 204);
    }
}
