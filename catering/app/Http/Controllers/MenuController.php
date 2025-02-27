<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;

class MenuController extends Controller
{
    // Menampilkan semua menu
    public function index()
    {
        $menus = Menu::all();
        return response()->json($menus);
    }

    // Menyimpan data menu baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'price'       => 'required|numeric',
            'image'       => 'nullable|string',
            'available'   => 'boolean'
        ]);

        $menu = Menu::create($validatedData);
        return response()->json($menu, 201);
    }

    // Menampilkan detail menu berdasarkan ID
    public function show($id)
    {
        $menu = Menu::findOrFail($id);
        return response()->json($menu);
    }

    // Memperbarui data menu
    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);
        $validatedData = $request->validate([
            'name'        => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'price'       => 'sometimes|required|numeric',
            'image'       => 'nullable|string',
            'available'   => 'boolean'
        ]);

        $menu->update($validatedData);
        return response()->json($menu);
    }

    // Menghapus menu
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();
        return response()->json(null, 204);
    }

    
}
