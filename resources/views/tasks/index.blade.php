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
@endpush

@section('content')
    @php($failedCommentTaskId = (string) old('comment_task_id'))
    @php($failedStatusTaskId = (string) old('status_task_id'))
    @php($selectedAssigneeId = (string) old('assigned_to', $users->first()?->id))
    @php($statusLabels = ['pending' => 'چاوەڕوان', 'in_progress' => 'لە کاردایە', 'pending_review' => 'چاوەڕوانی پشکنین', 'completed' => 'تەواوبووە'])
    @php($statusClasses = ['pending' => 'text-bg-warning', 'in_progress' => 'text-bg-primary', 'pending_review' => 'text-bg-info', 'completed' => 'text-bg-success'])
    @php($priorityLabels = ['urgent' => 'Urgent', 'high' => 'High', 'low' => 'Low'])
    @php($priorityClasses = ['urgent' => 'text-bg-danger', 'high' => 'text-bg-warning', 'low' => 'text-bg-secondary'])
    @php($isArchivedView = $currentView === 'archived')
    @php($showCompleted = $completedFilter === 'show')
    @php($focusRouteParameters = $currentFocus !== 'all' ? ['focus' => $currentFocus] : [])
    @php($showTaskForm = old('title') !== null || $errors->has('title') || $errors->has('description') || $errors->has('priority') || $errors->has('due_date') || $errors->has('assigned_to'))
    @php($viewContextRouteParameters = $isArchivedView ? ['view' => 'archived'] : array_merge($showCompleted ? ['completed' => 'show'] : [], $focusRouteParameters))
    @php($tabRouteParameters = $currentTab === 'delegated' ? ['tab' => 'delegated'] : [])
    @php($activeViewRouteParameters = array_merge($showCompleted ? ['completed' => 'show'] : [], $tabRouteParameters, $focusRouteParameters))
    @php($archivedViewRouteParameters = array_merge(['view' => 'archived'], $tabRouteParameters))
    @php($mineTabRouteParameters = $isArchivedView ? ['view' => 'archived'] : array_merge($showCompleted ? ['completed' => 'show'] : [], $focusRouteParameters))
    @php($delegatedTabRouteParameters = array_merge($mineTabRouteParameters, ['tab' => 'delegated']))
    @php($taskDetailRouteParameters = array_merge($viewContextRouteParameters, $tabRouteParameters))
    @php($activeMineRoute = route('tasks.index') . '#task-lists')
    @php($activeDelegatedRoute = route('tasks.index', ['tab' => 'delegated']) . '#task-lists')
    @php($pendingReviewRoute = route('tasks.index', ['tab' => 'delegated', 'focus' => 'pending_review']) . '#task-lists')
    @php($urgentRoute = route('tasks.index', array_merge($tabRouteParameters, ['focus' => 'urgent'])) . '#task-lists')
    @php($archivedStatsRoute = route('tasks.index', $archivedViewRouteParameters) . '#task-lists')
    @php($clearFocusRoute = route('tasks.index', array_merge($showCompleted ? ['completed' => 'show'] : [], $tabRouteParameters)) . '#task-lists')
    @php($focusLabel = $currentFocus === 'urgent' ? 'Urgent Tasks' : ($currentFocus === 'pending_review' ? 'Pending Review' : null))

    <div class="container-xxl px-3 px-lg-4">
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl">
                <a href="{{ $activeMineRoute }}" class="text-decoration-none text-reset">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge rounded-pill text-bg-light border text-dark">MT</span>
                                <span class="small text-secondary">My Tasks</span>
                            </div>
                            <div class="small text-uppercase text-secondary fw-semibold">Active For Me</div>
                            <div class="fs-2 fw-bold text-dark mt-2">{{ $activeAssignedToMeCount }}</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-xl">
                <a href="{{ $activeDelegatedRoute }}" class="text-decoration-none text-reset">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge rounded-pill text-bg-light border text-dark">AT</span>
                                <span class="small text-secondary">Assigned</span>
                            </div>
                            <div class="small text-uppercase text-secondary fw-semibold">Active By Me</div>
                            <div class="fs-2 fw-bold text-dark mt-2">{{ $activeAssignedByMeCount }}</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-xl">
                <a href="{{ $pendingReviewRoute }}" class="text-decoration-none text-reset">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge rounded-pill text-bg-light border text-dark">PR</span>
                                <span class="small text-secondary">Review</span>
                            </div>
                            <div class="small text-uppercase text-secondary fw-semibold">Pending Review</div>
                            <div class="fs-2 fw-bold text-dark mt-2">{{ $pendingReviewCount }}</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-xl">
                <a href="{{ $urgentRoute }}" class="text-decoration-none text-reset">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge rounded-pill text-bg-light border text-dark">UG</span>
                                <span class="small text-secondary">Priority</span>
                            </div>
                            <div class="small text-uppercase text-secondary fw-semibold">Urgent Tasks</div>
                            <div class="fs-2 fw-bold text-dark mt-2">{{ $urgentActiveCount }}</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-xl">
                <a href="{{ $archivedStatsRoute }}" class="text-decoration-none text-reset">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge rounded-pill text-bg-light border text-dark">AR</span>
                                <span class="small text-secondary">Archive</span>
                            </div>
                            <div class="small text-uppercase text-secondary fw-semibold">Archived Tasks</div>
                            <div class="fs-2 fw-bold text-dark mt-2">{{ $archivedTaskCount }}</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column gap-4">
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

                    <div class="d-flex flex-column gap-3">
                        <div class="nav nav-underline gap-3 border-bottom pb-2">
                            <a
                                href="{{ route('tasks.index', $activeViewRouteParameters) }}"
                                class="nav-link px-0 {{ $isArchivedView ? 'text-secondary' : 'active text-dark fw-bold' }}"
                            >
                                چالاک
                            </a>
                            <a
                                href="{{ route('tasks.index', $archivedViewRouteParameters) }}"
                                class="nav-link px-0 {{ $isArchivedView ? 'active text-dark fw-bold' : 'text-secondary' }}"
                            >
                                ئەرشیف
                            </a>
                        </div>

                        @unless ($isArchivedView)
                            <form method="GET" action="{{ route('tasks.index') }}" class="row g-3 align-items-end">
                                <input type="hidden" name="tab" value="{{ $currentTab }}">
                                @if ($currentFocus !== 'all')
                                    <input type="hidden" name="focus" value="{{ $currentFocus }}">
                                @endif
                                <div class="col-12 col-sm-6 col-lg-4">
                                    <label for="completed_filter" class="form-label small fw-semibold text-uppercase text-secondary">Completed</label>
                                    <select id="completed_filter" name="completed" class="form-select bg-light border-0 rounded-4">
                                        <option value="hide" @selected(! $showCompleted)>Hide Done</option>
                                        <option value="show" @selected($showCompleted)>Show Done</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-auto">
                                    <button type="submit" class="btn btn-primary rounded-4 px-4">Apply</button>
                                </div>
                            </form>
                        @endunless

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary rounded-4 px-4">
                                چوونەدەرەوە
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div>
                        <h2 class="h4 fw-bold text-dark mb-2">زیادکردنی تاسکی نوێ</h2>
                        <p class="text-secondary mb-0">
                            کاتێک پێویستت پێیە، فۆڕمەکە بکەرەوە و تاسکەکە بە شێوەی ڕێکخراو زیاد بکە.
                        </p>
                    </div>

                    <button
                        class="btn btn-primary rounded-4 px-4"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#add-task-panel"
                        aria-expanded="{{ $showTaskForm ? 'true' : 'false' }}"
                        aria-controls="add-task-panel"
                    >
                        زیادکردنی تاسک
                    </button>
                </div>

                <div id="add-task-panel" class="collapse{{ $showTaskForm ? ' show' : '' }} mt-4">
                    <form method="POST" action="{{ route('tasks.store') }}">
                        @csrf
                        <input type="hidden" name="tab" value="{{ $currentTab }}">
                        <input type="hidden" name="view" value="{{ $currentView }}">
                        <input type="hidden" name="completed" value="{{ $completedFilter }}">
                        <input type="hidden" name="focus" value="{{ $currentFocus }}">

                        <div class="row g-4">
                            <div class="col-xl-7">
                                <div class="mb-4">
                                    <label for="title" class="form-label fw-semibold">ناونیشانی تاسک</label>
                                    <input
                                        id="title"
                                        name="title"
                                        type="text"
                                        value="{{ old('title') }}"
                                        class="form-control form-control-lg bg-light border-0 rounded-4 @error('title') is-invalid @enderror"
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
                                        class="form-control bg-light border-0 rounded-4 @error('description') is-invalid @enderror"
                                        placeholder="وردەکاری زیاتر بۆ تاسکەکە..."
                                    >{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-xl-5">
                                <div class="card border-0 bg-light rounded-4 h-100">
                                    <div class="card-body p-4">
                                        <div class="row g-3">
                                            <div class="col-sm-6 col-xl-12">
                                                <label for="priority" class="form-label fw-semibold">Priority</label>
                                                <select id="priority" name="priority" class="form-select form-select-lg bg-white border-0 rounded-4 @error('priority') is-invalid @enderror">
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
                                                    class="form-control form-control-lg bg-white border-0 rounded-4 @error('due_date') is-invalid @enderror"
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

                                                <div class="card border-0 shadow-sm rounded-4">
                                                    <div class="card-body p-3">
                                                        <div id="selected_assignee_preview" class="rounded-4 bg-body-tertiary p-3 small text-secondary mb-3">
                                                            وەرگر هەڵبژێرە
                                                        </div>

                                                        <input
                                                            id="assignee_search"
                                                            type="text"
                                                            class="form-control bg-light border-0 rounded-4 mb-3"
                                                            placeholder="بە ناو یان username بگەڕێ..."
                                                            @disabled($users->isEmpty())
                                                        >

                                                        <div id="assignee_picker_list" class="d-grid gap-2">
                                                            @forelse ($users as $assignableUser)
                                                                @php($userInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim($assignableUser->name), 0, 1)))
                                                                <button
                                                                    type="button"
                                                                    class="assignee-option btn btn-light border-0 shadow-sm text-start d-flex align-items-center justify-content-between gap-3 rounded-4 p-3"
                                                                    data-user-id="{{ $assignableUser->id }}"
                                                                    data-user-name="{{ $assignableUser->name }}"
                                                                    data-user-username="{{ $assignableUser->username }}"
                                                                    data-user-email="{{ $assignableUser->email }}"
                                                                >
                                                                    <span class="d-flex align-items-center gap-3 min-w-0">
                                                                        <span class="badge rounded-pill text-bg-light border text-dark px-3 py-2">
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
                                                                <div class="alert alert-secondary border-0 rounded-4 mb-0">
                                                                    user ی تر بەردەست نییە بۆ سپاردن.
                                                                </div>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                </div>

                                                @error('assigned_to')
                                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary btn-lg rounded-4 w-100" @disabled($users->isEmpty())>
                                                    زیادکردنی تاسک
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="task-lists" class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                    <div>
                        <h2 class="h4 fw-bold text-dark mb-2">Task Lists</h2>
                        <p class="text-secondary mb-0">
                            بە tab ـەکان نێوان تاسکەکانی خۆت و تاسکەکانەی تۆ سپاردووت بگۆڕە.
                        </p>
                    </div>

                    <div class="nav nav-underline gap-3 border-bottom pb-2">
                        <a
                            href="{{ route('tasks.index', $mineTabRouteParameters) }}"
                            class="nav-link px-0 {{ $currentTab === 'mine' ? 'active text-dark fw-bold' : 'text-secondary' }}"
                        >
                            My Tasks
                        </a>
                        <a
                            href="{{ route('tasks.index', $delegatedTabRouteParameters) }}"
                            class="nav-link px-0 {{ $currentTab === 'delegated' ? 'active text-dark fw-bold' : 'text-secondary' }}"
                        >
                            Tasks I Assigned
                        </a>
                    </div>
                </div>

                @if ($currentFocus !== 'all')
                    <div class="alert border-0 shadow-sm rounded-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                        <div>
                            <div class="small text-uppercase fw-semibold text-secondary mb-1">Active Filter</div>
                            <div class="fw-semibold text-dark">{{ $focusLabel }}</div>
                        </div>
                        <a href="{{ $clearFocusRoute }}" class="btn btn-outline-dark rounded-4">Clear Filter</a>
                    </div>
                @endif

                @if ($currentTab === 'mine')
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                        <h3 class="h5 fw-bold text-dark mb-0">{{ $isArchivedView ? 'ئەرشیفی تاسکە سپێردراوەکان بۆ من' : 'تاسکە سپێردراوەکان بۆ من' }}</h3>
                        <span class="small text-secondary">{{ $assignedToMe->count() }} item</span>
                    </div>

                    <div class="d-grid gap-3">
                        @forelse ($assignedToMe as $task)
                            @php($isOverdue = $task->due_date && $task->due_date->isPast() && $task->status !== 'completed')
                            @php($commentsCollapseId = 'task-comments-' . $task->id)
                            @php($showCommentsPanel = $failedCommentTaskId === (string) $task->id)
                            <article class="card border-0 shadow-sm rounded-4">
                                <div class="card-body p-3 p-lg-4">
                                    <a
                                        href="{{ route('tasks.show', array_merge(['task' => $task->id], $taskDetailRouteParameters)) }}"
                                        class="text-decoration-none text-reset d-block"
                                    >
                                        <div class="d-flex flex-column gap-3">
                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                <h3 class="h5 fw-bold mb-0 text-dark">{{ $task->title }}</h3>
                                                <span class="badge rounded-pill {{ $priorityClasses[$task->priority] ?? 'text-bg-secondary' }}">
                                                    {{ $priorityLabels[$task->priority] ?? $task->priority }}
                                                </span>
                                                <span class="badge rounded-pill {{ $statusClasses[$task->status] ?? 'text-bg-secondary' }}">
                                                    {{ $statusLabels[$task->status] ?? $task->status }}
                                                </span>
                                            </div>

                                            @if ($task->description)
                                                <p class="text-secondary mb-0">{{ $task->description }}</p>
                                            @endif

                                            <div class="small text-secondary d-flex flex-column gap-1">
                                                <div>سپێردراوە لەلایەن: {{ $task->assigner?->name ?? 'نادیار' }}</div>
                                                <div>کاتی دواوە: {{ $task->due_date?->format('Y-m-d') ?? 'دیاری نەکراوە' }}</div>
                                                @if ($task->archived_at)
                                                    <div>ئەرشیف: {{ $task->archived_at->format('Y-m-d H:i') }}</div>
                                                @endif
                                                @if ($isOverdue)
                                                    <div class="text-danger fw-semibold">ئەو تاسکە دوا کەوتووە</div>
                                                @endif
                                            </div>
                                        </div>
                                    </a>

                                    <div class="border-top pt-3 mt-3">
                                        <form method="POST" action="{{ route('tasks.status.update', $task) }}" class="row g-2 align-items-end">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status_task_id" value="{{ $task->id }}">
                                            <input type="hidden" name="view" value="{{ $currentView }}">
                                            <input type="hidden" name="completed" value="{{ $completedFilter }}">
                                            <input type="hidden" name="tab" value="{{ $currentTab }}">
                                            <input type="hidden" name="focus" value="{{ $currentFocus }}">
                                            <div class="col-12 col-md-8 col-xl-6">
                                                <label for="status-{{ $task->id }}" class="form-label small fw-semibold text-uppercase text-secondary mb-1">Status</label>
                                                <select
                                                    id="status-{{ $task->id }}"
                                                    name="status"
                                                    class="form-select bg-light border-0 rounded-3 @if ($failedStatusTaskId === (string) $task->id && $errors->has('status')) is-invalid @endif"
                                                >
                                                    @foreach ($taskStatuses as $status)
                                                        <option value="{{ $status }}" @selected($task->status === $status)>
                                                            {{ $statusLabels[$status] ?? $status }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if ($failedStatusTaskId === (string) $task->id && $errors->has('status'))
                                                    <div class="invalid-feedback d-block">{{ $errors->first('status') }}</div>
                                                @endif
                                            </div>
                                            <div class="col-12 col-sm-auto">
                                                <button type="submit" class="btn btn-primary rounded-3 px-4 w-100">Save</button>
                                            </div>
                                        </form>

                                        <div class="d-flex flex-column flex-sm-row gap-2 mt-3">
                                            <button
                                                class="btn btn-outline-secondary rounded-3"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#{{ $commentsCollapseId }}"
                                                aria-expanded="{{ $showCommentsPanel ? 'true' : 'false' }}"
                                                aria-controls="{{ $commentsCollapseId }}"
                                            >
                                                تێبینی و فایلەکان ({{ $task->comments->count() }})
                                            </button>

                                            @if (! $isArchivedView && $task->is_completed)
                                                <form method="POST" action="{{ route('tasks.archive', $task) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="view" value="{{ $currentView }}">
                                                    <input type="hidden" name="completed" value="{{ $completedFilter }}">
                                                    <input type="hidden" name="tab" value="{{ $currentTab }}">
                                                    <input type="hidden" name="focus" value="{{ $currentFocus }}">
                                                    <button type="submit" class="btn btn-outline-secondary rounded-3 w-100">ئەرشیفکردنی تاسک</button>
                                                </form>
                                            @elseif ($task->archived_at)
                                                <div class="small text-secondary align-self-center">ئەرشیف کراوە لە {{ $task->archived_at->format('Y-m-d H:i') }}</div>
                                            @endif
                                        </div>

                                        <div id="{{ $commentsCollapseId }}" class="collapse{{ $showCommentsPanel ? ' show' : '' }} mt-3">
                                            <div class="border-top pt-3">
                                                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                                    <h4 class="h6 fw-bold mb-0 text-dark">تێبینی و هاوپێچەکان</h4>
                                                    <span class="small text-secondary">{{ $task->comments->count() }} item</span>
                                                </div>

                                                @forelse ($task->comments as $comment)
                                                    @php($commentInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim($comment->user?->name ?? '?'), 0, 1)))
                                                    <div class="d-flex align-items-start gap-3 py-3 border-top">
                                                        <span class="badge rounded-pill text-bg-light text-dark px-3 py-2 flex-shrink-0">
                                                            {{ $commentInitial !== '' ? $commentInitial : '?' }}
                                                        </span>
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
                                                                    class="btn btn-sm btn-outline-primary rounded-3 mt-3"
                                                                >
                                                                    {{ $comment->attachment_name ?: 'Attachment' }}
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="small text-secondary mb-0">هێشتا هیچ تێبینی یان فایلێک بۆ ئەم تاسکە زیاد نەکراوە.</p>
                                                @endforelse

                                                <form
                                                    class="row g-3 mt-1"
                                                    method="POST"
                                                    action="{{ route('tasks.comments.store', $task) }}"
                                                    enctype="multipart/form-data"
                                                >
                                                    @csrf
                                                    <input type="hidden" name="comment_task_id" value="{{ $task->id }}">
                                                    <input type="hidden" name="view" value="{{ $currentView }}">
                                                    <input type="hidden" name="completed" value="{{ $completedFilter }}">
                                                    <input type="hidden" name="tab" value="{{ $currentTab }}">
                                                    <input type="hidden" name="focus" value="{{ $currentFocus }}">
                                                    <div class="col-12">
                                                        <textarea
                                                            name="comment"
                                                            rows="3"
                                                            class="form-control bg-light border-0 rounded-3 @if ($failedCommentTaskId === (string) $task->id && ($errors->has('comment') || $errors->has('attachment'))) is-invalid @endif"
                                                            placeholder="تێبینی یان ڕوونکردنەوە زیاد بکە..."
                                                        >{{ $failedCommentTaskId === (string) $task->id ? old('comment') : '' }}</textarea>
                                                        @if ($failedCommentTaskId === (string) $task->id && ($errors->has('comment') || $errors->has('attachment')))
                                                            <div class="invalid-feedback d-block">
                                                                {{ $errors->first('comment') ?: $errors->first('attachment') }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="col-12 col-lg">
                                                        <input name="attachment" type="file" class="form-control bg-light border-0 rounded-3">
                                                        <div class="small text-secondary mt-2">PDF, images, Office files, or text up to 10MB.</div>
                                                    </div>
                                                    <div class="col-12 col-lg-auto">
                                                        <button type="submit" class="btn btn-primary rounded-3 px-4 w-100">Add Comment</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="alert alert-light border-0 rounded-4 shadow-sm mb-0">
                                {{ $isArchivedView ? 'هیچ تاسکێکی ئەرشیڤکراوت نییە.' : 'هیچ تاسکێکت نییە لە ئێستادا.' }}
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                        <h3 class="h5 fw-bold text-dark mb-0">{{ $isArchivedView ? 'ئەرشیفی ئەو تاسکانەی من دامنە' : 'ئەو تاسکانەی من دامنە' }}</h3>
                        <span class="small text-secondary">{{ $assignedByMe->count() }} item</span>
                    </div>

                    <div class="d-grid gap-3">
                        @forelse ($assignedByMe as $task)
                            @php($isOverdue = $task->due_date && $task->due_date->isPast() && $task->status !== 'completed')
                            @php($commentsCollapseId = 'task-comments-' . $task->id)
                            @php($showCommentsPanel = $failedCommentTaskId === (string) $task->id)
                            <article class="card border-0 shadow-sm rounded-4">
                                <div class="card-body p-3 p-lg-4">
                                    <a
                                        href="{{ route('tasks.show', array_merge(['task' => $task->id], $taskDetailRouteParameters)) }}"
                                        class="text-decoration-none text-reset d-block"
                                    >
                                        <div class="d-flex flex-column gap-3">
                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                <h3 class="h5 fw-bold mb-0 text-dark">{{ $task->title }}</h3>
                                                <span class="badge rounded-pill {{ $priorityClasses[$task->priority] ?? 'text-bg-secondary' }}">
                                                    {{ $priorityLabels[$task->priority] ?? $task->priority }}
                                                </span>
                                                <span class="badge rounded-pill {{ $statusClasses[$task->status] ?? 'text-bg-secondary' }}">
                                                    {{ $statusLabels[$task->status] ?? $task->status }}
                                                </span>
                                            </div>

                                            @if ($task->description)
                                                <p class="text-secondary mb-0">{{ $task->description }}</p>
                                            @endif

                                            <div class="small text-secondary d-flex flex-column gap-1">
                                                <div>سپێردراوە بۆ: {{ $task->assignee?->name ?? 'نادیار' }}</div>
                                                <div>کاتی دواوە: {{ $task->due_date?->format('Y-m-d') ?? 'دیاری نەکراوە' }}</div>
                                                <div>
                                                    {{ $task->status === 'completed' ? 'کارەکە تەواوبووە' : ($task->status === 'pending_review' ? 'لە چاوەڕوانی پشکنینە' : 'کارەکە بەردەوامە') }}
                                                </div>
                                                @if ($task->archived_at)
                                                    <div>ئەرشیف: {{ $task->archived_at->format('Y-m-d H:i') }}</div>
                                                @endif
                                                @if ($isOverdue)
                                                    <div class="text-danger fw-semibold">ئەو تاسکە دوا کەوتووە</div>
                                                @endif
                                            </div>
                                        </div>
                                    </a>

                                    <div class="border-top pt-3 mt-3">
                                        <div class="d-flex flex-column flex-sm-row gap-2">
                                            <button
                                                class="btn btn-outline-secondary rounded-3"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#{{ $commentsCollapseId }}"
                                                aria-expanded="{{ $showCommentsPanel ? 'true' : 'false' }}"
                                                aria-controls="{{ $commentsCollapseId }}"
                                            >
                                                تێبینی و فایلەکان ({{ $task->comments->count() }})
                                            </button>

                                            @if (! $isArchivedView && $task->is_completed)
                                                <form method="POST" action="{{ route('tasks.archive', $task) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="view" value="{{ $currentView }}">
                                                    <input type="hidden" name="completed" value="{{ $completedFilter }}">
                                                    <input type="hidden" name="tab" value="{{ $currentTab }}">
                                                    <input type="hidden" name="focus" value="{{ $currentFocus }}">
                                                    <button type="submit" class="btn btn-outline-secondary rounded-3 w-100">ئەرشیفکردنی تاسک</button>
                                                </form>
                                            @endif
                                        </div>

                                        <div id="{{ $commentsCollapseId }}" class="collapse{{ $showCommentsPanel ? ' show' : '' }} mt-3">
                                            <div class="border-top pt-3">
                                                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                                    <h4 class="h6 fw-bold mb-0 text-dark">تێبینی و هاوپێچەکان</h4>
                                                    <span class="small text-secondary">{{ $task->comments->count() }} item</span>
                                                </div>

                                                @forelse ($task->comments as $comment)
                                                    @php($commentInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim($comment->user?->name ?? '?'), 0, 1)))
                                                    <div class="d-flex align-items-start gap-3 py-3 border-top">
                                                        <span class="badge rounded-pill text-bg-light text-dark px-3 py-2 flex-shrink-0">
                                                            {{ $commentInitial !== '' ? $commentInitial : '?' }}
                                                        </span>
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
                                                                    class="btn btn-sm btn-outline-primary rounded-3 mt-3"
                                                                >
                                                                    {{ $comment->attachment_name ?: 'Attachment' }}
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="small text-secondary mb-0">هێشتا هیچ تێبینی یان فایلێک بۆ ئەم تاسکە زیاد نەکراوە.</p>
                                                @endforelse

                                                <form
                                                    class="row g-3 mt-1"
                                                    method="POST"
                                                    action="{{ route('tasks.comments.store', $task) }}"
                                                    enctype="multipart/form-data"
                                                >
                                                    @csrf
                                                    <input type="hidden" name="comment_task_id" value="{{ $task->id }}">
                                                    <input type="hidden" name="view" value="{{ $currentView }}">
                                                    <input type="hidden" name="completed" value="{{ $completedFilter }}">
                                                    <input type="hidden" name="tab" value="{{ $currentTab }}">
                                                    <input type="hidden" name="focus" value="{{ $currentFocus }}">
                                                    <div class="col-12">
                                                        <textarea
                                                            name="comment"
                                                            rows="3"
                                                            class="form-control bg-light border-0 rounded-3 @if ($failedCommentTaskId === (string) $task->id && ($errors->has('comment') || $errors->has('attachment'))) is-invalid @endif"
                                                            placeholder="context ی نوێ یان feedback زیاد بکە..."
                                                        >{{ $failedCommentTaskId === (string) $task->id ? old('comment') : '' }}</textarea>
                                                        @if ($failedCommentTaskId === (string) $task->id && ($errors->has('comment') || $errors->has('attachment')))
                                                            <div class="invalid-feedback d-block">
                                                                {{ $errors->first('comment') ?: $errors->first('attachment') }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="col-12 col-lg">
                                                        <input name="attachment" type="file" class="form-control bg-light border-0 rounded-3">
                                                        <div class="small text-secondary mt-2">PDF, images, Office files, or text up to 10MB.</div>
                                                    </div>
                                                    <div class="col-12 col-lg-auto">
                                                        <button type="submit" class="btn btn-primary rounded-3 px-4 w-100">Add Comment</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="alert alert-light border-0 rounded-4 shadow-sm mb-0">
                                {{ $isArchivedView ? 'هیچ تاسکێکی ئەرشیڤکراوی سپاردوو نییە.' : 'تۆ هێشتا هیچ تاسکێکت بە کەسی تر نەداوە.' }}
                            </div>
                        @endforelse
                    </div>
                @endif
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

                options.forEach((option) => {
                    const isSelected = option === selected;
                    option.classList.toggle('bg-primary-subtle', isSelected);
                    option.classList.toggle('text-primary-emphasis', isSelected);
                    option.classList.toggle('shadow', isSelected);
                });

                if (!selected) {
                    preview.textContent = 'وەرگر هەڵبژێرە';
                    return;
                }

                preview.innerHTML = `
                    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
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
