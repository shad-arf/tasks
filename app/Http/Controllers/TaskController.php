<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_if($user === null, 401);
        abort_if($user->isManager(), 403, 'Managers do not use the task workspace.');

        $assignedToMe = Task::query()
            ->with(['assigner:id,name,email', 'assignee:id,name,email'])
            ->where('assigned_to', $user->id)
            ->latest()
            ->get();

        $assignedByMe = Task::query()
            ->with(['assigner:id,name,email', 'assignee:id,name,email'])
            ->where('assigned_by', $user->id)
            ->latest()
            ->get();

        $users = User::query()
            ->select('id', 'name', 'email')
            ->where('role', 'user')
            ->whereKeyNot($user->id)
            ->orderBy('name')
            ->get();

        return view('tasks.index', [
            'currentUser' => $user,
            'users' => $users,
            'assignedToMe' => $assignedToMe,
            'assignedByMe' => $assignedByMe,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);
        abort_if($user->isManager(), 403, 'Managers do not create tasks.');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'user')),
                Rule::notIn([$user->id]),
            ],
        ]);

        Task::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'assigned_by' => $user->id,
            'assigned_to' => (int) $data['assigned_to'],
        ]);

        return to_route('tasks.index')->with('success', 'تاسک بەسەرکەوتوویی زیادکرا.');
    }

    public function toggle(Task $task, Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);
        abort_if($user->isManager(), 403, 'Managers do not update task status.');
        abort_if($user->id !== $task->assigned_to, 403, 'Only the assignee can change the task status.');

        $task->update([
            'is_completed' => ! $task->is_completed,
        ]);

        return to_route('tasks.index')
            ->with('success', 'دۆخی تاسک نوێکرایەوە.');
    }
}
