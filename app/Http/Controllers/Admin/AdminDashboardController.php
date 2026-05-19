<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->select('id', 'name', 'username', 'email', 'role', 'created_at')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'users' => $users->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at?->toDateTimeString(),
            ])->values(),
            'stats' => [
                'total_users' => $users->count(),
                'total_managers' => $users->where('role', 'manager')->count(),
                'total_regular_users' => $users->where('role', 'user')->count(),
            ],
        ]);
    }
}
