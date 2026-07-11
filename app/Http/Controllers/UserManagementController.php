<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\UserManagementRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->withCount(['sales', 'stockMovements'])
            ->latest()
            ->get();

        return view('users.index', [
            'users' => $users,
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(UserManagementRequest $request): RedirectResponse
    {
        $data = $request->validated();

        User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => UserRole::from($data['role']),
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(),
        ]);

        return back()->with('status', 'User berhasil ditambahkan.');
    }

    public function update(UserManagementRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => UserRole::from($data['role']),
            'is_active' => $request->boolean('is_active'),
        ];

        if (($data['password'] ?? '') !== '') {
            $payload['password'] = Hash::make($data['password']);
        }

        if ($request->user()->is($user) && ! $payload['is_active']) {
            return back()->with('error', 'Akun yang sedang dipakai tidak bisa dinonaktifkan.');
        }

        $user->update($payload);

        return back()->with('status', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (request()->user()->is($user)) {
            return back()->with('error', 'Akun yang sedang dipakai tidak bisa dihapus.');
        }

        if ($user->sales()->exists() || $user->stockMovements()->exists()) {
            return back()->with('error', 'User sudah punya histori transaksi/mutasi. Nonaktifkan saja agar histori tetap aman.');
        }

        $user->delete();

        return back()->with('status', 'User berhasil dihapus.');
    }
}
