@extends('layouts.app')

@section('title', 'تاسکەکان')
@section('html_lang', 'ckb')
@section('dir', 'rtl')
@section('body_class', 'bg-body-tertiary py-4')

@push('head')
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet"
        crossorigin="anonymous"
    >
    <style>
        .tasks-shell {
            max-width: 1320px;
        }

        .tasks-hero {
            background:
                radial-gradient(circle at top left, rgba(56, 189, 248, 0.18), transparent 28%),
                linear-gradient(135deg, #07111f 0%, #10233b 48%, #1d4ed8 100%);
            color: #f8fafc;
            border: 1px solid rgba(148, 163, 184, 0.18);
        }

        .tasks-hero .hero-muted {
            color: rgba(226, 232, 240, 0.82);
        }

        .stat-card {
            min-width: 220px;
            border: 1px solid #e2e8f0;
        }

        .picker-card,
        .task-card,
        .comment-box {
            border: 1px solid #e2e8f0;
        }

        .assignee-option {
            transition: background-color 0.18s ease, border-color 0.18s ease;
        }

        .assignee-option:hover,
        .assignee-option.is-selected {
            background: #eff6ff;
            border-color: #93c5fd;
        }

        .avatar-pill {
            width: 2.5rem;
            height: 2.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            font-weight: 700;
        }

        .task-card .task-meta {
            font-size: 0.84rem;
            color: #64748b;
        }

        .comment-item {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .file-input-note {
            font-size: 0.78rem;
            color: #64748b;
        }
    </style>
@endpush

@section('content')
    @php($currentUserInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim($currentUser->name), 0, 1)))
    @php($failedCommentTaskId = (string) old('comment_task_id'))
    @php($failedStatusTaskId = (string) old('status_task_id'))
    @php($selectedAssigneeId = (string) old('assigned_to', $users->first()?->id))
    @php($statusLabels = ['pending' => 'چاوەڕوان', 'in_progress' => 'لە کاردایە', 'pending_review' => 'چاوەڕوانی پشکنین', 'completed' => 'تەواوبووە'])
    @php($statusClasses = ['pending' => 'text-bg-warning', 'in_progress' => 'text-bg-info', 'pending_review' => 'text-bg-primary', 'completed' => 'text-bg-success'])
    @php($priorityLabels = ['urgent' => 'Urgent', 'high' => 'High', 'low' => 'Low'])
    @php($priorityClasses = ['urgent' => 'text-bg-danger', 'high' => 'text-bg-warning', 'low' => 'text-bg-secondary'])
    @php($isArchivedView = $currentView === 'archived')
    @php($showCompleted = $completedFilter === 'show')
    @php($activeViewRouteParameters = $showCompleted ? ['completed' => 'show'] : [])

    <div class="tasks-shell container-fluid px-3 px-lg-4">
        <div class="tasks-hero rounded-5 p-4 p-lg-5 shadow-lg mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-4">
                <div class="col-lg-8 px-0">
                    <span class="badge rounded-pill text-bg-light text-dark px-3 py-2 fw-semibold">Tasks Workspace</span>
                    <h1 class="display-6 fw-bold mt-3 mb-2">تاسکەکانم</h1>
                    <p class="mb-0 fs-6 hero-muted">
                        تاسکەکان بە پێی گرنگی، کاتی دواوە و دۆخی بەردەوامی کارەکە ڕێکخراون.
                        تێبینی و فایلەکانیش لە ژێر هەر تاسکێکدا دەمێنن بۆ ئەوەی هەموو context ـەکە لە یەک شوێن بێت.
                    </p>
                </div>

                <div class="d-flex flex-column gap-3">
                    <form method="POST" action="{{ route('logout') }}" class="text-lg-end">
                        @csrf
                        <button type="submit" class="btn btn-outline-light px-4">
                            چوونەدەرەوە
                        </button>
                    </form>

                    <div class="d-flex align-items-center gap-3 rounded-4 border border-light border-opacity-10 bg-white bg-opacity-10 px-3 py-3">
                        <div class="text-end">
                            <div class="fw-semibold">{{ $currentUser->name }}</div>
                            <div class="small hero-muted">{{ $currentUser->email ?: $currentUser->username }}</div>
                        </div>
                        <div class="avatar-pill bg-white bg-opacity-10 text-white">
                            {{ $currentUserInitial !== '' ? $currentUserInitial : '?' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-3 overflow-auto mb-4 pb-2">
            <div class="stat-card card rounded-4 shadow-sm flex-shrink-0">
                <div class="card-body">
                    <div class="text-uppercase text-secondary fw-semibold small">Active For Me</div>
                    <div class="display-6 fw-bold mt-2 mb-0 text-dark">{{ $activeAssignedToMeCount }}</div>
                </div>
            </div>
            <div class="stat-card card rounded-4 shadow-sm flex-shrink-0">
                <div class="card-body">
                    <div class="text-uppercase text-secondary fw-semibold small">Active By Me</div>
                    <div class="display-6 fw-bold mt-2 mb-0 text-dark">{{ $activeAssignedByMeCount }}</div>
                </div>
            </div>
            <div class="stat-card card rounded-4 shadow-sm flex-shrink-0">
                <div class="card-body">
                    <div class="text-uppercase text-secondary fw-semibold small">Pending Review</div>
                    <div class="display-6 fw-bold mt-2 mb-0 text-dark">{{ $pendingReviewCount }}</div>
                </div>
            </div>
            <div class="stat-card card rounded-4 shadow-sm flex-shrink-0">
                <div class="card-body">
                    <div class="text-uppercase text-secondary fw-semibold small">Urgent Tasks</div>
                    <div class="display-6 fw-bold mt-2 mb-0 text-dark">{{ $urgentActiveCount }}</div>
                </div>
            </div>
            <div class="stat-card card rounded-4 shadow-sm flex-shrink-0">
                <div class="card-body">
                    <div class="text-uppercase text-secondary fw-semibold small">Archived Tasks</div>
                    <div class="display-6 fw-bold mt-2 mb-0 text-dark">{{ $archivedTaskCount }}</div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success rounded-4 border-0 shadow-sm" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger rounded-4 border-0 shadow-sm" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="card border-0 rounded-5 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-lg-end">
                    <div>
                        <h2 class="h5 fw-bold text-dark mb-2">فلتەر و ئەرشیف</h2>
                        <p class="text-secondary mb-0">
                            @if ($isArchivedView)
                                تاسکە ئەرشیڤکراوەکان لێرە کۆکراونەتەوە. ئەگەر دۆخیان بگۆڕیتەوە بۆ ناتەواو، دەگەڕێنەوە بۆ لیستی چالاک.
                            @elseif ($showCompleted)
                                تاسکە تەواوبووەکان دیارن. لەم دۆخەدا دەتوانیت بیانئەرشیف بکەیت.
                            @else
                                بە شێوەی بنەڕەتی تاسکە تەواوبووەکان شاراوەنەتەوە بۆ ئەوەی تەنها کاری چالاک ببینیت.
                            @endif
                        </p>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-3 align-items-sm-end">
                        <div class="btn-group" role="group" aria-label="Task views">
                            <a
                                href="{{ route('tasks.index', $activeViewRouteParameters) }}"
                                class="btn {{ $isArchivedView ? 'btn-outline-dark' : 'btn-dark' }}"
                            >
                                چالاک
                            </a>
                            <a
                                href="{{ route('tasks.index', ['view' => 'archived']) }}"
                                class="btn {{ $isArchivedView ? 'btn-dark' : 'btn-outline-dark' }}"
                            >
                                ئەرشیف
                            </a>
                        </div>

                        @unless ($isArchivedView)
                            <form method="GET" action="{{ route('tasks.index') }}" class="d-flex gap-2 align-items-end">
                                <div>
                                    <label for="completed_filter" class="form-label small fw-semibold text-uppercase text-secondary">Completed</label>
                                    <select id="completed_filter" name="completed" class="form-select">
                                        <option value="hide" @selected(! $showCompleted)>Hide Done</option>
                                        <option value="show" @selected($showCompleted)>Show Done</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-dark px-4">Apply</button>
                            </form>
                        @endunless
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 rounded-5 shadow-sm mb-4">
            <div class="card-body p-4 p-lg-5">
                <div class="mb-4">
                    <h2 class="h3 fw-bold text-dark mb-2">زیادکردنی تاسکی نوێ</h2>
                    <p class="text-secondary mb-0">
                        تاسکەکە بە priority و due date و وەرگری ڕوون بسازە بۆ ئەوەی کارەکان باشتر delegate بکرێن.
                    </p>
                </div>

                <form method="POST" action="{{ route('tasks.store') }}">
                    @csrf

                    <div class="row g-4">
                        <div class="col-xl-7">
                            <div class="mb-4">
                                <label for="title" class="form-label fw-semibold">ناونیشانی تاسک</label>
                                <input
                                    id="title"
                                    name="title"
                                    type="text"
                                    value="{{ old('title') }}"
                                    class="form-control form-control-lg @error('title') is-invalid @enderror"
                                    placeholder="ناونیشانی تاسک..."
                                >
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="description" class="form-label fw-semibold">وردەکاری و context</label>
                                <textarea
                                    id="description"
                                    name="description"
                                    rows="6"
                                    class="form-control @error('description') is-invalid @enderror"
                                    placeholder="وردەکاری زیاتر بۆ تاسکەکە..."
                                >{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-xl-5">
                            <div class="picker-card rounded-5 bg-body-tertiary p-4 h-100">
                                <div class="row g-3">
                                    <div class="col-sm-6 col-xl-12">
                                        <label for="priority" class="form-label fw-semibold">Priority</label>
                                        <select id="priority" name="priority" class="form-select form-select-lg @error('priority') is-invalid @enderror">
                                            @foreach ($taskPriorities as $priority)
                                                <option value="{{ $priority }}" @selected(old('priority', 'high') === $priority)>
                                                    {{ $priorityLabels[$priority] ?? $priority }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('priority')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-sm-6 col-xl-12">
                                        <label for="due_date" class="form-label fw-semibold">Due Date</label>
                                        <input
                                            id="due_date"
                                            name="due_date"
                                            type="date"
                                            value="{{ old('due_date') }}"
                                            class="form-control form-control-lg @error('due_date') is-invalid @enderror"
                                        >
                                        @error('due_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                                            <label for="assignee_search" class="form-label fw-semibold mb-0">Assignee</label>
                                            <span class="small text-secondary">گەڕان + هەڵبژاردن</span>
                                        </div>

                                        <input type="hidden" name="assigned_to" id="assigned_to" value="{{ $selectedAssigneeId }}">

                                        <div class="rounded-4 bg-white border p-3 shadow-sm">
                                            <div id="selected_assignee_preview" class="rounded-4 bg-body-tertiary border p-3 small text-secondary mb-3">
                                                وەرگر هەڵبژێرە
                                            </div>

                                            <input
                                                id="assignee_search"
                                                type="text"
                                                class="form-control mb-3"
                                                placeholder="بە ناو یان username بگەڕێ..."
                                                @disabled($users->isEmpty())
                                            >

                                            <div id="assignee_picker_list" class="d-grid gap-2" style="max-height: 280px; overflow-y: auto;">
                                                @forelse ($users as $assignableUser)
                                                    @php($userInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim($assignableUser->name), 0, 1)))
                                                    <button
                                                        type="button"
                                                        class="assignee-option btn btn-light border text-start d-flex align-items-center justify-content-between gap-3"
                                                        data-user-id="{{ $assignableUser->id }}"
                                                        data-user-name="{{ $assignableUser->name }}"
                                                        data-user-username="{{ $assignableUser->username }}"
                                                        data-user-email="{{ $assignableUser->email }}"
                                                    >
                                                        <span class="d-flex align-items-center gap-3 min-w-0">
                                                            <span class="avatar-pill bg-primary-subtle text-primary-emphasis flex-shrink-0">
                                                                {{ $userInitial !== '' ? $userInitial : '?' }}
                                                            </span>
                                                            <span class="min-w-0">
                                                                <span class="d-block fw-semibold text-dark text-truncate">{{ $assignableUser->name }}</span>
                                                                <span class="d-block small text-secondary text-truncate">{{ $assignableUser->username }}</span>
                                                            </span>
                                                        </span>
                                                        <span class="small text-secondary text-truncate">{{ $assignableUser->email ?: '---' }}</span>
                                                    </button>
                                                @empty
                                                    <div class="alert alert-secondary mb-0">
                                                        user ی تر بەردەست نییە بۆ سپاردن.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>

                                        @error('assigned_to')
                                            <div class="text-danger small mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary btn-lg w-100" @disabled($users->isEmpty())>
                                            زیادکردنی تاسک
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-6">
                <div class="card border-0 rounded-5 shadow-sm h-100">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <span class="rounded-circle bg-danger" style="width: 10px; height: 10px;"></span>
                            <h2 class="h4 fw-bold mb-0 text-dark">{{ $isArchivedView ? 'ئەرشیفی تاسکە سپێردراوەکان بۆ من' : 'تاسکە سپێردراوەکان بۆ من' }}</h2>
                        </div>

                        <div class="d-grid gap-4">
                            @forelse ($assignedToMe as $task)
                                @php($isOverdue = $task->due_date && $task->due_date->isPast() && $task->status !== 'completed')
                                <article class="task-card rounded-5 bg-body-tertiary p-4">
                                    <div class="d-flex flex-column gap-4">
                                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                                            <div>
                                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                    <h3 class="h5 fw-bold mb-0 text-dark">{{ $task->title }}</h3>
                                                    <span class="badge rounded-pill {{ $priorityClasses[$task->priority] ?? 'text-bg-secondary' }}">
                                                        {{ $priorityLabels[$task->priority] ?? $task->priority }}
                                                    </span>
                                                    <span class="badge rounded-pill {{ $statusClasses[$task->status] ?? 'text-bg-secondary' }}">
                                                        {{ $statusLabels[$task->status] ?? $task->status }}
                                                    </span>
                                                </div>

                                                @if ($task->description)
                                                    <p class="mb-3 text-secondary">{{ $task->description }}</p>
                                                @endif

                                                <div class="d-flex flex-wrap gap-3 task-meta">
                                                    <span>سپێردراوە لەلایەن: {{ $task->assigner?->name ?? 'نادیار' }}</span>
                                                    <span>کاتی دواوە: {{ $task->due_date?->format('Y-m-d') ?? 'دیاری نەکراوە' }}</span>
                                                    @if ($task->archived_at)
                                                        <span>ئەرشیف: {{ $task->archived_at->format('Y-m-d H:i') }}</span>
                                                    @endif
                                                    @if ($isOverdue)
                                                        <span class="fw-semibold text-danger">ئەو تاسکە دوا کەوتووە</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="rounded-4 border bg-white p-3" style="min-width: 280px;">
                                                <form method="POST" action="{{ route('tasks.status.update', $task) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status_task_id" value="{{ $task->id }}">
                                                    <input type="hidden" name="view" value="{{ $currentView }}">
                                                    <input type="hidden" name="completed" value="{{ $completedFilter }}">
                                                    <label for="status-{{ $task->id }}" class="form-label small fw-semibold text-uppercase text-secondary">Status</label>
                                                    <div class="d-flex gap-2">
                                                        <select
                                                            id="status-{{ $task->id }}"
                                                            name="status"
                                                            class="form-select @if ($failedStatusTaskId === (string) $task->id && $errors->has('status')) is-invalid @endif"
                                                        >
                                                            @foreach ($taskStatuses as $status)
                                                                <option value="{{ $status }}" @selected($task->status === $status)>
                                                                    {{ $statusLabels[$status] ?? $status }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="btn btn-dark px-3">Save</button>
                                                    </div>
                                                    @if ($failedStatusTaskId === (string) $task->id && $errors->has('status'))
                                                        <div class="invalid-feedback d-block">{{ $errors->first('status') }}</div>
                                                    @endif
                                                </form>

                                                @if (! $isArchivedView && $task->is_completed)
                                                    <form method="POST" action="{{ route('tasks.archive', $task) }}" class="mt-3">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="view" value="{{ $currentView }}">
                                                        <input type="hidden" name="completed" value="{{ $completedFilter }}">
                                                        <button type="submit" class="btn btn-outline-secondary w-100">ئەرشیفکردنی تاسک</button>
                                                    </form>
                                                @elseif ($task->archived_at)
                                                    <div class="small text-secondary mt-3">ئەرشیف کراوە لە {{ $task->archived_at->format('Y-m-d H:i') }}</div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="comment-box rounded-5 bg-white p-4">
                                            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                                <h4 class="h6 fw-bold mb-0 text-dark">تێبینی و هاوپێچەکان</h4>
                                                <span class="small text-secondary">{{ $task->comments->count() }} item</span>
                                            </div>

                                            <div class="d-grid gap-3">
                                                @forelse ($task->comments as $comment)
                                                    @php($commentInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim($comment->user?->name ?? '?'), 0, 1)))
                                                    <div class="comment-item rounded-4 p-3">
                                                        <div class="d-flex align-items-start gap-3">
                                                            <div class="avatar-pill bg-secondary-subtle text-secondary-emphasis flex-shrink-0">
                                                                {{ $commentInitial !== '' ? $commentInitial : '?' }}
                                                            </div>
                                                            <div class="flex-grow-1 min-w-0">
                                                                <div class="d-flex flex-wrap align-items-center gap-2 small text-secondary">
                                                                    <span class="fw-semibold text-dark">{{ $comment->user?->name ?? 'نادیار' }}</span>
                                                                    <span>{{ $comment->created_at?->diffForHumans() }}</span>
                                                                </div>
                                                                @if ($comment->comment)
                                                                    <p class="mt-2 mb-0 text-secondary">{{ $comment->comment }}</p>
                                                                @endif
                                                                @if ($comment->attachment_path)
                                                                    <a
                                                                        href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($comment->attachment_path) }}"
                                                                        target="_blank"
                                                                        class="btn btn-sm btn-outline-primary mt-3"
                                                                    >
                                                                        {{ $comment->attachment_name ?: 'Attachment' }}
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="alert alert-light border mb-0">
                                                        هێشتا هیچ تێبینی یان فایلێک بۆ ئەم تاسکە زیاد نەکراوە.
                                                    </div>
                                                @endforelse
                                            </div>

                                            <form
                                                class="mt-4 rounded-4 border bg-body-tertiary p-3"
                                                method="POST"
                                                action="{{ route('tasks.comments.store', $task) }}"
                                                enctype="multipart/form-data"
                                            >
                                                @csrf
                                                <input type="hidden" name="comment_task_id" value="{{ $task->id }}">
                                                <input type="hidden" name="view" value="{{ $currentView }}">
                                                <input type="hidden" name="completed" value="{{ $completedFilter }}">
                                                <div class="mb-3">
                                                    <textarea
                                                        name="comment"
                                                        rows="3"
                                                        class="form-control @if ($failedCommentTaskId === (string) $task->id && ($errors->has('comment') || $errors->has('attachment'))) is-invalid @endif"
                                                        placeholder="تێبینی یان ڕوونکردنەوە زیاد بکە..."
                                                    >{{ $failedCommentTaskId === (string) $task->id ? old('comment') : '' }}</textarea>
                                                    @if ($failedCommentTaskId === (string) $task->id && ($errors->has('comment') || $errors->has('attachment')))
                                                        <div class="invalid-feedback d-block">
                                                            {{ $errors->first('comment') ?: $errors->first('attachment') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center">
                                                    <div class="w-100">
                                                        <input name="attachment" type="file" class="form-control">
                                                        <div class="file-input-note mt-2">PDF, images, Office files, or text up to 10MB.</div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary flex-shrink-0 px-4">زیادکردنی تێبینی</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="alert alert-light border rounded-4 mb-0">
                                    {{ $isArchivedView ? 'هیچ تاسکێکی ئەرشیڤکراوت نییە.' : 'هیچ تاسکێکت نییە لە ئێستادا.' }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card border-0 rounded-5 shadow-sm h-100">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <span class="rounded-circle bg-success" style="width: 10px; height: 10px;"></span>
                            <h2 class="h4 fw-bold mb-0 text-dark">{{ $isArchivedView ? 'ئەرشیفی ئەو تاسکانەی من دامنە' : 'ئەو تاسکانەی من دامنە' }}</h2>
                        </div>

                        <div class="d-grid gap-4">
                            @forelse ($assignedByMe as $task)
                                @php($isOverdue = $task->due_date && $task->due_date->isPast() && $task->status !== 'completed')
                                <article class="task-card rounded-5 bg-body-tertiary p-4">
                                    <div class="d-flex flex-column gap-4">
                                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                                            <div>
                                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                    <h3 class="h5 fw-bold mb-0 text-dark">{{ $task->title }}</h3>
                                                    <span class="badge rounded-pill {{ $priorityClasses[$task->priority] ?? 'text-bg-secondary' }}">
                                                        {{ $priorityLabels[$task->priority] ?? $task->priority }}
                                                    </span>
                                                    <span class="badge rounded-pill {{ $statusClasses[$task->status] ?? 'text-bg-secondary' }}">
                                                        {{ $statusLabels[$task->status] ?? $task->status }}
                                                    </span>
                                                </div>

                                                @if ($task->description)
                                                    <p class="mb-3 text-secondary">{{ $task->description }}</p>
                                                @endif

                                                 <div class="d-flex flex-wrap gap-3 task-meta">
                                                     <span>سپێردراوە بۆ: {{ $task->assignee?->name ?? 'نادیار' }}</span>
                                                     <span>کاتی دواوە: {{ $task->due_date?->format('Y-m-d') ?? 'دیاری نەکراوە' }}</span>
                                                     @if ($task->archived_at)
                                                         <span>ئەرشیف: {{ $task->archived_at->format('Y-m-d H:i') }}</span>
                                                     @endif
                                                     @if ($isOverdue)
                                                         <span class="fw-semibold text-danger">ئەو تاسکە دوا کەوتووە</span>
                                                     @endif
                                                </div>
                                            </div>

                                             <div class="rounded-4 border bg-white p-3" style="min-width: 240px;">
                                                 <div class="small text-uppercase fw-semibold text-secondary">Momentum</div>
                                                 <div class="mt-2 fw-medium text-dark">
                                                     {{ $task->status === 'completed' ? 'کارەکە تەواوبووە' : ($task->status === 'pending_review' ? 'لە چاوەڕوانی پشکنینە' : 'کارەکە بەردەوامە') }}
                                                 </div>
                                                 @if (! $isArchivedView && $task->is_completed)
                                                     <form method="POST" action="{{ route('tasks.archive', $task) }}" class="mt-3">
                                                         @csrf
                                                         @method('PATCH')
                                                         <input type="hidden" name="view" value="{{ $currentView }}">
                                                         <input type="hidden" name="completed" value="{{ $completedFilter }}">
                                                         <button type="submit" class="btn btn-outline-secondary w-100">ئەرشیفکردنی تاسک</button>
                                                     </form>
                                                 @endif
                                             </div>
                                         </div>

                                        <div class="comment-box rounded-5 bg-white p-4">
                                            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                                <h4 class="h6 fw-bold mb-0 text-dark">تێبینی و هاوپێچەکان</h4>
                                                <span class="small text-secondary">{{ $task->comments->count() }} item</span>
                                            </div>

                                            <div class="d-grid gap-3">
                                                @forelse ($task->comments as $comment)
                                                    @php($commentInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim($comment->user?->name ?? '?'), 0, 1)))
                                                    <div class="comment-item rounded-4 p-3">
                                                        <div class="d-flex align-items-start gap-3">
                                                            <div class="avatar-pill bg-secondary-subtle text-secondary-emphasis flex-shrink-0">
                                                                {{ $commentInitial !== '' ? $commentInitial : '?' }}
                                                            </div>
                                                            <div class="flex-grow-1 min-w-0">
                                                                <div class="d-flex flex-wrap align-items-center gap-2 small text-secondary">
                                                                    <span class="fw-semibold text-dark">{{ $comment->user?->name ?? 'نادیار' }}</span>
                                                                    <span>{{ $comment->created_at?->diffForHumans() }}</span>
                                                                </div>
                                                                @if ($comment->comment)
                                                                    <p class="mt-2 mb-0 text-secondary">{{ $comment->comment }}</p>
                                                                @endif
                                                                @if ($comment->attachment_path)
                                                                    <a
                                                                        href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($comment->attachment_path) }}"
                                                                        target="_blank"
                                                                        class="btn btn-sm btn-outline-primary mt-3"
                                                                    >
                                                                        {{ $comment->attachment_name ?: 'Attachment' }}
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="alert alert-light border mb-0">
                                                        هێشتا هیچ تێبینی یان فایلێک بۆ ئەم تاسکە زیاد نەکراوە.
                                                    </div>
                                                @endforelse
                                            </div>

                                            <form
                                                class="mt-4 rounded-4 border bg-body-tertiary p-3"
                                                method="POST"
                                                action="{{ route('tasks.comments.store', $task) }}"
                                                enctype="multipart/form-data"
                                            >
                                                @csrf
                                                <input type="hidden" name="comment_task_id" value="{{ $task->id }}">
                                                <input type="hidden" name="view" value="{{ $currentView }}">
                                                <input type="hidden" name="completed" value="{{ $completedFilter }}">
                                                <div class="mb-3">
                                                    <textarea
                                                        name="comment"
                                                        rows="3"
                                                        class="form-control @if ($failedCommentTaskId === (string) $task->id && ($errors->has('comment') || $errors->has('attachment'))) is-invalid @endif"
                                                        placeholder="context ی نوێ یان feedback زیاد بکە..."
                                                    >{{ $failedCommentTaskId === (string) $task->id ? old('comment') : '' }}</textarea>
                                                    @if ($failedCommentTaskId === (string) $task->id && ($errors->has('comment') || $errors->has('attachment')))
                                                        <div class="invalid-feedback d-block">
                                                            {{ $errors->first('comment') ?: $errors->first('attachment') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center">
                                                    <div class="w-100">
                                                        <input name="attachment" type="file" class="form-control">
                                                        <div class="file-input-note mt-2">PDF, images, Office files, or text up to 10MB.</div>
                                                    </div>
                                                    <button type="submit" class="btn btn-success flex-shrink-0 px-4">زیادکردنی تێبینی</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="alert alert-light border rounded-4 mb-0">
                                    {{ $isArchivedView ? 'هیچ تاسکێکی ئەرشیڤکراوی سپاردوو نییە.' : 'تۆ هێشتا هیچ تاسکێکت بە کەسی تر نەداوە.' }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"
    ></script>
    <script>
        (() => {
            const hiddenInput = document.getElementById('assigned_to');
            const searchInput = document.getElementById('assignee_search');
            const preview = document.getElementById('selected_assignee_preview');
            const options = Array.from(document.querySelectorAll('.assignee-option'));

            if (!hiddenInput || !searchInput || !preview || options.length === 0) {
                return;
            }

            const renderPreview = () => {
                const selected = options.find((option) => option.dataset.userId === hiddenInput.value);

                options.forEach((option) => option.classList.toggle('is-selected', option === selected));

                if (!selected) {
                    preview.textContent = 'وەرگر هەڵبژێرە';
                    return;
                }

                preview.innerHTML = `
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="fw-semibold text-dark">${selected.dataset.userName}</div>
                            <div class="small text-secondary mt-1">${selected.dataset.userUsername}</div>
                        </div>
                        <div class="small text-secondary">${selected.dataset.userEmail || '---'}</div>
                    </div>
                `;
            };

            const filterOptions = () => {
                const query = searchInput.value.trim().toLowerCase();

                options.forEach((option) => {
                    const haystack = `${option.dataset.userName} ${option.dataset.userUsername} ${option.dataset.userEmail}`.toLowerCase();
                    option.classList.toggle('d-none', query !== '' && !haystack.includes(query));
                });
            };

            options.forEach((option) => {
                option.addEventListener('click', () => {
                    hiddenInput.value = option.dataset.userId || '';
                    renderPreview();
                });
            });

            searchInput.addEventListener('input', filterOptions);

            renderPreview();
        })();
    </script>
@endpush
