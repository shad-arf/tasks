<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskCommentRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use App\Models\TaskComment;
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

        $currentView = $this->resolveTaskView($request);
        $completedFilter = $this->resolveCompletedFilter($request);

        $baseQuery = fn () => Task::query()
            ->with([
                'assigner:id,name,email,username',
                'assignee:id,name,email,username',
                'comments.user:id,name,email,username',
            ])
            ->orderByRaw('case when due_date is null then 1 else 0 end')
            ->orderBy('due_date')
            ->latest('created_at');

        $assignedToMeQuery = $baseQuery()
            ->where('assigned_to', $user->id);

        $assignedByMeQuery = $baseQuery()
            ->where('assigned_by', $user->id);

        $this->applyTaskVisibilityFilter($assignedToMeQuery, $currentView, $completedFilter);
        $this->applyTaskVisibilityFilter($assignedByMeQuery, $currentView, $completedFilter);

        $assignedToMe = $assignedToMeQuery->get();
        $assignedByMe = $assignedByMeQuery->get();

        $users = User::query()
            ->select('id', 'name', 'email', 'username')
            ->where('role', 'user')
            ->whereKeyNot($user->id)
            ->orderBy('name')
            ->get();

        return view('tasks.index', [
            'currentUser' => $user,
            'users' => $users,
            'assignedToMe' => $assignedToMe,
            'assignedByMe' => $assignedByMe,
            'currentView' => $currentView,
            'completedFilter' => $completedFilter,
            'activeAssignedToMeCount' => Task::query()
                ->where('assigned_to', $user->id)
                ->whereNull('archived_at')
                ->where('is_completed', false)
                ->count(),
            'activeAssignedByMeCount' => Task::query()
                ->where('assigned_by', $user->id)
                ->whereNull('archived_at')
                ->where('is_completed', false)
                ->count(),
            'pendingReviewCount' => Task::query()
                ->where('assigned_by', $user->id)
                ->whereNull('archived_at')
                ->where('status', Task::STATUS_PENDING_REVIEW)
                ->count(),
            'urgentActiveCount' => Task::query()
                ->whereNull('archived_at')
                ->where('is_completed', false)
                ->where('priority', Task::PRIORITY_URGENT)
                ->where(function ($query) use ($user) {
                    $query
                        ->where('assigned_to', $user->id)
                        ->orWhere('assigned_by', $user->id);
                })
                ->count(),
            'archivedTaskCount' => Task::query()
                ->whereNotNull('archived_at')
                ->where(function ($query) use ($user) {
                    $query
                        ->where('assigned_to', $user->id)
                        ->orWhere('assigned_by', $user->id);
                })
                ->count(),
            'taskStatuses' => Task::statuses(),
            'taskPriorities' => Task::priorities(),
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);
        abort_if($user->isManager(), 403, 'Managers do not create tasks.');

        $data = $request->validated();

        validator(
            $data,
            [
                'assigned_to' => [
                    'required',
                    'integer',
                    Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'user')),
                    Rule::notIn([$user->id]),
                ],
            ]
        )->validate();

        Task::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'],
            'due_date' => $data['due_date'] ?? null,
            'status' => Task::STATUS_PENDING,
            'is_completed' => false,
            'assigned_by' => $user->id,
            'assigned_to' => (int) $data['assigned_to'],
        ]);

        return to_route('tasks.index')->with('success', 'تاسک بەسەرکەوتوویی زیادکرا.');
    }

    public function updateStatus(Task $task, UpdateTaskStatusRequest $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);
        abort_if($user->isManager(), 403, 'Managers do not update task status.');
        abort_if($user->id !== $task->assigned_to, 403, 'Only the assignee can change the task status.');

        $status = $request->validated()['status'];

        $task->update([
            'status' => $status,
            'is_completed' => $status === Task::STATUS_COMPLETED,
            'archived_at' => $status === Task::STATUS_COMPLETED ? $task->archived_at : null,
        ]);

        return to_route('tasks.index', $this->taskIndexParameters($request))->with('success', 'دۆخی تاسک نوێکرایەوە.');
    }

    public function storeComment(Task $task, StoreTaskCommentRequest $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);
        abort_if($user->isManager(), 403, 'Managers do not comment on task cards.');
        abort_if(
            ! in_array($user->id, [$task->assigned_by, $task->assigned_to], true),
            403,
            'Only the assignee or assigner can comment on this task.'
        );

        $data = $request->validated();
        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('task-attachments', 'public');
            $attachmentName = $request->file('attachment')->getClientOriginalName();
        }

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'comment' => $data['comment'] ?? null,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        return to_route('tasks.index', $this->taskIndexParameters($request))->with('success', 'لەسەر تاسکەکە تێبینی زیادکرا.');
    }

    public function toggle(Task $task, Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);
        abort_if($user->isManager(), 403, 'Managers do not update task status.');
        abort_if($user->id !== $task->assigned_to, 403, 'Only the assignee can change the task status.');

        $nextStatus = $task->is_completed ? Task::STATUS_PENDING : Task::STATUS_COMPLETED;

        $task->update([
            'status' => $nextStatus,
            'is_completed' => ! $task->is_completed,
            'archived_at' => $nextStatus === Task::STATUS_COMPLETED ? $task->archived_at : null,
        ]);

        return to_route('tasks.index', $this->taskIndexParameters($request))
            ->with('success', 'دۆخی تاسک نوێکرایەوە.');
    }

    public function archive(Task $task, Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);
        abort_if($user->isManager(), 403, 'Managers do not archive task cards.');
        abort_if(
            ! in_array($user->id, [$task->assigned_by, $task->assigned_to], true),
            403,
            'Only task participants can archive this task.'
        );
        abort_if(! $task->is_completed, 422, 'Only completed tasks can be archived.');

        $task->update([
            'archived_at' => now(),
        ]);

        return to_route('tasks.index', $this->taskIndexParameters($request, ['view' => 'active']))
            ->with('success', 'تاسک ئەرشیڤ کرا.');
    }

    private function applyTaskVisibilityFilter($query, string $currentView, string $completedFilter): void
    {
        if ($currentView === 'archived') {
            $query->whereNotNull('archived_at');

            return;
        }

        $query->whereNull('archived_at');

        if ($completedFilter === 'hide') {
            $query->where('is_completed', false);
        }
    }

    private function resolveTaskView(Request $request): string
    {
        return $request->string('view')->value() === 'archived' ? 'archived' : 'active';
    }

    private function resolveCompletedFilter(Request $request): string
    {
        return $request->string('completed')->value() === 'show' ? 'show' : 'hide';
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function taskIndexParameters(Request $request, array $overrides = []): array
    {
        $parameters = [
            'view' => $this->resolveTaskView($request),
            'completed' => $this->resolveCompletedFilter($request),
        ];

        $parameters = array_merge($parameters, $overrides);

        if (($parameters['view'] ?? 'active') === 'archived') {
            return ['view' => 'archived'];
        }

        unset($parameters['view']);

        if (($parameters['completed'] ?? 'hide') === 'hide') {
            unset($parameters['completed']);
        }

        return $parameters;
    }
}
