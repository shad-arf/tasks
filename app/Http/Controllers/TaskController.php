<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskCommentRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_if($user === null, 401);
        abort_if($user->isManager(), 403, 'Managers do not use the task workspace.');

        $currentView = $this->resolveTaskView($request);
        $completedFilter = $this->resolveCompletedFilter($request);
        $currentTab = $this->resolveTaskTab($request);
        $currentFocus = $this->resolveTaskFocus($request);

        $baseQuery = fn () => Task::query()
            ->with([
                'assigner:id,name,email,username,phone',
                'assignee:id,name,email,username,phone',
                'comments.user:id,name,email,username,phone',
            ])
            ->orderByRaw('case when due_date is null then 1 else 0 end')
            ->orderBy('due_date')
            ->latest('created_at');

        $assignedToMeQuery = $baseQuery()
            ->where('assigned_to', $user->id);

        $assignedByMeQuery = $baseQuery()
            ->where('assigned_by', $user->id);

        $this->applyTaskVisibilityFilter($assignedToMeQuery, $currentView, $completedFilter, $currentFocus);
        $this->applyTaskVisibilityFilter($assignedByMeQuery, $currentView, $completedFilter, $currentFocus);

        $assignedToMe = $assignedToMeQuery->get();
        $assignedByMe = $assignedByMeQuery->get();

        $users = User::query()
            ->select('id', 'name', 'email', 'username', 'phone')
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
            'currentTab' => $currentTab,
            'currentFocus' => $currentFocus,
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

    public function store(StoreTaskRequest $request, WhatsAppService $whatsApp): RedirectResponse
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

        $assignee = User::query()
            ->select('id', 'name', 'phone')
            ->findOrFail((int) $data['assigned_to']);

        $task = Task::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'],
            'due_date' => $data['due_date'] ?? null,
            'status' => Task::STATUS_PENDING,
            'is_completed' => false,
            'assigned_by' => $user->id,
            'assigned_to' => (int) $data['assigned_to'],
        ]);

        $warningMessage = $this->sendTaskAssignmentWhatsAppMessage($request, $whatsApp, $task, $assignee, $user);

        $response = to_route('tasks.index', $this->taskIndexParameters($request, ['view' => 'active']))
            ->with('success', 'تاسک بەسەرکەوتوویی زیادکرا.');

        if ($warningMessage !== null) {
            $response->with('warning', $warningMessage);
        }

        return $response;
    }

    public function show(Request $request, Task $task): View
    {
        $user = $request->user();

        abort_if($user === null, 401);
        abort_if($user->isManager(), 403, 'Managers do not use the task workspace.');
        abort_if(
            ! in_array($user->id, [$task->assigned_by, $task->assigned_to], true),
            403,
            'Only task participants can view this task.'
        );

        $task->load([
            'assigner:id,name,email,username,phone',
            'assignee:id,name,email,username,phone',
            'comments.user:id,name,email,username,phone',
        ]);

        return view('tasks.show', [
            'task' => $task,
            'currentView' => $this->resolveTaskView($request),
            'completedFilter' => $this->resolveCompletedFilter($request),
            'currentTab' => $this->resolveTaskTab($request),
            'currentFocus' => $this->resolveTaskFocus($request),
            'statusLabels' => [
                'pending' => 'چاوەڕوان',
                'in_progress' => 'لە کاردایە',
                'pending_review' => 'چاوەڕوانی پشکنین',
                'completed' => 'تەواوبووە',
            ],
            'statusClasses' => [
                'pending' => 'text-bg-warning',
                'in_progress' => 'text-bg-info',
                'pending_review' => 'text-bg-primary',
                'completed' => 'text-bg-success',
            ],
            'priorityLabels' => [
                'urgent' => 'Urgent',
                'high' => 'High',
                'low' => 'Low',
            ],
            'priorityClasses' => [
                'urgent' => 'text-bg-danger',
                'high' => 'text-bg-warning',
                'low' => 'text-bg-secondary',
            ],
        ]);
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

    private function applyTaskVisibilityFilter($query, string $currentView, string $completedFilter, string $currentFocus): void
    {
        if ($currentView === 'archived') {
            $query->whereNotNull('archived_at');

            return;
        }

        $query->whereNull('archived_at');

        if ($completedFilter === 'hide') {
            $query->where('is_completed', false);
        }

        if ($currentFocus === 'urgent') {
            $query->where('priority', Task::PRIORITY_URGENT);
        }

        if ($currentFocus === 'pending_review') {
            $query->where('status', Task::STATUS_PENDING_REVIEW);
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

    private function resolveTaskTab(Request $request): string
    {
        return $request->string('tab')->value() === 'delegated' ? 'delegated' : 'mine';
    }

    private function resolveTaskFocus(Request $request): string
    {
        return match ($request->string('focus')->value()) {
            'urgent' => 'urgent',
            'pending_review' => 'pending_review',
            default => 'all',
        };
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
            'tab' => $this->resolveTaskTab($request),
            'focus' => $this->resolveTaskFocus($request),
        ];

        $parameters = array_merge($parameters, $overrides);

        if (($parameters['view'] ?? 'active') === 'archived') {
            return ['view' => 'archived'];
        }

        unset($parameters['view']);

        if (($parameters['completed'] ?? 'hide') === 'hide') {
            unset($parameters['completed']);
        }

        if (($parameters['tab'] ?? 'mine') === 'mine') {
            unset($parameters['tab']);
        }

        if (($parameters['focus'] ?? 'all') === 'all') {
            unset($parameters['focus']);
        }

        return $parameters;
    }

    private function sendTaskAssignmentWhatsAppMessage(
        StoreTaskRequest $request,
        WhatsAppService $whatsApp,
        Task $task,
        User $assignee,
        User $assigner
    ): ?string {
        if (! $request->boolean('send_whatsapp')) {
            return null;
        }

        if (blank($assignee->phone)) {
            return 'تاسکەکە زیادکرا، بەڵام WhatsApp نەنێردرا چونکە ژمارەی مۆبایلی ئەو بەکارهێنەرە تۆمارنەکراوە. تکایە لە user profile ژمارەکە بە country code وەک 9647501234567 زیاد بکە، پاشان دووبارە هەوڵبدەرەوە.';
        }

        $message = trim((string) $request->validated('whatsapp_message'));

        if ($message === '') {
            $message = $this->buildDefaultWhatsAppMessage($task, $assigner);
        }

        try {
            $whatsApp->sendTextMessage(
                $assignee->phone,
                $message,
                'task-'.$task->id.'-assignment'
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->buildWhatsAppFailureMessage($exception);
        }

        return null;
    }

    private function buildWhatsAppFailureMessage(Throwable $exception): string
    {
        $details = trim($exception->getMessage());

        if ($exception instanceof RequestException && $exception->response !== null) {
            $apiDetail = $exception->response->json('message')
                ?? $exception->response->json('error')
                ?? $exception->response->body();

            if (is_string($apiDetail) && trim($apiDetail) !== '') {
                $details = trim($apiDetail);
            }
        }

        $message = 'تاسکەکە زیادکرا، بەڵام ناردنی نامەی WhatsApp سەرکەوتوو نەبوو. تکایە دڵنیابە لەوەی WHATSAPP_ACCOUNT ڕاستە، ژمارەی وەرگر بە country code تۆمارکراوە، و CLIENT_ID / CLIENT_SECRET دروستن.';

        if ($details === '') {
            return $message;
        }

        return $message.' هۆکاری هەڵە: '.$details;
    }

    private function buildDefaultWhatsAppMessage(Task $task, User $assigner): string
    {
        $lines = [
            'New task assigned to you.',
            'Title: '.$task->title,
            'Priority: '.strtoupper($task->priority),
            'Assigned by: '.$assigner->name,
        ];

        if ($task->due_date !== null) {
            $lines[] = 'Due date: '.$task->due_date->format('Y-m-d');
        }

        if ($task->description !== null && trim($task->description) !== '') {
            $lines[] = 'Details: '.$task->description;
        }

        return implode("\n", $lines);
    }
}
