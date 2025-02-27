<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Menentukan apakah pengguna dapat melihat daftar pesanan.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin'; // Hanya admin yang bisa melihat semua order
    }

    /**
     * Menentukan apakah pengguna dapat melihat pesanan tertentu.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id || $user->role === 'admin';
    }

    /**
     * Menentukan apakah pengguna dapat membuat pesanan.
     */
    public function create(User $user): bool
    {
        return $user->role === 'customer'; // Hanya customer yang bisa membuat order
    }

    /**
     * Menentukan apakah pengguna dapat memperbarui pesanan.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->id === $order->user_id && $order->status !== 'completed';
    }

    /**
     * Menentukan apakah pengguna dapat menghapus pesanan.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->id === $order->user_id && $order->status === 'pending';
    }

    /**
     * Menentukan apakah pengguna dapat mengembalikan pesanan yang dihapus.
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Menentukan apakah pengguna dapat menghapus permanen pesanan.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->role === 'admin';
    }
}
