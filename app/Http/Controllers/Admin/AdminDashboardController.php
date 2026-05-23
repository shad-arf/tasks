<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('business:id,name')
            ->select('id', 'name', 'username', 'email', 'phone', 'business_id', 'role', 'created_at')
            ->orderBy('name')
            ->get();

        $businesses = Business::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('admin.dashboard', [
            'currentUser' => auth()->user(),
            'users' => $users,
            'businesses' => $businesses,
            'stats' => [
                'total_users' => $users->count(),
                'total_managers' => $users->where('role', 'manager')->count(),
                'total_regular_users' => $users->where('role', 'user')->count(),
                'total_businesses' => $businesses->count(),
            ],
        ]);
    }
}
