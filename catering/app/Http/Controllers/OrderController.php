<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\Return_;

class OrderController extends Controller
{
    // Menampilkan semua pesanan beserta item terkait (jika ada relasi)
    public function index()
    {
        $orders = Order::with('orderItems')->get();
        return response()->json($orders);
    }

    // Menyimpan data pesanan baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'total_price' => 'required|numeric',
            'status'      => 'in:pending,paid,processing,completed,cancelled'
        ]);
    
        // Cek apakah terdapat order dengan status aktif (pending, paid, processing)
        $hasActiveOrder = Order::where('user_id', $validatedData['user_id'])
            ->whereIn('status', ['pending', 'paid', 'processing'])
            ->exists();
    
        if ($hasActiveOrder) {
            return response()->json([
                'message' => 'Cannot process order because there is still an active order'
            ], 401);
        }
    
        $order = Order::create($validatedData);
    
        // Opsional: jika terdapat data order_items dalam request
        if ($request->has('order_items')) {
            foreach ($request->order_items as $item) {
                $order->orderItems()->create([
                    'menu_id'  => $item['menu_id'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal']
                ]);
            }
        }
    
        return response()->json($order->load('orderItems'), 201);
    }
    
    // Menampilkan detail pesanan berdasarkan ID
    public function show($id)
    {
        $order = Order::with('orderItems')->findOrFail($id);
        return response()->json($order);
    }


    public function userOrder()
    {
        $userId = Auth::user()->id;

        $order = Order::with('orderItems')->where('user_id', $userId)->get();

        return response()->json($order);
    }

    // Memperbarui data pesanan
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validatedData = $request->validate([
            'user_id'     => 'sometimes|required|exists:users,id',
            'total_price' => 'sometimes|required|numeric',
            'status'      => 'in:pending,paid,processing,completed,cancelled'
        ]);

        $order->update($validatedData);
        return response()->json($order);
    }

    // Menghapus pesanan
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        return response()->json(null, 204);
    }
}
