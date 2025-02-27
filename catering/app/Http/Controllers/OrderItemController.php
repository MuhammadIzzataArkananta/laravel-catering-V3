<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderItem;

class OrderItemController extends Controller
{
    // Menampilkan semua detail pesanan
    public function index()
    {
        $orderItems = OrderItem::all();
        return response()->json($orderItems);
    }

    // Menyimpan detail pesanan baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'menu_id'  => 'required|exists:menus,id',
            'quantity' => 'required|integer|min:1',
            'subtotal' => 'required|numeric'
        ]);

        $orderItem = OrderItem::create($validatedData);
        return response()->json($orderItem, 201);
    }

    // Menampilkan detail pesanan berdasarkan ID
    public function show($id)
    {
        $orderItem = OrderItem::findOrFail($id);
        return response()->json($orderItem);
    }

    // Memperbarui detail pesanan
    public function update(Request $request, $id)
    {
        $orderItem = OrderItem::findOrFail($id);
        $validatedData = $request->validate([
            'order_id' => 'sometimes|required|exists:orders,id',
            'menu_id'  => 'sometimes|required|exists:menus,id',
            'quantity' => 'sometimes|required|integer|min:1',
            'subtotal' => 'sometimes|required|numeric'
        ]);

        $orderItem->update($validatedData);
        return response()->json($orderItem);
    }

    // Menghapus detail pesanan
    public function destroy($id)
    {
        $orderItem = OrderItem::findOrFail($id);
        $orderItem->delete();
        return response()->json(null, 204);
    }
}
