<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $currentUser = auth()->user();

        $users = User::query()
            ->with('business:id,name')
            ->select('id', 'name', 'username', 'email', 'phone', 'business_id', 'role', 'created_at')
            ->orderBy('name')
            ->get();

        $businesses = Business::query()
            ->with('defaultPublicTaskAssignee:id,name,username,business_id')
            ->select('id', 'name', 'default_public_task_assignee_id')
            ->orderBy('name')
            ->get();

        $publicTaskQuery = Task::query()
            ->with([
                'business:id,name',
                'assignee:id,name,email,username,phone,business_id',
            ])
            ->where('description', 'like', 'Public business form submission%')
            ->whereNull('archived_at')
            ->latest();

        if ($currentUser?->business_id !== null) {
            $publicTaskQuery->where('business_id', $currentUser->business_id);
        }

        $publicTasks = $publicTaskQuery->get();

        $taskAssignableUsers = User::query()
            ->select('id', 'name', 'username', 'email', 'phone', 'business_id')
            ->where('role', 'user')
            ->when($currentUser?->business_id !== null, fn ($query) => $query->where('business_id', $currentUser->business_id))
            ->orderBy('name')
            ->get()
            ->groupBy('business_id');

        return view('admin.dashboard', [
            'currentUser' => $currentUser,
            'users' => $users,
            'businesses' => $businesses,
            'publicTasks' => $publicTasks,
            'taskAssignableUsers' => $taskAssignableUsers,
            'stats' => [
                'total_users' => $users->count(),
                'total_managers' => $users->where('role', 'manager')->count(),
                'total_regular_users' => $users->where('role', 'user')->count(),
                'total_businesses' => $businesses->count(),
                'public_tasks' => $publicTasks->count(),
            ],
        ]);
    }
}
